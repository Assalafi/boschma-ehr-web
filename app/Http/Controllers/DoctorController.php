<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Encounter;
use App\Models\ClinicalConsultation;
use App\Models\ClinicalDiagnosis;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Investigation;
use App\Models\Drug;
use App\Models\IcdCode;
use App\Models\EncounterAction;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceReferral;
use App\Models\ServiceItem;
use App\Models\ServiceCategory;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\Ward;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Admission;
use App\Enums\ActionType;

class DoctorController extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        
        // Patients waiting for consultation (triaged but no consultation yet)
        $pendingConsultations = Encounter::where('facility_id', $facilityId)
            ->where('status', Encounter::STATUS_TRIAGED)
            ->whereDoesntHave('consultations')
            ->count();
        
        // In-progress consultations (started but not completed)
        $inProgressCount = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->where('status', ClinicalConsultation::STATUS_IN_PROGRESS)
            ->count();
            
        // Completed consultations today
        $completedToday = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->where('status', ClinicalConsultation::STATUS_COMPLETED)
            ->whereDate('created_at', today())
            ->count();
        
        // My consultations today
        $myConsultationsToday = ClinicalConsultation::where('doctor_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
        
        // Pending investigation results
        $pendingInvestigations = Investigation::whereHas('consultation.encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->where('status', 'Pending')
            ->count();

        // Patient queue - sorted by priority (Red first, then Yellow, then Green)
        $patientQueue = Encounter::with(['patient', 'vitalSigns', 'program'])
            ->where('facility_id', $facilityId)
            ->where('status', Encounter::STATUS_TRIAGED)
            ->whereDoesntHave('consultations')
            ->get()
            ->sortBy(function($encounter) {
                $priority = $encounter->vitalSigns->first()?->overall_priority ?? 'Green';
                return match($priority) {
                    'Red' => 1,
                    'Yellow' => 2,
                    'Green' => 3,
                    default => 4
                };
            })
            ->take(15);
            
        // Recent consultations
        $recentConsultations = ClinicalConsultation::with(['encounter.patient', 'doctor', 'diagnoses'])
            ->whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->latest()
            ->take(10)
            ->get();
            
        // Priority distribution today
        $priorityStats = Encounter::where('facility_id', $facilityId)
            ->whereHas('vitalSigns')
            ->whereDate('created_at', today())
            ->with('vitalSigns')
            ->get()
            ->groupBy(function($encounter) {
                return $encounter->vitalSigns->first()?->overall_priority ?? 'Unknown';
            })
            ->map->count()
            ->toArray();

        return view('doctor.dashboard', compact(
            'pendingConsultations',
            'inProgressCount',
            'completedToday',
            'myConsultationsToday',
            'pendingInvestigations',
            'patientQueue',
            'recentConsultations',
            'priorityStats'
        ));
    }

    /**
     * Patient Queue - Data-driven stages, not reliant on encounter status field.
     *  1. Awaiting Consultation = Triaged with no consultation
     *  2. In Consultation       = has an in-progress consultation
     *  3. Awaiting Lab          = has at least one pending service_order
     *  4. Awaiting Pharmacy     = has at least one pending/partial prescription
     *  5. Completed Today       = consultation completed today
     */
    public function queue(Request $request)
    {
        $user       = Auth::user();
        $facilityId = $user->facility_id;

        $base = Encounter::with(['patient', 'vitalSigns', 'program', 'consultations'])
            ->where('facility_id', $facilityId);

        // 1. Awaiting Consultation — triaged, no consultation started
        $triagedQ = clone $base;
        if ($request->filled('priority')) {
            $triagedQ->whereHas('vitalSigns', fn($q) => $q->where('overall_priority', $request->priority));
        }
        $triaged = $triagedQ
            ->whereIn('status', [Encounter::STATUS_TRIAGED, Encounter::STATUS_REGISTERED, Encounter::STATUS_WAITING])
            ->whereDoesntHave('consultations')
            ->whereHas('vitalSigns')
            ->get()
            ->sortBy(fn($e) => match(strtolower($e->vitalSigns->first()?->overall_priority ?? 'Green')) {
                'red', 'critical', 'high' => 1, 
                'yellow', 'urgent' => 2, 
                'green', 'normal' => 3, 
                default => 4
            });

        // 2. In Consultation — has an active (in-progress) consultation
        $inConsultation = (clone $base)
            ->whereHas('consultations', fn($q) => $q->where('status', ClinicalConsultation::STATUS_IN_PROGRESS))
            ->orderByDesc('updated_at')
            ->get();

        // 3. Awaiting Lab — has at least one pending service_order for this facility
        $awaitingLab = (clone $base)
            ->whereHas('serviceOrders', fn($q) => $q->where('status', 'pending'))
            ->orderByDesc('updated_at')
            ->get();

        // 4. Awaiting Pharmacy — has at least one pending or partially-dispensed prescription
        $awaitingPharmacy = (clone $base)
            ->whereHas('consultations.prescriptions', fn($q) => $q->whereIn('status', [
                Prescription::STATUS_PENDING,
                Prescription::STATUS_PARTIAL,
            ]))
            ->orderByDesc('updated_at')
            ->get();

        // 5. Completed Today — consultation completed today
        $completedToday = (clone $base)
            ->whereHas('consultations', fn($q) => $q
                ->where('status', ClinicalConsultation::STATUS_COMPLETED)
                ->whereDate('updated_at', today())
            )
            ->orderByDesc('updated_at')
            ->get();

        return view('doctor.queue', compact(
            'triaged',
            'inConsultation',
            'awaitingLab',
            'awaitingPharmacy',
            'completedToday'
        ));
    }

    /**
     * Start Consultation - View patient details and start consultation
     */
    public function startConsultation(Request $request, Encounter $encounter, $step = null)
    {
        $encounter->load(['patient', 'vitalSigns.takenBy', 'program', 'consultations.diagnoses', 'consultations.procedures', 'consultations.prescriptions.items.drug']);
        
        // Allow editing if consultation is still in-progress, draft, or active; only redirect when completed
        $existingConsultation = $encounter->consultations->first();
        if ($existingConsultation && !in_array($existingConsultation->status, [ClinicalConsultation::STATUS_IN_PROGRESS, 'draft', 'active'])) {
            return redirect()->route('doctor.consultation.show', $existingConsultation)
                ->with('info', 'Consultation already completed for this encounter.');
        }
        
        // Get patient history (previous encounters)
        $patientHistory = [];
        if ($encounter->patient) {
            $patientHistory = Encounter::with(['consultations.diagnoses', 'consultations.prescriptions.items', 'vitalSigns'])
                ->where('patient_id', $encounter->patient->id)
                ->where('id', '!=', $encounter->id)
                ->latest()
                ->take(5)
                ->get();
        }
        
        // Get ICD codes for diagnosis selection
        // Use binary collation so '&' (ASCII 38) sorts before digits,
        // keeping postcoordination codes (e.g. 1A00&XN8P1) adjacent to their base code (1A00)
        $icdCodes = IcdCode::orderByRaw('code COLLATE utf8mb4_bin')->get();
        
        // Get drugs for prescription, excluding those already sent to pharmacy for this encounter
        $sentDrugIds = $encounter->consultations
            ->flatMap(fn($c) => $c->prescriptions)
            ->flatMap(fn($rx) => $rx->items)
            ->pluck('drug_id')
            ->unique()
            ->toArray();
        $drugs = Drug::whereNotIn('id', $sentDrugIds)->orderBy('name')->get();

        // Fetch all service items grouped by category > type, with Lab/Rad categories first
        $rawServices = DB::table('service_items')
            ->join('service_types', 'service_items.service_type_id', '=', 'service_types.id')
            ->join('service_categories', 'service_types.service_category_id', '=', 'service_categories.id')
            ->select(
                'service_categories.id as cat_id',
                'service_categories.name as cat_name',
                'service_types.name as type_name',
                'service_items.id as id',
                'service_items.name as item_name'
            )
            ->orderBy('service_categories.name')
            ->orderBy('service_types.name')
            ->orderBy('service_items.name')
            ->get()
            ->groupBy('cat_name');

        // Sort: Lab categories first, Rad second, everything else alphabetically
        $labCats = []; $radCats = []; $otherCats = [];
        foreach ($rawServices as $catName => $items) {
            $lower = strtolower($catName);
            if (str_contains($lower, 'laboratory') || str_contains($lower, 'haematolog') || str_contains($lower, 'hematolog')) {
                $labCats[$catName] = $items;
            } elseif (str_contains($lower, 'radiolog')) {
                $radCats[$catName] = $items;
            } else {
                $otherCats[$catName] = $items;
            }
        }
        ksort($otherCats);
        $serviceCategories = collect($labCats + $radCats + $otherCats);

        $savedData = session('consultation_draft_' . $encounter->id, []);

        // Fetch service names already sent to lab for this encounter (active orders + active referrals only)
        $orderedServiceNames = DB::table('service_order_items')
            ->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
            ->join('service_items', 'service_order_items.service_item_id', '=', 'service_items.id')
            ->where('service_orders.encounter_id', $encounter->id)
            ->whereNotIn('service_orders.status', ['cancelled'])
            ->whereNotIn('service_order_items.status', ['cancelled'])
            ->pluck('service_items.name')
            ->merge(
                DB::table('service_referrals')
                    ->join('service_items', 'service_referrals.service_item_id', '=', 'service_items.id')
                    ->where('service_referrals.encounter_id', $encounter->id)
                    ->whereNotIn('service_referrals.status', ['cancelled'])
                    ->pluck('service_items.name')
            )
            ->unique()
            ->values();

        // If AJAX request (for refreshing Sent to Pharmacy panel), return only the panel fragment and updated drugs list
        if ($request->ajax() || $request->wantsJson()) {
            $sentPrescriptions = $encounter->consultations->flatMap(fn($c) => $c->prescriptions)->filter(fn($rx) => $rx->status !== \App\Models\Prescription::STATUS_CANCELLED);
            return response()->json([
                'panel' => view('doctor.consultation._sent_to_pharmacy_panel', compact('sentPrescriptions'))->render(),
                'drugs' => $drugs->map(fn($d) => ['id' => $d->id, 'name' => $d->name])
            ]);
        }

        $facilities = Facility::orderBy('name')->get();

        $wards = Ward::where('facility_id', Auth::user()->facility_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('doctor.consultation.create', compact('encounter', 'patientHistory', 'icdCodes', 'drugs', 'savedData', 'serviceCategories', 'orderedServiceNames', 'facilities', 'wards', 'step'));
    }


    /**
     * Drug search API - returns drugs with stock info for the doctor's facility
     */
    public function drugSearch(Request $request)
    {
        $q = trim($request->input('q', ''));
        $exclude = (array) $request->input('exclude', []);
        $facilityId = Auth::user()->facility_id;

        // Debug logging
        \Illuminate\Support\Facades\Log::info('Drug search called', [
            'query' => $q,
            'facility_id' => $facilityId,
            'exclude' => $exclude,
            'query_length' => strlen($q)
        ]);

        if (strlen($q) < 4) {
            return response()->json([]);
        }

        // Temporarily remove facility filter to test
        $drugs = Drug::where(function($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('strength', 'LIKE', "%{$q}%")
                      ->orWhere('dosage_form', 'LIKE', "%{$q}%")
                      ->orWhere('description', 'LIKE', "%{$q}%");
            })
            // ->when($facilityId, fn($qb) => $qb->where('facility_id', $facilityId))
            ->whereNotIn('id', $exclude)
            ->orderBy('name')
            ->limit(25)
            ->get(['id','name','dosage_form','strength','unit','unit_price','description','facility_id'])
            ->map(function($drug) use ($facilityId) {
                $stock = $drug->totalStockInFacility($facilityId);
                $status = $stock === 0 ? 'out' : ($stock < 10 ? 'low' : 'in');
                return [
                    'id'          => $drug->id,
                    'name'        => $drug->name,
                    'dosage_form' => $drug->dosage_form ?? '',
                    'strength'    => $drug->strength ?? '',
                    'unit'        => $drug->unit ?? '',
                    'unit_price'  => (float) ($drug->unit_price ?? 0),
                    'description' => $drug->description ?? '',
                    'stock'       => $stock,
                    'stock_status'=> $status,
                    'facility_id' => $drug->facility_id,
                ];
            });

        // Debug logging
        \Illuminate\Support\Facades\Log::info('Drug search results', ['count' => $drugs->count()]);

        return response()->json($drugs);
    }

    /**
     * Update Clinical Assessment (Step 1) - saves presenting complaints, physical exam, and provisional diagnoses
     */
    public function updateClinicalAssessment(Request $request, Encounter $encounter)
    {
        $request->validate([
            'presenting_complaints' => 'required|string',
            'physical_examination'  => 'nullable|string',
            'provisional_diagnosis' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $consultation = ClinicalConsultation::updateOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'doctor_id'             => Auth::id(),
                    'presenting_complaints' => $request->presenting_complaints,
                    'physical_examination'  => $request->physical_examination,
                    'status'                => ClinicalConsultation::STATUS_IN_PROGRESS,
                ]
            );

            // Rebuild provisional diagnoses
            ClinicalDiagnosis::where('clinical_consultation_id', $consultation->id)
                ->where('diagnosis_type', 'Provisional')
                ->delete();

            foreach (array_filter((array) $request->provisional_diagnosis) as $diagId) {
                ClinicalDiagnosis::create([
                    'clinical_consultation_id' => $consultation->id,
                    'icd_code_id'              => $diagId,
                    'diagnosis_type'           => 'Provisional',
                ]);
            }

            // Also persist to session draft
            $draft = session('consultation_draft_' . $encounter->id, []);
            $draft['presenting_complaints'] = $request->presenting_complaints;
            $draft['physical_examination'] = $request->physical_examination;
            $draft['provisional_diagnosis'] = array_filter((array) $request->provisional_diagnosis);
            session(['consultation_draft_' . $encounter->id => $draft]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Clinical assessment updated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Confirmed Diagnosis (Step 3) - saves confirmed diagnoses
     */
    public function updateConfirmedDiagnosis(Request $request, Encounter $encounter)
    {
        $request->validate([
            'confirmed_diagnosis' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Ensure consultation exists
            $consultation = ClinicalConsultation::firstOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'doctor_id' => Auth::id(),
                    'status'    => ClinicalConsultation::STATUS_IN_PROGRESS,
                ]
            );

            // Rebuild confirmed diagnoses
            ClinicalDiagnosis::where('clinical_consultation_id', $consultation->id)
                ->where('diagnosis_type', 'Confirmed')
                ->delete();

            foreach (array_filter((array) $request->confirmed_diagnosis) as $diagId) {
                ClinicalDiagnosis::create([
                    'clinical_consultation_id' => $consultation->id,
                    'icd_code_id'              => $diagId,
                    'diagnosis_type'           => 'Confirmed',
                ]);
            }

            // Also persist to session draft
            $draft = session('consultation_draft_' . $encounter->id, []);
            $draft['confirmed_diagnosis'] = array_filter((array) $request->confirmed_diagnosis);
            session(['consultation_draft_' . $encounter->id => $draft]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Confirmed diagnosis updated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send Prescriptions to Pharmacy
     */
    public function sendToPharmacy(Request $request, Encounter $encounter)
    {
        $request->validate([
            'drug_items'            => 'required|array|min:1',
            'drug_items.*.drug_id'  => 'required|exists:drugs,id',
        ]);

        DB::beginTransaction();
        try {
            $consultation = ClinicalConsultation::firstOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'doctor_id' => Auth::id(),
                    'status'    => ClinicalConsultation::STATUS_IN_PROGRESS,
                ]
            );

            // Generate a unique prescription number
            $prescriptionNumber = 'RX-' . now()->format('Ymd') . '-' . strtoupper(substr(str_shuffle(str_repeat('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 6));

            $prescription = Prescription::create([
                'clinical_consultation_id' => $consultation->id,
                'prescription_number'      => $prescriptionNumber,
                'prescribed_by'            => Auth::id(),
                'status'                   => Prescription::STATUS_PENDING,
                'prescription_date'        => now(),
            ]);

            $addedCount = 0;
            foreach ($request->drug_items as $item) {
                if (empty($item['drug_id'])) continue;
                
                $freq = (int) ($item['frequency'] ?? 3);
                $dur  = (int) ($item['duration']  ?? 5);
                
                PrescriptionItem::create([
                    'prescription_id'   => $prescription->id,
                    'drug_id'           => $item['drug_id'],
                    'dosage'            => $item['dosage']       ?? 'As directed',
                    'frequency'         => $freq,
                    'duration'          => $dur,
                    'quantity'          => $freq * $dur,
                    'instructions'      => $item['instructions'] ?? null,
                    'dispensing_status' => PrescriptionItem::STATUS_PENDING,
                ]);
                $addedCount++;
            }

            if ($addedCount > 0) {
                EncounterAction::create([
                    'encounter_id' => $encounter->id,
                    'user_id'      => Auth::id(),
                    'action_type'  => ActionType::PRESCRIPTION,
                    'description'  => $addedCount . ' medication(s) prescribed.',
                    'action_time'  => now(),
                ]);
            }

            DB::commit();
            return response()->json([
                'success'     => true, 
                'message'     => 'Prescription sent to pharmacy successfully. What would you like to do next?',
                'show_choice' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to send to pharmacy: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Procedures - saves consultation procedures
     */
    public function updateProcedures(Request $request, Encounter $encounter)
    {
        $request->validate([
            'procedures'   => 'required|array|min:1',
            'procedures.*' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $consultation = ClinicalConsultation::firstOrCreate(
                ['encounter_id' => $encounter->id],
                [
                    'doctor_id' => Auth::id(),
                    'status'    => ClinicalConsultation::STATUS_IN_PROGRESS,
                ]
            );

            // Rebuild procedures
            \App\Models\ConsultationProcedure::where('clinical_consultation_id', $consultation->id)->delete();

            foreach ($request->procedures as $procName) {
                \App\Models\ConsultationProcedure::create([
                    'clinical_consultation_id' => $consultation->id,
                    'procedure_name'           => $procName,
                    'procedure_date'           => now(),
                    'performed_by'             => Auth::id(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Procedures updated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save procedures: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Refer Patient — creates a patient-level referral in service_referrals
     */
    public function referPatient(Request $request, Encounter $encounter)
    {
        $request->validate([
            'to_facility_id' => 'required|exists:facilities,id',
            'reason'         => 'required|string|max:2000',
            'clinical_findings' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $facilityId = Auth::user()->facility_id;

            // Check if a patient referral already exists for this encounter
            $existing = DB::table('service_referrals')
                ->where('encounter_id', $encounter->id)
                ->where('referral_type', 'patient')
                ->whereNull('service_item_id')
                ->first();

            if ($existing && $existing->status !== 'pending') {
                return response()->json(['error' => 'A referral already exists and is ' . $existing->status . '.'], 422);
            }

            $data = [
                'encounter_id'    => $encounter->id,
                'from_facility_id'=> $facilityId,
                'to_facility_id'  => $request->to_facility_id,
                'referral_type'   => 'patient',
                'service_item_id' => null,
                'reason'          => $request->reason . ($request->clinical_findings ? "\n\nClinical Findings: " . $request->clinical_findings : ''),
                'status'          => 'pending',
                'updated_at'      => now(),
            ];

            if ($existing) {
                DB::table('service_referrals')->where('id', $existing->id)->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('service_referrals')->insert($data);
            }

            // Update encounter status
            $encounter->update(['status' => Encounter::STATUS_REFERRED]);

            // Ensure consultation exists
            $consultation = ClinicalConsultation::firstOrCreate(
                ['encounter_id' => $encounter->id],
                ['doctor_id' => Auth::id(), 'status' => ClinicalConsultation::STATUS_IN_PROGRESS]
            );
            $consultation->update(['status' => ClinicalConsultation::STATUS_COMPLETED]);

            // Log action
            $toFacility = Facility::find($request->to_facility_id);
            EncounterAction::create([
                'encounter_id' => $encounter->id,
                'user_id'      => Auth::id(),
                'action_type'  => ActionType::CONSULTATION,
                'description'  => 'Patient referred to ' . ($toFacility->name ?? 'facility') . '. Reason: ' . Str::limit($request->reason, 100),
                'action_time'  => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Patient referred to ' . ($toFacility->name ?? 'facility') . ' successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to refer patient: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Discharge or Admit Patient — finalises the encounter
     */
    public function dischargePatient(Request $request, Encounter $encounter)
    {
        $rules = [
            'outcome'        => 'required|in:Treated,Admit,Follow-up',
            'clinical_note'  => 'nullable|string|max:5000',
            'follow_up_date' => 'nullable|date|after:today',
        ];

        // Admission-specific validation
        if ($request->outcome === 'Admit') {
            $rules['ward_id']                = 'required|exists:wards,id';
            $rules['bed_id']                 = 'nullable|exists:beds,id';
            $rules['admission_type']         = 'required|in:emergency,elective,observation';
            $rules['condition_on_admission'] = 'nullable|string|max:500';
            $rules['admission_notes']        = 'nullable|string|max:2000';
        }

        $request->validate($rules);

        // Check for pending lab or pharmacy orders before allowing discharge
        if ($request->outcome === 'Treated') {
            \Log::info('Checking pending orders for discharge - Encounter ID: ' . $encounter->id);
            
            $pendingLabOrders = ServiceOrder::where('encounter_id', $encounter->id)
                ->where('status', 'pending')
                ->exists();

            $pendingPharmacyOrders = Prescription::whereHas('consultation', function($q) use ($encounter) {
                    $q->where('encounter_id', $encounter->id);
                })
                ->whereIn('status', [Prescription::STATUS_PENDING, Prescription::STATUS_PARTIAL])
                ->exists();

            \Log::info('Pending lab orders: ' . ($pendingLabOrders ? 'YES' : 'NO'));
            \Log::info('Pending pharmacy orders: ' . ($pendingPharmacyOrders ? 'YES' : 'NO'));

            if ($pendingLabOrders || $pendingPharmacyOrders) {
                $messages = [];
                if ($pendingLabOrders) {
                    $messages[] = 'pending laboratory orders';
                }
                if ($pendingPharmacyOrders) {
                    $messages[] = 'pending pharmacy prescriptions';
                }

                $message = 'Cannot discharge patient with ' . implode(' and ', $messages) . '. Please complete or cancel these orders first.';
                \Log::info('Discharge blocked: ' . $message);
                return response()->json(['error' => $message], 422);
            }
            
            \Log::info('Discharge allowed - no pending orders found');
        }

        DB::beginTransaction();
        try {
            $outcome = $request->outcome;

            // Map outcome to encounter status
            $statusMap = [
                'Treated'   => Encounter::STATUS_COMPLETED,
                'Admit'     => Encounter::STATUS_ADMITTED,
                'Follow-up' => Encounter::STATUS_FOLLOW_UP,
            ];

            // Ensure consultation exists and complete it
            $consultation = ClinicalConsultation::firstOrCreate(
                ['encounter_id' => $encounter->id],
                ['doctor_id' => Auth::id(), 'status' => ClinicalConsultation::STATUS_IN_PROGRESS]
            );

            $updateData = ['status' => ClinicalConsultation::STATUS_COMPLETED];
            if ($request->clinical_note) {
                $existingNote = $consultation->clinical_note ?? '';
                $updateData['clinical_note'] = trim($existingNote . "\n\nDischarge Note: " . $request->clinical_note);
            }
            $consultation->update($updateData);

            // Update encounter
            $encounterUpdate = ['status' => $statusMap[$outcome] ?? Encounter::STATUS_COMPLETED];
            if ($outcome === 'Follow-up' && $request->follow_up_date) {
                $encounterUpdate['follow_up_date'] = $request->follow_up_date;
            }
            $encounter->update($encounterUpdate);

            // Handle admission: create record + mark bed occupied
            if ($outcome === 'Admit') {
                $admission = Admission::create([
                    'id'                     => (string) Str::uuid(),
                    'encounter_id'           => $encounter->id,
                    'patient_id'             => $encounter->patient_id,
                    'facility_id'            => Auth::user()->facility_id,
                    'ward_id'                => $request->ward_id,
                    'bed_id'                 => $request->bed_id,
                    'consultant_id'          => Auth::id(),
                    'admitted_by'            => Auth::id(),
                    'admission_date'         => now(),
                    'admission_type'         => $request->admission_type,
                    'condition_on_admission' => $request->condition_on_admission,
                    'admission_notes'        => $request->admission_notes,
                    'is_active'              => true,
                ]);

                // Mark bed as occupied
                if ($request->bed_id) {
                    Bed::where('id', $request->bed_id)->update(['is_occupied' => true]);
                }

                $ward = Ward::find($request->ward_id);
                $bed  = $request->bed_id ? Bed::find($request->bed_id) : null;
                $desc = 'Patient admitted to ' . ($ward->name ?? 'ward')
                      . ($bed ? ', ' . $bed->name : '')
                      . '. Type: ' . ucfirst($request->admission_type) . '.';
            } else {
                $desc = match($outcome) {
                    'Treated'   => 'Patient treated and discharged.',
                    'Follow-up' => 'Patient scheduled for follow-up.' . ($request->follow_up_date ? ' Date: ' . $request->follow_up_date : ''),
                };
            }

            EncounterAction::create([
                'encounter_id' => $encounter->id,
                'user_id'      => Auth::id(),
                'action_type'  => ActionType::CONSULTATION,
                'description'  => $desc,
                'action_time'  => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $desc,
                'redirect' => route('doctor.queue'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Get rooms for a ward (with available bed count)
     */
    public function getRoomsByWard(Ward $ward)
    {
        $rooms = Room::where('ward_id', $ward->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->withCount(['beds as available_beds' => function ($q) {
                $q->where('is_occupied', false)->where('is_active', true);
            }])
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'name'           => $r->name,
                'available_beds' => $r->available_beds,
            ]);

        return response()->json($rooms);
    }

    /**
     * AJAX: Get available beds for a room
     */
    public function getBedsByRoom(Room $room)
    {
        $beds = Bed::where('room_id', $room->id)
            ->where('is_active', true)
            ->where('is_occupied', false)
            ->orderBy('name')
            ->get()
            ->map(fn($b) => [
                'id'   => $b->id,
                'name' => $b->name,
            ]);

        return response()->json($beds);
    }

    /**
     * Recall (delete) a pending prescription item — doctor only, before dispensation
     */
    public function recallPrescriptionItem(Request $request, PrescriptionItem $item)
    {
        $item->load(['drug', 'prescription.consultation.encounter']);

        if ($item->dispensing_status === PrescriptionItem::STATUS_DISPENSED) {
            return response()->json(['error' => 'Cannot recall an already dispensed item.'], 422);
        }

        DB::beginTransaction();
        try {
            $drugName = $item->drug?->name ?? 'Unknown drug';
            $prescription = $item->prescription;

            $item->delete();

            // Sync prescription status after deletion
            $prescription->load('items');
            $total     = $prescription->items->count();
            $dispensed = $prescription->items->where('dispensing_status', PrescriptionItem::STATUS_DISPENSED)->count();

            if ($total === 0) {
                // No items left — cancel the prescription
                $prescription->update(['status' => Prescription::STATUS_CANCELLED]);
            } elseif ($dispensed === $total) {
                // All remaining items are dispensed
                $prescription->update(['status' => Prescription::STATUS_DISPENSED]);
            } elseif ($dispensed > 0) {
                // Some dispensed, some pending
                $prescription->update(['status' => Prescription::STATUS_PARTIAL]);
            } else {
                // All pending
                $prescription->update(['status' => Prescription::STATUS_PENDING]);
            }

            // Log action
            $encounter = $prescription->consultation?->encounter;
            if ($encounter) {
                EncounterAction::create([
                    'encounter_id' => $encounter->id,
                    'user_id'      => Auth::id(),
                    'action_type'  => ActionType::PRESCRIPTION,
                    'description'  => 'Recalled (deleted) prescription item: ' . $drugName . '.',
                    'action_time'  => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $drugName . ' recalled successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to recall item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Consultation
     */
    public function storeConsultation(Request $request, Encounter $encounter)
    {
        $request->validate([
            'presenting_complaints' => 'required|string',
            'physical_examination'  => 'nullable|string',
            'clinical_note'         => 'nullable|string',
            'investigation_required'=> 'nullable|string',
            'provisional_diagnosis' => 'nullable|array',
            'confirmed_diagnosis'   => 'nullable|array',
            'outcome'               => 'nullable|in:Improved,Refer,Admit,Discharged,Follow-up',
            'follow_up_date'        => 'nullable|date|after:today',
            'drug_items'            => 'nullable|array',
            'drug_items.*.drug_id'  => 'nullable|exists:drugs,id',
        ]);

        DB::beginTransaction();
        try {
            $invRequired = $request->investigation_required === 'Yes';

            // Compile history
            $historyParts = array_filter([
                $request->presenting_complaints,
                $request->past_medical_history  ? "PMH: "     . $request->past_medical_history  : null,
                $request->past_surgical_history ? "PSH: "     . $request->past_surgical_history : null,
                $request->drug_history          ? "Drug Hx: " . $request->drug_history          : null,
                $request->allergy_history       ? "Allergy: " . $request->allergy_history       : null,
                $request->social_history        ? "Social: "  . $request->social_history        : null,
            ]);

            // Compile examination
            $examParts = array_filter([
                $request->general_examination ? "General: " . $request->general_examination : null,
                $request->physical_examination,
            ]);

            // Compile treatment notes
            $treatmentNotes = array_filter([
                $request->filled('procedures') ? "Procedures: " . implode(', ', $request->procedures) : null,
                $request->filled('treatment_types') ? "Treatment types: " . implode(', ', $request->treatment_types) : null,
                $request->filled('referral_to') ? "Referral to: {$request->referral_to} ({$request->referral_urgency}). Reason: {$request->referral_reason}" : null,
                $request->clinical_note,
            ]);

            // Create consultation
            $consultation = ClinicalConsultation::create([
                'encounter_id'           => $encounter->id,
                'doctor_id'              => Auth::id(),
                'presenting_complaints'  => implode("\n", $historyParts),
                'history_of_present_illness' => $request->past_medical_history,
                'physical_examination'   => implode("\n", $examParts),
                'clinical_note'          => implode("\n", $treatmentNotes),
                'investigation_required' => $invRequired,
                'investigation_note'     => $request->investigation_note,
                'status'                 => ClinicalConsultation::STATUS_IN_PROGRESS,
            ]);

            // Provisional diagnoses
            foreach ((array) $request->provisional_diagnosis as $diagnosisId) {
                if (!empty($diagnosisId)) {
                    ClinicalDiagnosis::create([
                        'clinical_consultation_id' => $consultation->id,
                        'icd_code_id'              => $diagnosisId,
                        'diagnosis_type'           => 'Provisional',
                    ]);
                }
            }

            // Confirmed diagnoses
            foreach ((array) $request->confirmed_diagnosis as $diagnosisId) {
                if (!empty($diagnosisId)) {
                    ClinicalDiagnosis::create([
                        'clinical_consultation_id' => $consultation->id,
                        'icd_code_id'              => $diagnosisId,
                        'diagnosis_type'           => 'Confirmed',
                    ]);
                }
            }

            // Investigations
            if ($invRequired) {
                if ($request->filled('lab_tests')) {
                    Investigation::create([
                        'clinical_consultation_id' => $consultation->id,
                        'type'         => 'Laboratory',
                        'category'     => 'General',
                        'tests'        => json_encode($request->lab_tests),
                        'notes'        => $request->investigation_note,
                        'status'       => 'Pending',
                        'requested_by' => Auth::id(),
                    ]);
                }
                if ($request->filled('radiology_tests')) {
                    Investigation::create([
                        'clinical_consultation_id' => $consultation->id,
                        'type'         => 'Radiology',
                        'category'     => 'General',
                        'tests'        => json_encode($request->radiology_tests),
                        'notes'        => $request->investigation_note,
                        'status'       => 'Pending',
                        'requested_by' => Auth::id(),
                    ]);
                }
                if ($request->filled('other_services')) {
                    Investigation::create([
                        'clinical_consultation_id' => $consultation->id,
                        'type'         => 'Other',
                        'category'     => 'General',
                        'tests'        => json_encode($request->other_services),
                        'notes'        => $request->investigation_note,
                        'status'       => 'Pending',
                        'requested_by' => Auth::id(),
                    ]);
                }
            }

            // Prescription from drug_items table (new redesigned form)
            // (Note: This is now primarily handled by the 'Send to Pharmacy' button in step 4,
            // but we keep this as a fallback if they submit the form without sending first)
            $drugItems = array_filter((array) $request->drug_items, fn($i) => !empty($i['drug_id'] ?? null));
            if (!empty($drugItems)) {
                // Only create if a prescription doesn't already exist for this consultation
                $existingPrescription = Prescription::where('clinical_consultation_id', $consultation->id)->first();
                
                if (!$existingPrescription) {
                    $prescriptionNumber = 'RX-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                    
                    $prescription = Prescription::create([
                        'clinical_consultation_id' => $consultation->id,
                        'prescription_number'      => $prescriptionNumber,
                        'prescribed_by'            => Auth::id(),
                        'status'                   => Prescription::STATUS_PENDING,
                        'prescription_date'        => now(),
                    ]);
                    
                    foreach ($drugItems as $item) {
                        $freq = (int) ($item['frequency'] ?? 3);
                        $dur  = (int) ($item['duration']  ?? 5);
                        PrescriptionItem::create([
                            'prescription_id'   => $prescription->id,
                            'drug_id'           => $item['drug_id'],
                            'dosage'            => $item['dosage']       ?? 'As directed',
                            'frequency'         => $freq,
                            'duration'          => $dur,
                            'quantity'          => $freq * $dur,
                            'instructions'      => $item['instructions'] ?? null,
                            'dispensing_status' => PrescriptionItem::STATUS_PENDING,
                        ]);
                    }
                }
            }

            // Update encounter status
            $encounter->update([
                'status' => Encounter::STATUS_IN_CONSULTATION,
            ]);
            
            // Clear the draft
            session()->forget('consultation_draft_' . $encounter->id);

            // Log the action
            EncounterAction::create([
                'encounter_id' => $encounter->id,
                'user_id' => Auth::id(),
                'action_type' => ActionType::CONSULTATION,
                'description' => 'Clinical consultation started by Dr. ' . Auth::user()->name,
                'action_time' => now(),
            ]);

            DB::commit();

            return redirect()->route('doctor.consultation.show', $consultation)
                ->with('success', 'Consultation created successfully. You can now add prescriptions and investigations.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create consultation: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * View Consultation
     */
    public function showConsultation(ClinicalConsultation $consultation)
    {
        $consultation->load([
            'encounter.patient',
            'encounter.vitalSigns',
            'encounter.program',
            'doctor',
            'diagnoses.icdCode',
            'prescriptions.items.drug',
            'investigations',
            'procedures'
        ]);
        
        // Get drugs for prescription modal
        $drugs = Drug::orderBy('name')->get();
        
        // Get ICD codes
        $icdCodes = IcdCode::orderBy('code')->get();
        
        return view('doctor.consultation.show', compact('consultation', 'drugs', 'icdCodes'));
    }

    /**
     * Add Prescription to Consultation
     */
    public function addPrescription(Request $request, ClinicalConsultation $consultation)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:drugs,id',
            'items.*.dosage' => 'required|string',
            'items.*.frequency' => 'required|integer|min:1|max:12',
            'items.*.duration' => 'required|integer|min:1|max:365',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.instructions' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create prescription
            $prescription = Prescription::create([
                'clinical_consultation_id' => $consultation->id,
                'prescribed_by' => Auth::id(),
                'status' => Prescription::STATUS_PENDING,
            ]);

            // Add prescription items
            foreach ($request->items as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'drug_id' => $item['drug_id'],
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'],
                    'quantity' => $item['quantity'],
                    'instructions' => $item['instructions'] ?? null,
                    'dispensing_status' => PrescriptionItem::STATUS_PENDING,
                ]);
            }

            // Log action
            EncounterAction::create([
                'encounter_id' => $consultation->encounter_id,
                'user_id' => Auth::id(),
                'action_type' => ActionType::PRESCRIPTION,
                'description' => 'Prescription added with ' . count($request->items) . ' item(s)',
                'action_time' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Prescription added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add prescription: ' . $e->getMessage());
        }
    }

    /**
     * Add Investigation to Consultation
     */
    public function addInvestigation(Request $request, ClinicalConsultation $consultation)
    {
        $request->validate([
            'type' => 'required|in:Laboratory,Radiology',
            'category' => 'required|string',
            'tests' => 'required|array|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Investigation::create([
                'clinical_consultation_id' => $consultation->id,
                'type' => $request->type,
                'category' => $request->category,
                'tests' => json_encode($request->tests),
                'notes' => $request->notes,
                'status' => 'Pending',
                'requested_by' => Auth::id(),
            ]);

            // Update consultation
            $consultation->update(['investigation_required' => true]);

            // Log action
            EncounterAction::create([
                'encounter_id' => $consultation->encounter_id,
                'user_id' => Auth::id(),
                'action_type' => ActionType::INVESTIGATION,
                'description' => $request->type . ' investigation requested: ' . implode(', ', $request->tests),
                'action_time' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Investigation request added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add investigation: ' . $e->getMessage());
        }
    }

    /**
     * Complete Consultation
     */
    public function completeConsultation(Request $request, ClinicalConsultation $consultation)
    {
        $request->validate([
            'outcome' => 'required|in:Improved,Refer,Admit,Discharged,Follow-up',
            'follow_up_date' => 'nullable|date|after:today',
            'final_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update consultation status
            $consultation->update([
                'status' => ClinicalConsultation::STATUS_COMPLETED,
                'clinical_note' => $consultation->clinical_note . "\n\n--- Final Notes ---\n" . $request->final_notes,
            ]);

            // Update encounter status based on outcome
            $encounterStatus = match($request->outcome) {
                'Discharged', 'Improved' => Encounter::STATUS_COMPLETED,
                'Admit' => Encounter::STATUS_ADMITTED,
                'Refer' => Encounter::STATUS_REFERRED,
                'Follow-up' => Encounter::STATUS_FOLLOW_UP,
                default => Encounter::STATUS_COMPLETED,
            };

            $consultation->encounter->update([
                'status' => $encounterStatus,
                'outcome' => $request->outcome,
                'follow_up_date' => $request->follow_up_date,
            ]);

            // Log action
            EncounterAction::create([
                'encounter_id' => $consultation->encounter_id,
                'user_id' => Auth::id(),
                'action_type' => ActionType::DISCHARGE,
                'description' => 'Consultation completed. Outcome: ' . $request->outcome,
                'action_time' => now(),
            ]);

            DB::commit();

            return redirect()->route('doctor.dashboard')
                ->with('success', 'Consultation completed successfully. Outcome: ' . $request->outcome);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete consultation: ' . $e->getMessage());
        }
    }

    /**
     * Consultation History
     */
    public function consultationHistory(Request $request)
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;

        $query = ClinicalConsultation::with(['encounter.patient', 'doctor', 'diagnoses.icdCode'])
            ->whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            });

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search patient
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('encounter.patient', function($q) use ($search) {
                $q->search($search);
            });
        }

        $consultations = $query->latest()->paginate(20);

        return view('doctor.consultation.history', compact('consultations'));
    }

    /**
     * Patient Search - Search patients by name, ID, file number
     */
    public function patientSearch(Request $request)
    {
        $query = $request->get('q');
        $patients = collect();

        if ($query && strlen($query) >= 2) {
            $patients = Patient::search($query)
                ->limit(30)
                ->get();
        }

        return view('doctor.patient.search', compact('patients', 'query'));
    }

    /**
     * Patient Dashboard - Full rich record for a patient
     */
    public function patientDashboard(Patient $patient)
    {
        // No need to load beneficiary - Patient has unified accessors

        $encounters = Encounter::with([
            'consultations.doctor',
            'consultations.diagnoses.icdCode',
            'consultations.prescriptions.items.drug',
            'consultations.investigations',
            'vitalSigns',
            'program',
            'serviceOrders.items.serviceItem.serviceType',
            'serviceOrders.items.latestResult.reportedBy',
        ])
        ->where('patient_id', $patient->id)
        ->latest('visit_date')
        ->get();

        // Aggregate data across all encounters
        $allConsultations = $encounters->flatMap(fn($e) => $e->consultations);
        $allVitals        = $encounters->flatMap(fn($e) => $e->vitalSigns)->sortByDesc('created_at');
        $allPrescriptions = $allConsultations->flatMap(fn($c) => $c->prescriptions->load('items.drug'));
        $allDiagnoses     = $allConsultations->flatMap(fn($c) => $c->diagnoses->load('icdCode'));
        $allServiceOrders = $encounters->flatMap(fn($e) => $e->serviceOrders);

        // Active vs history
        $activeConsultations  = $allConsultations->where('status', ClinicalConsultation::STATUS_IN_PROGRESS);
        $pastConsultations    = $allConsultations->where('status', ClinicalConsultation::STATUS_COMPLETED)->sortByDesc('created_at');
        $activePrescriptions  = $allPrescriptions->whereIn('status', [Prescription::STATUS_PENDING, Prescription::STATUS_PARTIAL]);
        $dispensedPrescriptions = $allPrescriptions->where('status', Prescription::STATUS_DISPENSED)->sortByDesc('created_at');
        $pendingOrders        = $allServiceOrders->where('status', 'pending');
        $completedOrders      = $allServiceOrders->where('status', 'completed')->sortByDesc('updated_at');

        // Stats
        $stats = [
            'total_visits'       => $encounters->count(),
            'last_visit'         => $encounters->first()?->visit_date,
            'active_rx'          => $activePrescriptions->count(),
            'pending_lab'        => $pendingOrders->count(),
            'total_diagnoses'    => $allDiagnoses->unique('icd_code_id')->count(),
            'follow_up'          => $encounters->whereNotNull('follow_up_date')->sortByDesc('follow_up_date')->first()?->follow_up_date,
        ];

        return view('doctor.patient.dashboard', compact(
            'patient',
            'encounters',
            'allConsultations',
            'allVitals',
            'allPrescriptions',
            'allDiagnoses',
            'allServiceOrders',
            'activeConsultations',
            'pastConsultations',
            'activePrescriptions',
            'dispensedPrescriptions',
            'pendingOrders',
            'completedOrders',
            'stats'
        ));
    }

    /**
     * Patient History - View all encounters for a patient
     */
    public function patientHistory($patientId)
    {
        $encounters = Encounter::with([
            'patient',
            'consultations.diagnoses.icdCode',
            'consultations.prescriptions.items.drug',
            'consultations.doctor',
            'vitalSigns',
            'program'
        ])
        ->where('patient_id', $patientId)
        ->latest()
        ->paginate(10);

        $patient = $encounters->first()?->patient;

        return view('doctor.patient.history', compact('encounters', 'patient'));
    }

    /**
     * Doctor Reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $facilityId = $user->facility_id;
        $startDate = $request->get('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->format('Y-m-d'));

        // Total consultations
        $totalConsultations = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();

        // By outcome
        $outcomeStats = Encounter::where('facility_id', $facilityId)
            ->whereHas('consultations')
            ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotNull('outcome')
            ->selectRaw('outcome, count(*) as count')
            ->groupBy('outcome')
            ->pluck('count', 'outcome')
            ->toArray();

        // By doctor
        $byDoctor = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('doctor_id, count(*) as count')
            ->groupBy('doctor_id')
            ->with('doctor')
            ->get()
            ->mapWithKeys(fn($item) => [$item->doctor->name ?? 'Unknown' => $item->count])
            ->toArray();

        // Top diagnoses
        $topDiagnoses = ClinicalDiagnosis::whereHas('consultation.encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('icd_code_id, count(*) as count')
            ->groupBy('icd_code_id')
            ->orderByDesc('count')
            ->take(10)
            ->with('icdCode')
            ->get();

        // Daily breakdown
        $dailyStats = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Convert dates to Carbon for the view
        $startDate = \Carbon\Carbon::parse($startDate);
        $endDate = \Carbon\Carbon::parse($endDate);
        
        // Format byDoctor for view
        $byDoctor = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereBetween('created_at', [$startDate->format('Y-m-d') . ' 00:00:00', $endDate->format('Y-m-d') . ' 23:59:59'])
            ->join('users', 'clinical_consultations.doctor_id', '=', 'users.id')
            ->selectRaw('users.name, count(*) as consultation_count')
            ->groupBy('users.id', 'users.name')
            ->get();
        
        // Format daily breakdown
        $dailyBreakdown = ClinicalConsultation::whereHas('encounter', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereBetween('created_at', [$startDate->format('Y-m-d') . ' 00:00:00', $endDate->format('Y-m-d') . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $byOutcome = $outcomeStats;

        return view('doctor.reports', compact(
            'startDate',
            'endDate',
            'totalConsultations',
            'byOutcome',
            'byDoctor',
            'topDiagnoses',
            'dailyBreakdown'
        ));
    }

    /**
     * Send selected services to lab / service dept.
     *
     * Design:
     *  - service_orders.facility_id = the facility that PERFORMS the service (destination).
     *  - One service_order per (encounter_id, facility_id) — upsert, never duplicate.
     *  - Local items  → upserted into current-facility's service_order.
     *  - Referred items → service_order for referral facility created at confirmServiceReferral.
     *  - service_order_items → skipped if (service_order_id, service_item_id) already exists.
     */
    public function sendToLab(Request $request, Encounter $encounter)
    {
        $request->validate([
            'services'   => 'required|array|min:1',
            'services.*' => 'required|string',
        ]);

        $facilityId = Auth::user()->facility_id ?? $encounter->facility_id;

        // Parse services — format: "ServiceName::uuid" (preferred) or "ServiceName" (legacy)
        // Key by UUID so duplicate-named items (e.g. two "Urinalysis") stay separate
        $explicitUuids  = [];  // uuid => name
        $nameOnlyItems  = [];  // plain names without uuid

        foreach ($request->services as $service) {
            if (str_contains($service, '::')) {
                [$name, $uuid] = explode('::', $service, 2);
                $explicitUuids[$uuid] = trim($name);
            } else {
                $nameOnlyItems[] = trim($service);
            }
        }
        $nameOnlyItems = array_filter(array_unique($nameOnlyItems));

        // Resolve items — keyed by UUID for uniqueness
        $labCategoryIds = ServiceCategory::where(function ($q) {
                $q->where('name', 'like', '%aborator%')
                  ->orWhere('name', 'like', '%Laboratory%')
                  ->orWhere('name', 'like', '%AEMATOLOG%')
                  ->orWhere('name', 'like', '%HAEMATOLOGY%')
                  ->orWhere('name', 'like', '%BLOOD%');
            })->pluck('id')->toArray();

        // 1. Resolve by explicit UUID (exact — supports multiple same-named items)
        $items = collect(); // keyed by service_item.id
        if (!empty($explicitUuids)) {
            DB::table('service_items')
                ->whereIn('id', array_keys($explicitUuids))
                ->get(['id', 'name'])
                ->each(fn($row) => $items->put($row->id, $row));
        }

        // 2. Resolve name-only items with lab-category preference
        if (!empty($nameOnlyItems)) {
            $byName = collect(); // temp: name => best item
            DB::table('service_items')
                ->whereIn('name', $nameOnlyItems)
                ->join('service_types', 'service_items.service_type_id', '=', 'service_types.id')
                ->get(['service_items.id', 'service_items.name', 'service_types.service_category_id'])
                ->each(function ($row) use (&$byName, $labCategoryIds) {
                    if (!$byName->has($row->name)) {
                        $byName->put($row->name, $row);
                    } else {
                        $existing = $byName->get($row->name);
                        if (in_array($row->service_category_id, $labCategoryIds)
                            && !in_array($existing->service_category_id, $labCategoryIds)) {
                            $byName->put($row->name, $row);
                        }
                    }
                });
            $byName->each(fn($row) => $items->put($row->id, $row));
        }

        // Build $names list (used downstream for referral checks etc.)
        $names = $items->pluck('name')->toArray();

        $localItems    = [];
        $needsReferral = [];

        foreach ($items as $item) {

            $atFacility = DB::table('facility_services')
                ->where('facility_id', $facilityId)
                ->where('service_item_id', $item->id)
                ->where('is_available', 1)
                ->exists();

            if ($atFacility) {
                $localItems[] = $item;
            } else {
                // Other facilities that offer this service?
                $otherFacilities = DB::table('facility_services')
                    ->join('facilities', 'facility_services.facility_id', '=', 'facilities.id')
                    ->where('facility_services.service_item_id', $item->id)
                    ->where('facility_services.is_available', 1)
                    ->select('facilities.id', 'facilities.name', 'facilities.lga', 'facilities.type')
                    ->orderBy('facilities.name')
                    ->get();

                if ($otherFacilities->isEmpty()) {
                    // No facility offers it — still handle locally
                    $localItems[] = $item;
                } else {
                    $existingRef = DB::table('service_referrals')
                        ->where('encounter_id', $encounter->id)
                        ->where('service_item_id', $item->id)
                        ->select('id', 'to_facility_id', 'status', 'reason')
                        ->first();

                    $needsReferral[] = [
                        'id'               => $item->id,
                        'name'             => $item->name,
                        'facilities'       => $otherFacilities,
                        'existing_referral' => $existingRef ? [
                            'to_facility_id' => $existingRef->to_facility_id,
                            'status'         => $existingRef->status,
                            'reason'         => $existingRef->reason,
                        ] : null,
                    ];
                }
            }
        }

        $orderSummary = null;

        if (!empty($localItems)) {
            DB::beginTransaction();
            try {
                // Upsert: one service_order per (encounter, facility)
                $order = ServiceOrder::firstOrCreate(
                    ['encounter_id' => $encounter->id, 'facility_id'  => $facilityId],
                    [
                        'patient_id'   => $encounter->patient_id,
                        'ordered_by'   => Auth::id(),
                        'order_number' => 'SO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                        'status'       => 'pending',
                    ]
                );

                // Reopen if previously cancelled
                if ($order->status === 'cancelled') {
                    $order->update(['status' => 'pending']);
                }

                $newlyAdded = 0;
                foreach ($localItems as $item) {
                    $alreadyExists = DB::table('service_order_items')
                        ->where('service_order_id', $order->id)
                        ->where('service_item_id', $item->id)
                        ->exists();

                    if (!$alreadyExists) {
                        ServiceOrderItem::create([
                            'service_order_id'        => $order->id,
                            'service_item_id'         => $item->id,
                            'authorization_code'      => 'AUTH' . now()->format('Ymd') . rand(1000, 9999),
                            'authorization_expires_at'=> now()->addDays(30),
                            'status'                  => 'pending',
                        ]);
                        $newlyAdded++;
                    }
                }

                // Upsert Investigation record
                $consultation = ClinicalConsultation::where('encounter_id', $encounter->id)->first();
                Investigation::updateOrCreate(
                    ['encounter_id' => $encounter->id, 'type' => 'Service Order'],
                    [
                        'clinical_consultation_id' => $consultation?->id,
                        'patient_id'               => $encounter->patient_id,
                        'facility_id'              => $facilityId,
                        'ordered_by'               => Auth::id(),
                        'requested_by'             => Auth::id(),
                        'category'                 => 'General',
                        'tests'                    => json_encode(array_column($localItems, 'name')),
                        'status'                   => 'Pending',
                    ]
                );

                if ($newlyAdded > 0) {
                    EncounterAction::create([
                        'encounter_id' => $encounter->id,
                        'user_id'      => Auth::id(),
                        'action_type'  => ActionType::INVESTIGATION,
                        'description'  => $newlyAdded . ' service(s) added to order ' . $order->order_number . '.',
                        'action_time'  => now(),
                    ]);
                }

                DB::commit();
                $orderSummary = [
                    'order_number' => $order->order_number,
                    'added'        => $newlyAdded,
                    'total_local'  => count($localItems),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to create service order: ' . $e->getMessage()], 500);
            }
        }

        return response()->json([
            'success'        => true,
            'order_created'  => $orderSummary,
            'needs_referral' => $needsReferral,
            'show_choice'    => true,
            'message'        => 'Services sent to lab successfully. What would you like to do next?',
        ]);
    }

    /**
     * Confirm service referrals.
     *
     * For each referral:
     *  1. Upsert service_referral record.
     *  2. Upsert service_order for the referral facility (facility_id = to_facility_id).
     *  3. Add service_order_item to that order if not already present.
     */
    public function confirmServiceReferral(Request $request, Encounter $encounter)
    {
        $request->validate([
            'referrals'                    => 'required|array|min:1',
            'referrals.*.service_item_id'  => 'required|string',
            'referrals.*.to_facility_id'   => 'required|string|exists:facilities,id',
            'referrals.*.reason'           => 'nullable|string',
        ]);

        $fromFacilityId = Auth::user()->facility_id ?? $encounter->facility_id;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($request->referrals as $ref) {
                $toFacilityId  = $ref['to_facility_id'];
                $serviceItemId = $ref['service_item_id'];
                $reason        = $ref['reason'] ?? 'Doctor referral for investigation';

                // ── 1. Upsert service_referral ──────────────────────────────
                $existingRef = DB::table('service_referrals')
                    ->where('encounter_id', $encounter->id)
                    ->where('service_item_id', $serviceItemId)
                    ->first();

                if ($existingRef) {
                    if ($existingRef->status === 'pending') {
                        DB::table('service_referrals')
                            ->where('id', $existingRef->id)
                            ->update([
                                'to_facility_id' => $toFacilityId,
                                'reason'         => $reason,
                                'updated_at'     => now(),
                            ]);
                        $updated++;
                    } else {
                        $skipped++;
                        continue; // Non-pending referral — skip order creation too
                    }
                } else {
                    ServiceReferral::create([
                        'encounter_id'     => $encounter->id,
                        'from_facility_id' => $fromFacilityId,
                        'to_facility_id'   => $toFacilityId,
                        'referral_type'    => 'service',
                        'service_item_id'  => $serviceItemId,
                        'reason'           => $reason,
                        'status'           => 'pending',
                    ]);
                    $created++;
                }

                // ── 2. Upsert service_order for the referral facility ───────
                $refOrder = ServiceOrder::firstOrCreate(
                    ['encounter_id' => $encounter->id, 'facility_id' => $toFacilityId],
                    [
                        'patient_id'   => $encounter->patient_id,
                        'ordered_by'   => Auth::id(),
                        'order_number' => 'SO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                        'status'       => 'pending',
                    ]
                );

                if ($refOrder->status === 'cancelled') {
                    $refOrder->update(['status' => 'pending']);
                }

                // ── 3. Add service_order_item if not already there ──────────
                $itemExists = DB::table('service_order_items')
                    ->where('service_order_id', $refOrder->id)
                    ->where('service_item_id', $serviceItemId)
                    ->exists();

                if (!$itemExists) {
                    ServiceOrderItem::create([
                        'service_order_id'        => $refOrder->id,
                        'service_item_id'         => $serviceItemId,
                        'authorization_code'      => 'AUTH' . now()->format('Ymd') . rand(1000, 9999),
                        'authorization_expires_at'=> now()->addDays(30),
                        'status'                  => 'pending',
                    ]);
                }
            }

            if ($created + $updated > 0) {
                EncounterAction::create([
                    'encounter_id' => $encounter->id,
                    'user_id'      => Auth::id(),
                    'action_type'  => ActionType::REFERRAL,
                    'description'  => ($created + $updated) . ' service referral(s) confirmed.',
                    'action_time'  => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save referrals: ' . $e->getMessage()], 500);
        }

        $msg = [];
        if ($created) $msg[] = "$created created";
        if ($updated) $msg[] = "$updated updated";
        if ($skipped) $msg[] = "$skipped skipped (non-pending)";

        return response()->json([
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'message' => implode(', ', $msg),
        ]);
    }

    /**
     * Return current service order + referral status for an encounter (for status monitoring panel).
     */
    public function serviceOrderStatus(Encounter $encounter)
    {
        // Get unified list of services - each service appears only once
        // Priority: Referral > Order (if both exist, show as referral)
        $services = DB::table('service_items')
            ->leftJoin('service_order_items', function($join) use ($encounter) {
                $join->on('service_items.id', '=', 'service_order_items.service_item_id')
                     ->whereExists(function($query) use ($encounter) {
                         $query->select(DB::raw(1))
                               ->from('service_orders')
                               ->where('service_orders.encounter_id', $encounter->id)
                               ->where('service_orders.id', '=', DB::raw('service_order_items.service_order_id'));
                     });
            })
            ->leftJoin('service_referrals', function($join) use ($encounter) {
                $join->on('service_items.id', '=', 'service_referrals.service_item_id')
                     ->where('service_referrals.encounter_id', $encounter->id);
            })
            ->leftJoin('facilities as to_f', 'service_referrals.to_facility_id', '=', 'to_f.id')
            ->leftJoin('service_orders', function($join) use ($encounter) {
                $join->on('service_orders.id', '=', 'service_order_items.service_order_id')
                     ->where('service_orders.encounter_id', $encounter->id);
            })
            ->leftJoin('facilities as order_f', 'service_orders.facility_id', '=', 'order_f.id')
            ->select(
                'service_items.id as service_item_id',
                'service_items.name',
                DB::raw('
                    CASE 
                        WHEN service_referrals.id IS NOT NULL THEN service_referrals.status
                        WHEN service_order_items.id IS NOT NULL THEN service_order_items.status
                        ELSE NULL
                    END as status
                '),
                DB::raw('
                    CASE 
                        WHEN service_referrals.id IS NOT NULL THEN "referral"
                        WHEN service_order_items.id IS NOT NULL THEN "order"
                        ELSE NULL
                    END as type
                '),
                'service_referrals.id as referral_id',
                'service_order_items.id as order_item_id',
                'to_f.name as referral_facility',
                'order_f.name as order_facility',
                'service_orders.order_number',
                'service_referrals.created_at as referral_created_at',
                'service_order_items.created_at as order_created_at'
            )
            ->where(function($query) {
                $query->whereNotNull('service_order_items.id')
                      ->orWhereNotNull('service_referrals.id');
            })
            ->orderByRaw('
                CASE 
                    WHEN service_referrals.created_at IS NOT NULL THEN service_referrals.created_at
                    ELSE service_order_items.created_at
                END DESC
            ')
            ->get();

        return response()->json(['services' => $services]);
    }

    /**
     * Remove a single service_order_item by service name for this encounter.
     * Deletes the item from whichever pending order it belongs to.
     * If the order has no remaining items after removal, the order is also deleted.
     */
    public function removeServiceOrderItem(Request $request, Encounter $encounter)
    {
        $request->validate(['service_name' => 'required|string']);

        $serviceItem = DB::table('service_items')
            ->where('name', $request->service_name)
            ->first();

        if (!$serviceItem) {
            return response()->json(['error' => 'Service not found.'], 404);
        }

        // Find the pending order for this encounter that contains this item
        $orderItem = DB::table('service_order_items')
            ->join('service_orders', 'service_order_items.service_order_id', '=', 'service_orders.id')
            ->where('service_orders.encounter_id', $encounter->id)
            ->where('service_orders.status', 'pending')
            ->where('service_order_items.service_item_id', $serviceItem->id)
            ->select('service_order_items.id as item_id', 'service_orders.id as order_id')
            ->first();

        DB::beginTransaction();
        try {
            // Delete the order item if it exists
            if ($orderItem) {
                ServiceOrderItem::where('id', $orderItem->item_id)->delete();

                // Delete the parent order if it now has no items left
                $remaining = DB::table('service_order_items')
                    ->where('service_order_id', $orderItem->order_id)
                    ->count();

                if ($remaining === 0) {
                    ServiceOrder::where('id', $orderItem->order_id)->delete();
                }
            }

            // Always delete any pending referral for this service on this encounter
            DB::table('service_referrals')
                ->where('encounter_id', $encounter->id)
                ->where('service_item_id', $serviceItem->id)
                ->where('status', 'pending')
                ->delete();

            DB::commit();

            // Also remove from session draft so the list item no longer shows as pre-checked
            $this->_removeServicesFromDraft($encounter->id, [$request->service_name]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to remove item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * View lab results for a service order
     */
    public function viewLabResults(ServiceOrder $order): \Illuminate\View\View
    {
        // Load order with all necessary relationships
        $order->load([
            'encounter.patient',
            'encounter.vitalSigns',
            'encounter.consultations.doctor',
            'encounter.consultations.diagnoses',
            'orderedBy',
            'items.serviceItem.serviceType.serviceCategory',
            'items.serviceResults.reportedBy'
        ]);

        // Doctors can view lab results from any facility (inter-hospital consultation)
        // No facility restriction - doctors may need to review results from other hospitals

        return view('doctor.lab-results.view', compact('order'));
    }

    /**
     * Recall (cancel) a pending service order — cancels the order, its items, and any pending referrals.
     */
    public function recallServiceOrder(Request $request, Encounter $encounter, ServiceOrder $order)
    {
        if ($order->encounter_id !== $encounter->id) {
            return response()->json(['error' => 'Order does not belong to this encounter.'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Only pending orders can be recalled. This order is already ' . $order->status . '.'], 422);
        }

        $orderNumber = $order->order_number; // capture before delete

        DB::beginTransaction();
        try {
            // Collect service_item_ids before deleting items
            $itemIds = DB::table('service_order_items')
                ->where('service_order_id', $order->id)
                ->pluck('service_item_id');

            // Delete order items
            ServiceOrderItem::where('service_order_id', $order->id)->delete();

            // Delete the order itself
            $order->delete();

            // Delete pending referrals linked to the same service items for this encounter
            if ($itemIds->isNotEmpty()) {
                DB::table('service_referrals')
                    ->where('encounter_id', $encounter->id)
                    ->where('status', 'pending')
                    ->whereIn('service_item_id', $itemIds)
                    ->delete();
            }

            // Log the action
            EncounterAction::create([
                'encounter_id' => $encounter->id,
                'user_id'      => Auth::id(),
                'action_type'  => ActionType::INVESTIGATION,
                'description'  => 'Service order ' . $orderNumber . ' recalled and deleted by doctor.',
                'action_time'  => now(),
            ]);

            // Also purge all items of this order from the session draft
            $itemNames = DB::table('service_items')
                ->whereIn('id', $itemIds)
                ->pluck('name')
                ->toArray();
            $this->_removeServicesFromDraft($encounter->id, $itemNames);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order ' . $orderNumber . ' recalled and deleted.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to recall order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove given service names from the consultation session draft
     * (clears pre-selection of lab_tests / radiology_tests / other_services).
     */
    private function _removeServicesFromDraft(string $encounterId, array $names): void
    {
        if (empty($names)) return;
        $key   = 'consultation_draft_' . $encounterId;
        $draft = session($key, []);
        foreach (['lab_tests', 'radiology_tests', 'other_services'] as $field) {
            if (!empty($draft[$field])) {
                $draft[$field] = array_values(array_filter(
                    $draft[$field],
                    fn($s) => !in_array($s, $names)
                ));
            }
        }
        session([$key => $draft]);
    }
}
