<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceOrderItem;
use App\Models\ServiceCategory;
use App\Models\ServiceResult;

class RadiologistController extends Controller
{
    /**
     * Return IDs for radiology-specific service categories only.
     * Matches: "Radiology Services", "Radiological Services", and any future radiology cats.
     */
    private function radiologyCategoryIds(): array
    {
        return ServiceCategory::where('name', 'like', '%radiol%')
            ->pluck('id')
            ->toArray();
    }

    private function baseQuery(array $catIds)
    {
        $facilityId = Auth::user()->facility_id;

        return ServiceOrderItem::whereHas('serviceItem.serviceType', function ($q) use ($catIds) {
                $q->whereIn('service_category_id', $catIds);
            })
            ->whereHas('serviceOrder', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            });
    }

    /**
     * Radiologist dashboard — mirrors Lab dashboard structure.
     */
    public function dashboard(): \Illuminate\View\View
    {
        $catIds = $this->radiologyCategoryIds();
        $base   = $this->baseQuery($catIds);

        $stats = [
            'pending'         => (clone $base)->where('status', 'pending')->count(),
            'in_progress'     => (clone $base)->where('status', 'in_progress')->count(),
            'completed_today' => (clone $base)->where('status', 'completed')->whereDate('updated_at', today())->count(),
            'completed_total' => (clone $base)->where('status', 'completed')->count(),
            'today_total'     => (clone $base)->whereDate('created_at', today())->count(),
            'week_total'      => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        return view('radiologist.dashboard', compact('stats'));
    }

    /**
     * Radiologist worklist queue.
     */
    public function queue(Request $request): \Illuminate\View\View
    {
        $catIds     = $this->radiologyCategoryIds();
        $facilityId = Auth::user()->facility_id;
        $tab        = $request->get('tab', 'pending');

        $statuses = match ($tab) {
            'in_progress' => ['in_progress'],
            'completed'   => ['completed'],
            default       => ['pending'],
        };

        $query = ServiceOrderItem::with([
                'serviceItem.serviceType.serviceCategory',
                'serviceOrder.encounter.patient',
                'serviceOrder.orderedBy',
                'latestResult',
            ])
            ->whereHas('serviceItem.serviceType', function ($q) use ($catIds) {
                $q->whereIn('service_category_id', $catIds);
            })
            ->whereHas('serviceOrder', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereIn('status', $statuses);

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('serviceOrder.encounter.patient', function ($q) use ($search) {
                $q->search($search);
            });
        }

        if ($request->filled('program')) {
            $query->whereHas('serviceOrder.encounter', function ($q) use ($request) {
                $q->where('program_id', $request->program);
            });
        }

        $orders = $query->latest()->paginate(20)->appends($request->query());

        $base   = $this->baseQuery($catIds);
        $counts = [
            'pending'     => (clone $base)->where('status', 'pending')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'completed'   => (clone $base)->where('status', 'completed')->count(),
        ];

        $programs = \App\Models\Program::orderBy('name')->pluck('name', 'id');

        return view('radiologist.queue', compact('orders', 'tab', 'counts', 'programs'));
    }

    /**
     * Show a single radiology order with result recording form.
     */
    public function orderShow(ServiceOrderItem $item): \Illuminate\View\View
    {
        $item->load([
            'serviceItem.serviceType.serviceCategory',
            'serviceOrder.encounter.patient',
            'serviceOrder.encounter.vitalSigns',
            'serviceOrder.encounter.consultations.doctor',
            'serviceOrder.encounter.consultations.diagnoses',
            'serviceOrder.orderedBy',
            'serviceResults.reportedBy',
        ]);

        return view('radiologist.order', compact('item'));
    }

    /**
     * Update the status of a radiology order (pending ↔ in_progress).
     */
    public function updateStatus(Request $request, ServiceOrderItem $item): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $item->update(['status' => $request->status]);

        return back()->with('success', 'Status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }

    /**
     * Record a radiology result and mark the order as completed.
     */
    public function recordResult(Request $request, ServiceOrderItem $item): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'result_value'    => 'nullable|string|max:500',
            'result_note'     => 'nullable|string|max:3000',
            'reference_range' => 'nullable|string|max:500',
            'remark'          => 'nullable|string|max:1000',
            'result_files.*'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $documentUrl = null;
            if ($request->hasFile('result_files')) {
                $filePaths = [];
                foreach ($request->file('result_files') as $file) {
                    if ($file->isValid()) {
                        $filePaths[] = $file->store('rad-results', 'public');
                    }
                }
                if (!empty($filePaths)) {
                    $documentUrl = json_encode($filePaths);
                }
            }

            ServiceResult::create([
                'service_order_item_id' => $item->id,
                'result_value'          => $request->result_value,
                'result_note'           => $request->result_note,
                'reference_range'       => $request->reference_range,
                'remark'                => $request->remark,
                'result_document_url'   => $documentUrl,
                'reported_by'           => Auth::id(),
                'reported_at'           => now(),
                'status'                => 'completed',
            ]);

            $item->update(['status' => 'completed']);

            $serviceOrder        = $item->serviceOrder;
            $allItemsCompleted   = $serviceOrder->items()->where('status', '!=', 'completed')->count() === 0;
            if ($allItemsCompleted) {
                $serviceOrder->update(['status' => 'completed']);
            }

            DB::commit();
            return redirect()->route('radiologist.queue')->with('success', 'Radiology report recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record result: ' . $e->getMessage()]);
        }
    }

    /**
     * Radiology results history.
     */
    public function history(Request $request): \Illuminate\View\View
    {
        $catIds     = $this->radiologyCategoryIds();
        $facilityId = Auth::user()->facility_id;

        $query = ServiceOrderItem::with([
                'serviceItem',
                'serviceOrder.encounter.patient.beneficiary',
                'serviceOrder.orderedBy',
                'latestResult.reportedBy',
            ])
            ->whereHas('serviceItem.serviceType', function ($q) use ($catIds) {
                $q->whereIn('service_category_id', $catIds);
            })
            ->whereHas('serviceOrder', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->where('status', 'completed');

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('serviceOrder.encounter.patient', function ($q) use ($search) {
                $q->where('file_number', 'like', "%$search%")
                  ->orWhereHas('beneficiary', fn ($b) => $b->where('fullname', 'like', "%$search%"));
            });
        }

        if ($request->filled('program')) {
            $query->whereHas('serviceOrder.encounter', function ($q) use ($request) {
                $q->where('program_id', $request->program);
            });
        }

        if ($request->date_from) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        $orders   = $query->latest()->paginate(20)->appends($request->query());
        $programs = \App\Models\Program::orderBy('name')->pluck('name', 'id');

        return view('radiologist.history', compact('orders', 'programs'));
    }
}
