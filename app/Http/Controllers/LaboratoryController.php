<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrder;
use App\Models\ServiceResult;
use App\Models\ServiceCategory;
use App\Models\Investigation;
use Illuminate\Support\Facades\Log;

class LaboratoryController extends Controller
{
    private function labCategoryIds(): array
    {
        // Include ALL service categories - all services are processed through the lab system
        // This ensures comprehensive tracking and coordination of all patient services
        return ServiceCategory::pluck('id')->toArray();
    }

    private function labBaseQuery(array $labCatIds)
    {
        $facilityId = Auth::user()->facility_id;
        return ServiceOrderItem::whereHas('serviceItem.serviceType', function ($q) use ($labCatIds) {
                $q->whereIn('service_category_id', $labCatIds);
            })
            ->whereHas('serviceOrder', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            });
    }

    
    public function dashboard(): \Illuminate\View\View
    {
        $labCatIds = $this->labCategoryIds();
        $base = $this->labBaseQuery($labCatIds);

        $stats = [
            'pending'        => (clone $base)->where('status', 'pending')->count(),
            'in_progress'    => (clone $base)->where('status', 'in_progress')->count(),
            'completed_today'=> (clone $base)->where('status', 'completed')->whereDate('updated_at', today())->count(),
            'completed_total'=> (clone $base)->where('status', 'completed')->count(),
            'today_total'    => (clone $base)->whereDate('created_at', today())->count(),
            'week_total'     => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        return view('laboratory.dashboard', compact('stats'));
    }

    public function queue(Request $request): \Illuminate\View\View
    {
        $labCatIds = $this->labCategoryIds();
        $facilityId = Auth::user()->facility_id;
        $tab = $request->get('tab', 'pending');

        $statuses = match($tab) {
            'in_progress' => ['in_progress'],
            'completed'   => ['completed'],
            default       => ['pending'],
        };

        $query = ServiceOrderItem::with([
                'serviceItem.serviceType.serviceCategory',
                'serviceOrder.encounter.patient.beneficiary',
                'serviceOrder.orderedBy',
                'latestResult',
            ])
            ->whereHas('serviceItem.serviceType', function ($q) use ($labCatIds) {
                $q->whereIn('service_category_id', $labCatIds);
            })
            ->whereHas('serviceOrder', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->whereIn('status', $statuses);

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('serviceOrder.encounter.patient', function ($q) use ($search) {
                $q->where('file_number', 'like', "%$search%")
                  ->orWhereHas('beneficiary', fn($b) => $b->where('fullname', 'like', "%$search%")
                      ->orWhere('name', 'like', "%$search%"));
            });
        }

        $orders = $query->latest()->paginate(20)->appends($request->query());

        $base = $this->labBaseQuery($labCatIds);
        $counts = [
            'pending'     => (clone $base)->where('status', 'pending')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'completed'   => (clone $base)->where('status', 'completed')->count(),
        ];

        return view('laboratory.queue', compact('orders', 'tab', 'counts'));
    }

    public function orderShow(ServiceOrderItem $item): \Illuminate\View\View
    {
        $item->load([
            'serviceItem.serviceType.serviceCategory',
            'serviceOrder.encounter.patient.beneficiary',
            'serviceOrder.encounter.vitalSigns',
            'serviceOrder.encounter.consultations.doctor',
            'serviceOrder.encounter.consultations.diagnoses',
            'serviceOrder.orderedBy',
            'serviceResults.reportedBy',
        ]);

        return view('laboratory.order', compact('item'));
    }

    public function updateStatus(Request $request, ServiceOrderItem $item): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $item->update(['status' => $request->status]);

        return back()->with('success', 'Status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }

    public function recordResult(Request $request, ServiceOrderItem $item): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'result_value'     => 'nullable|string|max:500',
            'result_note'      => 'nullable|string|max:3000',
            'reference_range'  => 'nullable|string|max:500',
            'remark'           => 'nullable|string|max:1000',
            'result_files.*'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $documentUrl = null;
            if ($request->hasFile('result_files')) {
                $filePaths = [];
                foreach ($request->file('result_files') as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('lab-results', 'public');
                        $filePaths[] = $path;
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

            DB::commit();
            return redirect()->route('laboratory.queue')->with('success', 'Result recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record result: ' . $e->getMessage()]);
        }
    }

    public function history(Request $request): \Illuminate\View\View
    {
        $labCatIds = $this->labCategoryIds();
        $facilityId = Auth::user()->facility_id;

        $query = ServiceOrderItem::with([
                'serviceItem',
                'serviceOrder.encounter.patient.beneficiary',
                'serviceOrder.orderedBy',
                'latestResult.reportedBy',
            ])
            ->whereHas('serviceItem.serviceType', function ($q) use ($labCatIds) {
                $q->whereIn('service_category_id', $labCatIds);
            })
            ->whereHas('serviceOrder', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->where('status', 'completed');

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('serviceOrder.encounter.patient', function ($q) use ($search) {
                $q->where('file_number', 'like', "%$search%")
                  ->orWhereHas('beneficiary', fn($b) => $b->where('fullname', 'like', "%$search%"));
            });
        }

        if ($request->date_from) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20)->appends($request->query());

        return view('laboratory.history', compact('orders'));
    }

    /* ── Legacy routes kept for backward compat ── */
    public function ordersIndex(): \Illuminate\View\View
    {
        return $this->queue(request());
    }

    public function uploadResults(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('laboratory.queue');
    }

    public function storeUpload(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('laboratory.queue');
    }

    public function createResult($orderId): \Illuminate\View\View
    {
        // Find the service order item
        $order = ServiceOrderItem::with(['encounter.patient', 'orderedBy', 'laboratoryTest'])->findOrFail($orderId);
        return view('laboratory.results.create', compact('order'));
    }

    public function storeResult(Request $request, $orderId): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'result_value'   => 'required|string',
            'unit'           => 'nullable|string',
            'result_status'  => 'required|in:normal,abnormal,critical',
            'interpretation' => 'nullable|string',
            'result_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $order = ServiceOrderItem::findOrFail($orderId);
        
        // Create service result
        $result = ServiceResult::create([
            'service_order_item_id' => $order->id,
            'result_value' => $request->result_value,
            'unit' => $request->unit,
            'reference_range' => $order->laboratoryTest?->reference_range,
            'remark' => $request->interpretation,
            'result_status' => $request->result_status,
            'reported_by' => Auth::id(),
            'reported_at' => now(),
        ]);

        // Handle multiple file uploads
        if ($request->hasFile('result_files')) {
            $filePaths = [];
            $uploadedFiles = $request->file('result_files');
            
            foreach ($uploadedFiles as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('lab-results', 'public');
                    if ($path) {
                        $filePaths[] = $path;
                    }
                }
            }
            
            if (!empty($filePaths)) {
                $result->update(['result_document_url' => json_encode($filePaths)]);
            }
        }

        // Update order status
        $order->update(['status' => 'completed']);

        if ($request->action === 'save_and_send') {
            // Notify doctor logic here
        return redirect()->route('laboratory.queue')->with('success', 'Result saved and sent to doctor successfully.');
        }

        return redirect()->route('laboratory.queue')->with('success', 'Result saved successfully.');
    }

    public function viewResults(Investigation $investigation): \Illuminate\View\View
    {
        $investigation->load(['encounter.patient', 'consultation']);
        return view('laboratory.results.view', compact('investigation'));
    }
}
