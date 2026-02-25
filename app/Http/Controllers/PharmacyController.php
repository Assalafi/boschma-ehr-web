<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Drug;
use App\Models\DrugStock;
use App\Models\PharmacyDispensation;
use App\Models\EncounterAction;
use App\Models\Encounter;
use App\Models\Facility;

class PharmacyController extends Controller
{
    public function index()
    {
        return redirect()->route('pharmacy.queue');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $user       = Auth::user();
        $facilityId = $user->facility_id;

        $pendingPrescriptions = Prescription::whereHas('consultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('status', Prescription::STATUS_PENDING)
            ->count();

        $todayDispensations = PharmacyDispensation::whereDate('dispensing_date_time', today())
            ->whereHas('prescriptionItem.prescription.consultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->count();

        $lowStockDrugs = Drug::where('facility_id', $facilityId)
            ->whereHas('stocks', fn($q) =>
                $q->where('facility_id', $facilityId)
                  ->where('quantity_remaining', '<', 10)
                  ->where('quantity_remaining', '>', 0)
                  ->where('status', 'approved'))
            ->count();

        $todayRevenue = PharmacyDispensation::whereDate('dispensing_date_time', today())
            ->whereHas('prescriptionItem.prescription.consultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->sum('cost_of_medication');

        return view('pharmacy.dashboard', compact(
            'pendingPrescriptions',
            'todayDispensations',
            'lowStockDrugs',
            'todayRevenue'
        ));
    }

    /**
     * Dispensation Queue — all pending prescriptions for this facility
     */
    /**
     * @return \Illuminate\View\View
     */
    public function queue(Request $request)
    {
        $facilityId = Auth::user()->facility_id;

        $query = Prescription::with([
            'consultation.encounter.patient',
            'prescribedBy:id,name',
            'items' => fn($q) => $q->whereIn('dispensing_status', [PrescriptionItem::STATUS_PENDING]),
            'items.drug',
        ])
        ->whereHas('consultation.encounter', fn($q) => $q->where('facility_id', $facilityId))
        ->whereIn('status', [Prescription::STATUS_PENDING, Prescription::STATUS_PARTIAL])
        ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('consultation.encounter.patient', fn($q) =>
                $q->search($s));
        }

        $pendingCount = $query->count();
        $prescriptions = $query->paginate(20);

        $dispensedToday = PharmacyDispensation::whereDate('dispensing_date_time', today())
            ->whereHas('prescriptionItem.prescription.consultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->count();

        return view('pharmacy.queue', compact('prescriptions', 'pendingCount', 'dispensedToday'));
    }

    /**
     * Show a single prescription with all items for dispensing
     */
    public function showPrescription(Prescription $prescription)
    {
        $facilityId = Auth::user()->facility_id;
        $facility   = Facility::find($facilityId);

        $prescription->load([
            'consultation.encounter.patient',
            'prescribedBy:id,name',
            'items.drug',
            'items.dispensations.dispensingOfficer:id,name',
        ]);

        return view('pharmacy.prescription', compact('prescription', 'facilityId', 'facility'));
    }

    /**
     * AJAX: Dispense, update, or cancel a single prescription item
     */
    public function dispenseItem(Request $request, PrescriptionItem $item)
    {
        $request->validate([
            'action'         => 'required|in:dispense,update,cancel',
            'quantity'       => 'nullable|integer|min:1',
            'payment_method' => 'nullable|string|in:Cash,Card,Mobile Money,Insurance',
        ]);

        $facilityId = Auth::user()->facility_id;
        $facility   = Facility::find($facilityId);
        $isSecondary = $facility && strtolower($facility->type) === 'secondary';

        $item->load(['drug', 'prescription.consultation.encounter', 'dispensations']);

        // Check if encounter is completed - if so, no edits allowed
        $encounter = $item->prescription->consultation->encounter ?? null;
        if ($encounter && $encounter->status === \App\Models\Encounter::STATUS_COMPLETED) {
            return response()->json(['error' => 'Cannot modify: Encounter has been completed.'], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->action === 'dispense' || $request->action === 'update') {
                $drug = $item->drug;
                if (!$drug) {
                    return response()->json(['error' => 'Drug not found for this prescription item.'], 404);
                }

                $alreadyDispensed = $item->dispensations->sum('quantity_dispensed');
                $prescribedQty = $item->quantity;
                
                // For update action, quantity is the NEW total to dispense
                if ($request->action === 'update') {
                    if (!$request->quantity) {
                        return response()->json(['error' => 'Quantity is required for update.'], 422);
                    }
                    $quantityToDispense = $request->quantity;
                    
                    // Allow over-dispensing - no validation against prescribed quantity
                    
                    // Calculate difference (what we need to add or return)
                    $difference = $quantityToDispense - $alreadyDispensed;
                    
                    if ($difference > 0) {
                        // Need to dispense more
                        $availableStock = $drug->totalStockInFacility($facilityId);
                        if ($availableStock < $difference) {
                            return response()->json(['error' => "Insufficient stock. Available: {$availableStock}, Additional needed: {$difference}."], 422);
                        }
                        
                        $cost = $drug->getSellingPrice($facilityId) * $difference;
                        $copayment = $isSecondary ? round($cost * 0.10, 2) : null;

                        PharmacyDispensation::create([
                            'prescription_item_id' => $item->id,
                            'quantity_dispensed'   => $difference,
                            'cost_of_medication'   => $cost,
                            'payment_method'       => $request->payment_method,
                            'copayment_amount'     => $copayment,
                            'dispensing_date_time' => now(),
                            'dispensing_officer_id' => Auth::id(),
                        ]);
                        
                        $this->deductStock($drug, $difference, $facilityId);
                        
                        // Log additional dispensing
                        if ($encounter) {
                            EncounterAction::create([
                                'encounter_id' => $encounter->id,
                                'user_id' => Auth::id(),
                                'action_type' => 'Pharmacy',
                                'description' => "Updated dispensing for {$drug->name}: additional {$difference} unit(s). Total now: {$quantityToDispense}",
                                'action_time' => now(),
                            ]);
                        }
                    } elseif ($difference < 0) {
                        // Need to return stock to inventory
                        $returnQty = abs($difference);
                        
                        // Add stock back using reverse FEFO (add to most recent batch)
                        $this->addStock($drug, $returnQty, $facilityId);
                        
                        // Create a negative dispensation record to track the return
                        PharmacyDispensation::create([
                            'prescription_item_id' => $item->id,
                            'quantity_dispensed'   => -$returnQty,
                            'cost_of_medication'   => -$drug->getSellingPrice($facilityId) * $returnQty,
                            'dispensing_date_time' => now(),
                            'dispensing_officer_id' => Auth::id(),
                        ]);
                        
                        if ($encounter) {
                            EncounterAction::create([
                                'encounter_id' => $encounter->id,
                                'user_id' => Auth::id(),
                                'action_type' => 'Pharmacy',
                                'description' => "Updated dispensing for {$drug->name}: returned {$returnQty} unit(s) to stock. Total now: {$quantityToDispense}",
                                'action_time' => now(),
                            ]);
                        }
                    }
                    
                    // Update status based on total dispensed
                    $item->update([
                        'dispensing_status' => $quantityToDispense >= $prescribedQty 
                            ? PrescriptionItem::STATUS_DISPENSED 
                            : PrescriptionItem::STATUS_PENDING
                    ]);
                    
                } else {
                    // Original dispense action - use quantity from request or remaining prescribed
                    $quantityToDispense = $request->quantity ?? ($prescribedQty - $alreadyDispensed);
                    
                    if ($quantityToDispense <= 0) {
                        return response()->json(['error' => 'Quantity must be greater than 0.'], 422);
                    }
                    
                    $availableStock = $drug->totalStockInFacility($facilityId);
                    if ($availableStock < $quantityToDispense) {
                        return response()->json(['error' => "Insufficient stock. Available: {$availableStock}, Required: {$quantityToDispense}."], 422);
                    }
                    
                    $cost = $drug->getSellingPrice($facilityId) * $quantityToDispense;
                    $copayment = $isSecondary ? round($cost * 0.10, 2) : null;

                    PharmacyDispensation::create([
                        'prescription_item_id' => $item->id,
                        'quantity_dispensed'   => $quantityToDispense,
                        'cost_of_medication'   => $cost,
                        'payment_method'       => $request->payment_method,
                        'copayment_amount'     => $copayment,
                        'dispensing_date_time' => now(),
                        'dispensing_officer_id' => Auth::id(),
                    ]);
                    
                    $this->deductStock($drug, $quantityToDispense, $facilityId);
                    
                    // Update status based on total dispensed
                    $totalDispensed = $alreadyDispensed + $quantityToDispense;
                    $item->update([
                        'dispensing_status' => $totalDispensed >= $prescribedQty 
                            ? PrescriptionItem::STATUS_DISPENSED 
                            : PrescriptionItem::STATUS_PENDING
                    ]);
                    
                    // Log initial dispensing
                    if ($encounter) {
                        EncounterAction::create([
                            'encounter_id' => $encounter->id,
                            'user_id' => Auth::id(),
                            'action_type' => 'Pharmacy',
                            'description' => "Dispensed {$quantityToDispense} unit(s) of {$drug->name}.",
                            'action_time' => now(),
                        ]);
                    }
                }
            } else {
                // Cancel action
                $item->update(['dispensing_status' => 'Cancelled']);
                
                if ($encounter) {
                    EncounterAction::create([
                        'encounter_id' => $encounter->id,
                        'user_id' => Auth::id(),
                        'action_type' => 'Pharmacy',
                        'description' => "Cancelled prescription item: " . ($item->drug ? $item->drug->name : 'Unknown drug'),
                        'action_time' => now(),
                    ]);
                }
            }

            $this->syncPrescriptionStatus($item->prescription);

            DB::commit();

            $item->refresh();
            $item->load(['dispensations.dispensingOfficer:id,name']);
            
            return response()->json([
                'success' => true,
                'message' => match($request->action) {
                    'dispense' => 'Item dispensed successfully.',
                    'update' => 'Dispensing updated successfully.',
                    'cancel' => 'Item cancelled.',
                    default => 'Action completed.'
                },
                'new_status' => $item->dispensing_status,
                'total_dispensed' => $item->dispensations->sum('quantity_dispensed'),
                'prescribed_quantity' => $item->quantity,
                'remaining_stock' => $item->drug ? $item->drug->totalStockInFacility($facilityId) : 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Bulk dispense multiple pending prescription items at once
     */
    public function dispenseBulk(Request $request, Prescription $prescription)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|string',
            'items.*.qty'    => 'required|integer|min:1',
            'payment_method' => 'required|string|in:Cash,Card,Mobile Money,Insurance',
        ]);

        $facilityId  = Auth::user()->facility_id;
        $facility    = Facility::find($facilityId);
        $isSecondary = $facility && strtolower($facility->type) === 'secondary';

        $encounter = $prescription->consultation?->encounter ?? null;
        if ($encounter && $encounter->status === \App\Models\Encounter::STATUS_COMPLETED) {
            return response()->json(['error' => 'Cannot modify: Encounter has been completed.'], 422);
        }

        DB::beginTransaction();
        try {
            $dispensed = [];
            $errors    = [];

            foreach ($request->items as $entry) {
                $item = PrescriptionItem::with(['drug', 'dispensations'])->find($entry['id']);
                if (!$item || $item->prescription_id !== $prescription->id) {
                    $errors[] = "Item {$entry['id']} not found.";
                    continue;
                }
                if ($item->dispensing_status !== PrescriptionItem::STATUS_PENDING) {
                    $errors[] = ($item->drug->name ?? 'Item') . ' is not pending.';
                    continue;
                }

                $drug = $item->drug;
                if (!$drug) {
                    $errors[] = "Drug not found for item.";
                    continue;
                }

                $qty   = (int) $entry['qty'];
                $stock = $drug->totalStockInFacility($facilityId);
                if ($stock < $qty) {
                    $errors[] = "Insufficient stock for {$drug->name}. Available: {$stock}, Requested: {$qty}.";
                    continue;
                }

                $cost      = $drug->getSellingPrice($facilityId) * $qty;
                $copayment = $isSecondary ? round($cost * 0.10, 2) : null;

                PharmacyDispensation::create([
                    'prescription_item_id' => $item->id,
                    'quantity_dispensed'   => $qty,
                    'cost_of_medication'   => $cost,
                    'payment_method'       => $request->payment_method,
                    'copayment_amount'     => $copayment,
                    'dispensing_date_time' => now(),
                    'dispensing_officer_id' => Auth::id(),
                ]);

                $this->deductStock($drug, $qty, $facilityId);

                $alreadyDispensed = $item->dispensations->sum('quantity_dispensed');
                $totalDispensed   = $alreadyDispensed + $qty;
                $item->update([
                    'dispensing_status' => $totalDispensed >= $item->quantity
                        ? PrescriptionItem::STATUS_DISPENSED
                        : PrescriptionItem::STATUS_PENDING,
                ]);

                if ($encounter) {
                    EncounterAction::create([
                        'encounter_id' => $encounter->id,
                        'user_id'      => Auth::id(),
                        'action_type'  => 'Pharmacy',
                        'description'  => "Dispensed {$qty} unit(s) of {$drug->name}. Payment: {$request->payment_method}" . ($isSecondary ? " | Copayment: GHS {$copayment}" : ''),
                        'action_time'  => now(),
                    ]);
                }

                $dispensed[] = $drug->name;
            }

            if (!empty($errors) && empty($dispensed)) {
                DB::rollBack();
                return response()->json(['error' => implode(' ', $errors)], 422);
            }

            $this->syncPrescriptionStatus($prescription);
            DB::commit();

            $msg = count($dispensed) . ' item(s) dispensed successfully.';
            if (!empty($errors)) $msg .= ' Warnings: ' . implode(' ', $errors);

            return response()->json(['success' => true, 'message' => $msg, 'warnings' => $errors]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dispensation History
     */
    /**
     * @return \Illuminate\View\View
     */
    public function history(Request $request)
    {
        $facilityId = Auth::user()->facility_id;

        $query = Prescription::with([
            'consultation.encounter.patient',
            'prescribedBy:id,name',
            'items.drug',
            'items.dispensations.dispensingOfficer:id,name',
        ])
        ->whereHas('consultation.encounter', fn($q) => $q->where('facility_id', $facilityId))
        ->whereIn('status', [Prescription::STATUS_DISPENSED, Prescription::STATUS_PARTIAL])
        ->latest();

        if ($request->filled('date')) {
            $query->whereDate('updated_at', $request->date);
        }

        $prescriptions = $query->paginate(20);

        return view('pharmacy.history', compact('prescriptions'));
    }

    // ── Legacy compat ─────────────────────────────────────────────────────────

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dispensationIndex()
    {
        return redirect()->route('pharmacy.queue');
    }

    // ── Stock Management ──────────────────────────────────────────────────────

    public function stockIndex()
    {
        $user  = Auth::user();
        $drugs = Drug::with(['stocks' => fn($q) =>
                $q->where('facility_id', $user->facility_id)->where('status', 'approved')])
            ->where('facility_id', $user->facility_id)
            ->paginate(20);

        return view('pharmacy.stock.index', compact('drugs'));
    }

    public function drugStock(Drug $drug)
    {
        $drug->load(['stocks' => fn($q) => $q->where('status', 'approved')->orderBy('expiry_date')]);
        return view('pharmacy.stock.show', compact('drug'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStock(Request $request, Drug $drug)
    {
        $request->validate([
            'quantity'     => 'required|integer|min:1',
            'batch_number' => 'required|string',
            'expiry_date'  => 'required|date|after:today',
            'unit_price'   => 'nullable|numeric|min:0',
        ]);

        DrugStock::create([
            'drug_id'           => $drug->id,
            'facility_id'       => Auth::user()->facility_id,
            'batch_number'      => $request->batch_number,
            'quantity'          => $request->quantity,
            'quantity_remaining'=> $request->quantity,
            'expiry_date'       => $request->expiry_date,
            'selling_price'     => $request->unit_price,
            'status'            => 'active',
        ]);

        return redirect()->route('stock-management.index');
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function deductStock(Drug $drug, int $quantity, $facilityId): int
    {
        return $drug->deductStock($quantity, $facilityId);
    }
    
    /**
     * Add stock back to inventory (reverse of deductStock)
     */
    private function addStock(Drug $drug, int $quantity, $facilityId): int
    {
        // Find the most recent stock batch for this drug in this facility
        $stock = DrugStock::where('drug_id', $drug->id)
            ->where('facility_id', $facilityId)
            ->where('status', 'approved')
            ->where('quantity_remaining', '>', 0)
            ->latest('stocked_at')
            ->first();
            
        if ($stock) {
            // Add back to the most recent batch
            $stock->increment('quantity_remaining', $quantity);
            return $quantity;
        }
        
        // If no existing batch found, create a new return batch
        DrugStock::create([
            'drug_id' => $drug->id,
            'facility_id' => $facilityId,
            'batch_number' => 'RETURN-' . date('YmdHis'),
            'expiry_date' => now()->addYears(2), // Default 2 years for returns
            'quantity_received' => $quantity,
            'quantity_remaining' => $quantity,
            'unit_cost' => $drug->unit_price ?? 0,
            'supplier' => 'RETURN',
            'status' => 'approved',
            'stocked_by' => Auth::id(),
            'stocked_at' => now(),
        ]);
        
        return $quantity;
    }

    private function syncPrescriptionStatus(Prescription $prescription): void
    {
        $prescription->load('items');
        $total      = $prescription->items->count();
        if (!$total) return;

        $dispensed  = $prescription->items->where('dispensing_status', PrescriptionItem::STATUS_DISPENSED)->count();
        $cancelled  = $prescription->items->where('dispensing_status', 'Cancelled')->count();

        if ($dispensed + $cancelled === $total && $dispensed > 0) {
            $prescription->update(['status' => Prescription::STATUS_DISPENSED]);
        } elseif ($dispensed > 0) {
            $prescription->update(['status' => Prescription::STATUS_PARTIAL]);
        }
    }
}
