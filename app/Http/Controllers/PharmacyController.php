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
            'items.drug',
        ])
        ->whereIn('status', [Prescription::STATUS_PENDING, Prescription::STATUS_PARTIAL])
        ->whereHas('consultation.encounter', function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId);
        })
        ->whereHas('items', function($q) {
            $q->whereIn('dispensing_status', [PrescriptionItem::STATUS_PENDING, PrescriptionItem::STATUS_PARTIALLY_DISPENSED]);
        })
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
            'consultation.encounter.program',
            'prescribedBy:id,name',
            'items.drug',
            'items.dispensations.dispensingOfficer:id,name',
        ]);

        $programId = $prescription->consultation?->encounter?->program_id ?? null;

        return view('pharmacy.prescription', compact('prescription', 'facilityId', 'facility', 'programId'));
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

        $encounter = $item->prescription->consultation->encounter ?? null;

        $programId = $encounter->program_id ?? null;

        DB::beginTransaction();
        try {
            if ($request->action === 'dispense' || $request->action === 'update') {
                $drug = $item->drug;
                if (!$drug) {
                    return response()->json(['error' => 'Drug not found for this prescription item.'], 404);
                }

                $alreadyDispensed = $item->dispensations->sum('quantity_dispensed');
                $prescribedQty = $item->quantity;
                
                // For update action, quantity is the NEW prescribed quantity
                if ($request->action === 'update') {
                    if (!$request->quantity) {
                        return response()->json(['error' => 'Quantity is required for update.'], 422);
                    }
                    $newPrescribedQty = $request->quantity;
                    
                    // Update the prescribed quantity to the new amount
                    $item->update(['quantity' => $newPrescribedQty]);
                    
                    // Calculate difference between new prescribed and already dispensed
                    $difference = $newPrescribedQty - $alreadyDispensed;
                    
                    if ($difference > 0) {
                        // Need to dispense more
                        $availableStock = $drug->totalStockInFacility($facilityId, $programId);
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
                        
                        $this->deductStock($drug, $difference, $facilityId, $programId);
                        
                        // Log additional dispensing
                        if ($encounter) {
                            EncounterAction::create([
                                'encounter_id' => $encounter->id,
                                'user_id' => Auth::id(),
                                'action_type' => 'Pharmacy',
                                'description' => "Updated dispensing for {$drug->name}: additional {$difference} unit(s). New prescribed quantity: {$newPrescribedQty}",
                                'action_time' => now(),
                            ]);
                        }
                    } elseif ($difference < 0) {
                        // Need to return stock to inventory
                        $returnQty = abs($difference);
                        
                        // Add stock back using reverse FEFO (add to most recent batch)
                        $this->addStock($drug, $returnQty, $facilityId, $programId);
                        
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
                                'description' => "Updated dispensing for {$drug->name}: returned {$returnQty} unit(s) to stock. New prescribed quantity: {$newPrescribedQty}",
                                'action_time' => now(),
                            ]);
                        }
                    }
                    
                    // Pharmacist qty is the real qty - mark as dispensed
                    $item->update([
                        'dispensing_status' => PrescriptionItem::STATUS_DISPENSED,
                    ]);
                    
                } else {
                    // Original dispense action - use quantity from request or remaining prescribed
                    $quantityToDispense = $request->quantity ?? ($prescribedQty - $alreadyDispensed);
                    
                    if ($quantityToDispense <= 0) {
                        return response()->json(['error' => 'Quantity must be greater than 0.'], 422);
                    }
                    
                    $availableStock = $drug->totalStockInFacility($facilityId, $programId);
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
                    
                    $this->deductStock($drug, $quantityToDispense, $facilityId, $programId);
                    
                    // Pharmacist qty is the real qty - update prescribed quantity
                    $totalDispensed = $alreadyDispensed + $quantityToDispense;
                    $item->update([
                        'quantity' => $totalDispensed,
                        'dispensing_status' => PrescriptionItem::STATUS_DISPENSED,
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
                // Cancel action - return all dispensed stock to inventory
                $totalDispensed = $item->dispensations->sum('quantity_dispensed');
                
                if ($totalDispensed > 0 && $item->drug) {
                    // Add stock back using reverse FEFO (add to most recent batch)
                    $this->addStock($item->drug, $totalDispensed, $facilityId, $programId);
                    
                    // Create a negative dispensation record to track the return
                    PharmacyDispensation::create([
                        'prescription_item_id' => $item->id,
                        'quantity_dispensed'   => -$totalDispensed,
                        'cost_of_medication'   => -$item->drug->getSellingPrice($facilityId) * $totalDispensed,
                        'dispensing_date_time' => now(),
                        'dispensing_officer_id' => Auth::id(),
                    ]);
                }
                
                $item->update(['dispensing_status' => 'Cancelled']);
                
                if ($encounter) {
                    EncounterAction::create([
                        'encounter_id' => $encounter->id,
                        'user_id' => Auth::id(),
                        'action_type' => 'Pharmacy',
                        'description' => "Cancelled prescription item and returned {$totalDispensed} unit(s) to stock: " . ($item->drug ? $item->drug->name : 'Unknown drug'),
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
                    'update' => 'Prescription quantity updated successfully.',
                    'cancel' => 'Item cancelled.',
                    default => 'Action completed.'
                },
                'new_status' => $item->dispensing_status,
                'total_dispensed' => $item->dispensations->sum('quantity_dispensed'),
                'prescribed_quantity' => $item->quantity, // This is now the updated quantity
                'remaining_stock' => $item->drug ? $item->drug->totalStockInFacility($facilityId, $programId) : 0,
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

        $programId = $encounter->program_id ?? null;

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
                $stock = $drug->totalStockInFacility($facilityId, $programId);
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

                $this->deductStock($drug, $qty, $facilityId, $programId);

                $alreadyDispensed = $item->dispensations->sum('quantity_dispensed');
                $totalDispensed   = $alreadyDispensed + $qty;
                $item->update([
                    'quantity' => $totalDispensed,
                    'dispensing_status' => PrescriptionItem::STATUS_DISPENSED,
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

    // ── Drugs Inventory ──────────────────────────────────────────────────────

    public function drugs(Request $request)
    {
        $user       = Auth::user();
        $facilityId = $user->facility_id;

        // Debug: Log facility info
        \Log::info("Pharmacy Drugs - Facility ID: {$facilityId}, User: {$user->name}");

        // All programs that have stock in this facility
        $programs = \App\Models\Program::whereHas('drugStocks', fn($q) =>
            $q->where('facility_id', $facilityId)
        )->orderBy('name')->get();

        // Selected program filter
        $programId = $request->input('program_id');

        // Build drug query - show drugs for facility OR global drugs (facility_id = 0)
        $query = Drug::where(function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->orWhere('facility_id', 0); // Include global drugs
            })
            ->with(['stocks' => fn($q) =>
                $q->where('facility_id', $facilityId)
                  ->where('status', 'approved')
                  ->when($programId, fn($q2) => $q2->where('program_id', $programId))
            ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('dosage_form', 'LIKE', "%{$s}%")
                  ->orWhere('strength', 'LIKE', "%{$s}%")
            );
        }

        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'in') {
                $query->whereHas('stocks', fn($q) =>
                    $q->where('facility_id', $facilityId)
                      ->where('status', 'approved')
                      ->where('quantity_remaining', '>', 0)
                      ->when($programId, fn($q2) => $q2->where('program_id', $programId))
                );
            } elseif ($status === 'out') {
                $query->whereDoesntHave('stocks', fn($q) =>
                    $q->where('facility_id', $facilityId)
                      ->where('status', 'approved')
                      ->where('quantity_remaining', '>', 0)
                      ->when($programId, fn($q2) => $q2->where('program_id', $programId))
                );
            } elseif ($status === 'low') {
                // Get drugs with low stock (include global drugs)
                $lowStockDrugIds = Drug::where(function($q) use ($facilityId) {
                        $q->where('facility_id', $facilityId)
                          ->orWhere('facility_id', 0);
                    })
                    ->whereHas('stocks', fn($q) =>
                        $q->where('facility_id', $facilityId)
                          ->where('status', 'approved')
                          ->where('quantity_remaining', '>', 0)
                          ->when($programId, fn($q2) => $q2->where('program_id', $programId))
                    )
                    ->get()
                    ->filter(fn($d) => $d->totalStockInFacility($facilityId, $programId) < 10 && $d->totalStockInFacility($facilityId, $programId) > 0)
                    ->pluck('id');
                $query->whereIn('id', $lowStockDrugIds);
            }
        }

        $drugs = $query->orderBy('name')->paginate(25)->appends($request->query());

        // Debug: Log results
        \Log::info("Pharmacy Drugs - Total drugs found: {$drugs->total()}");

        // Summary stats (include global drugs)
        $totalDrugs = Drug::where(function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->orWhere('facility_id', 0);
            })->count();

        $inStockCount = Drug::where(function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->orWhere('facility_id', 0);
            })
            ->whereHas('stocks', fn($q) =>
                $q->where('facility_id', $facilityId)
                  ->where('status', 'approved')
                  ->where('quantity_remaining', '>', 0)
            )->count();

        $outOfStockCount = $totalDrugs - $inStockCount;

        $lowStockCount = 0;
        $expiringCount = 0;

        // Low stock: drugs with stock between 1-9 (include global drugs)
        $allDrugsWithStock = Drug::where(function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->orWhere('facility_id', 0);
            })
            ->whereHas('stocks', fn($q) =>
                $q->where('facility_id', $facilityId)
                  ->where('status', 'approved')
                  ->where('quantity_remaining', '>', 0)
            )->get();
        
        foreach ($allDrugsWithStock as $d) {
            $qty = $d->totalStockInFacility($facilityId);
            if ($qty > 0 && $qty < 10) $lowStockCount++;
        }

        // Expiring within 90 days
        $expiringCount = DrugStock::where('facility_id', $facilityId)
            ->where('status', 'approved')
            ->where('quantity_remaining', '>', 0)
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->distinct('drug_id')
            ->count('drug_id');

        // Total stock value
        $totalStockValue = DrugStock::where('facility_id', $facilityId)
            ->where('status', 'approved')
            ->where('quantity_remaining', '>', 0)
            ->selectRaw('SUM(quantity_remaining * COALESCE(unit_cost, 0)) as total')
            ->value('total') ?? 0;

        // Stock by program
        $stockByProgram = DrugStock::where('drug_stocks.facility_id', $facilityId)
            ->where('drug_stocks.status', 'approved')
            ->where('drug_stocks.quantity_remaining', '>', 0)
            ->join('programs', 'drug_stocks.program_id', '=', 'programs.id')
            ->selectRaw('programs.name as program_name, programs.id as program_id, COUNT(DISTINCT drug_stocks.drug_id) as drug_count, SUM(drug_stocks.quantity_remaining) as total_qty')
            ->groupBy('programs.id', 'programs.name')
            ->get();

        return view('pharmacy.drugs', compact(
            'drugs', 'programs', 'programId',
            'totalDrugs', 'inStockCount', 'outOfStockCount', 'lowStockCount',
            'expiringCount', 'totalStockValue', 'stockByProgram'
        ));
    }

    // ── Reports ─────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $user       = Auth::user();
        $facilityId = $user->facility_id;
        $facility   = Facility::find($facilityId);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        // ── Overview Stats ──
        $totalDispensations = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->count();

        $totalRevenue = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->sum('cost_of_medication');

        $totalCopayment = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->sum('copayment_amount');

        $totalItemsDispensed = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('quantity_dispensed', '>', 0)
            ->sum('quantity_dispensed');

        $uniquePatients = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->join('prescription_items', 'pharmacy_dispensations.prescription_item_id', '=', 'prescription_items.id')
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->join('clinical_consultations', 'prescriptions.clinical_consultation_id', '=', 'clinical_consultations.id')
            ->join('encounters', 'clinical_consultations.encounter_id', '=', 'encounters.id')
            ->distinct('encounters.patient_id')
            ->count('encounters.patient_id');

        // ── Pharmacist Performance ──
        $pharmacistPerformance = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('quantity_dispensed', '>', 0)
            ->join('users', 'pharmacy_dispensations.dispensing_officer_id', '=', 'users.id')
            ->selectRaw('
                users.id as user_id,
                users.name as pharmacist_name,
                COUNT(*) as total_dispensations,
                SUM(quantity_dispensed) as total_items,
                SUM(cost_of_medication) as total_revenue,
                SUM(copayment_amount) as total_copayment,
                COUNT(DISTINCT DATE(dispensing_date_time)) as active_days,
                MIN(dispensing_date_time) as first_dispense,
                MAX(dispensing_date_time) as last_dispense
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_dispensations')
            ->get();

        // ── Top Dispensed Drugs ──
        $topDrugs = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('quantity_dispensed', '>', 0)
            ->join('prescription_items', 'pharmacy_dispensations.prescription_item_id', '=', 'prescription_items.id')
            ->join('drugs', 'prescription_items.drug_id', '=', 'drugs.id')
            ->selectRaw('
                drugs.id as drug_id,
                drugs.name as drug_name,
                drugs.dosage_form,
                drugs.strength,
                SUM(quantity_dispensed) as total_dispensed,
                SUM(cost_of_medication) as total_revenue,
                COUNT(DISTINCT pharmacy_dispensations.id) as dispense_count
            ')
            ->groupBy('drugs.id', 'drugs.name', 'drugs.dosage_form', 'drugs.strength')
            ->orderByDesc('total_dispensed')
            ->limit(20)
            ->get();

        // ── Revenue by Payment Method ──
        $revenueByPayment = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('quantity_dispensed', '>', 0)
            ->selectRaw('
                COALESCE(payment_method, "Unknown") as method,
                COUNT(*) as count,
                SUM(cost_of_medication) as revenue,
                SUM(copayment_amount) as copayment
            ')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        // ── Daily Trend (last 30 days) ──
        $dailyTrend = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('quantity_dispensed', '>', 0)
            ->selectRaw('DATE(dispensing_date_time) as date, COUNT(*) as count, SUM(cost_of_medication) as revenue, SUM(quantity_dispensed) as items')
            ->groupByRaw('DATE(dispensing_date_time)')
            ->orderBy('date')
            ->get();

        // ── Revenue by Program ──
        $revenueByProgram = PharmacyDispensation::whereBetween('dispensing_date_time', [$dateFrom, $dateTo . ' 23:59:59'])
            ->whereHas('prescriptionItem.prescription.clinicalConsultation.encounter', fn($q) =>
                $q->where('facility_id', $facilityId))
            ->where('quantity_dispensed', '>', 0)
            ->join('prescription_items', 'pharmacy_dispensations.prescription_item_id', '=', 'prescription_items.id')
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->join('clinical_consultations', 'prescriptions.clinical_consultation_id', '=', 'clinical_consultations.id')
            ->join('encounters', 'clinical_consultations.encounter_id', '=', 'encounters.id')
            ->leftJoin('programs', 'encounters.program_id', '=', 'programs.id')
            ->selectRaw('
                COALESCE(programs.name, "No Program") as program_name,
                COUNT(*) as count,
                SUM(quantity_dispensed) as items,
                SUM(cost_of_medication) as revenue
            ')
            ->groupByRaw('COALESCE(programs.name, "No Program")')
            ->orderByDesc('revenue')
            ->get();

        return view('pharmacy.reports', compact(
            'dateFrom', 'dateTo', 'facility',
            'totalDispensations', 'totalRevenue', 'totalCopayment', 'totalItemsDispensed', 'uniquePatients',
            'pharmacistPerformance', 'topDrugs', 'revenueByPayment', 'dailyTrend', 'revenueByProgram'
        ));
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function deductStock(Drug $drug, int $quantity, $facilityId, $programId = null): int
    {
        return $drug->deductStock($quantity, $facilityId, $programId);
    }
    
    /**
     * Add stock back to inventory (reverse of deductStock)
     */
    private function addStock(Drug $drug, int $quantity, $facilityId, $programId = null): int
    {
        // Find the most recent stock batch for this drug in this facility and program
        $stock = DrugStock::where('drug_id', $drug->id)
            ->where('facility_id', $facilityId)
            ->when($programId, fn($q) => $q->where('program_id', $programId))
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
            'program_id' => $programId,
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
