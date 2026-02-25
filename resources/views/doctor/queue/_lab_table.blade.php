<div style="overflow-x:auto">
  <table style="width:100%;border-collapse:separate;border-spacing:0">
    <thead>
      <tr>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Patient</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Program</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Tests Ordered</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Order No.</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Ordered By</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Wait</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;text-align:center">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($encounters as $encounter)
      @php
        $consultation = $encounter->consultations->first();
        $order = \App\Models\ServiceOrder::where('encounter_id', $encounter->id)->first();
        $hasResults = false;
        if ($order) {
            $orderItems = \App\Models\ServiceOrderItem::where('service_order_id', $order->id)
                ->join('service_items', 'service_order_items.service_item_id', '=', 'service_items.id')
                ->select('service_items.name', 'service_order_items.status', 'service_order_items.id')
                ->get();
            $hasResults = $orderItems->contains('status', 'completed');
        } else {
            $orderItems = collect();
        }
        $startTime = $order?->created_at ?? $encounter->updated_at;
        $waitMinutes = $startTime->diffInMinutes();
      @endphp
      <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafb'" onmouseout="this.style.background=''">
        <td style="padding:12px 14px;vertical-align:middle">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:#fef3c7">
              @if($encounter->patient->enrollee_photo ?? false)
                <img src="{{ $encounter->patient->enrollee_photo }}" style="width:100%;height:100%;object-fit:cover" alt="">
              @else
                <span class="material-symbols-outlined" style="font-size:18px;color:#d97706">person</span>
              @endif
            </div>
            <div>
              <div style="font-weight:600;color:#1e293b;font-size:13px">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</div>
              <div style="font-size:11px;color:#94a3b8">{{ $encounter->patient->enrollee_number ?? '' }}</div>
            </div>
          </div>
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569">{{ $encounter->program->name ?? 'N/A' }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          @if($orderItems->isNotEmpty())
          <div style="display:flex;flex-wrap:wrap;gap:4px">
            @foreach($orderItems->take(3) as $item)
              @if($item->status === 'completed')
              <a href="{{ route('doctor.lab-results.view', $order->id) }}" style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:600;background:#dcfce7;color:#166534;text-decoration:none">
                {{ $item->name }} <span class="material-symbols-outlined" style="font-size:10px">check_circle</span>
              </a>
              @else
              <span style="display:inline-flex;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:600;background:#fef3c7;color:#92400e">{{ $item->name }}</span>
              @endif
            @endforeach
            @if($orderItems->count() > 3)
            <span style="font-size:10px;color:#94a3b8;padding:2px 4px">+{{ $orderItems->count() - 3 }} more</span>
            @endif
          </div>
          @else <span style="color:#94a3b8;font-size:12px">No tests</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          @if($order)
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $order->order_number }}</span>
          @else <span style="color:#94a3b8;font-size:12px">N/A</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle;font-size:12px;color:#64748b">{{ $order?->orderedBy?->name ?? 'N/A' }}</td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $waitMinutes > 120 ? '#fee2e2' : ($waitMinutes > 60 ? '#fef3c7' : '#f1f5f9') }};color:{{ $waitMinutes > 120 ? '#991b1b' : ($waitMinutes > 60 ? '#92400e' : '#475569') }}">{{ $startTime->diffForHumans(null, true) }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle;text-align:center">
          <div style="display:flex;gap:6px;justify-content:center">
            @if($hasResults)
            <a href="{{ route('doctor.lab-results.view', $order->id) }}" style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#059669;color:#fff;text-decoration:none">
              <span class="material-symbols-outlined" style="font-size:14px">science</span> Results
            </a>
            @endif
            <a href="{{ route('doctor.consultation.start', $encounter) }}" style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:transparent;border:1.5px solid #e2e8f0;color:#64748b;text-decoration:none">
              <span class="material-symbols-outlined" style="font-size:14px">{{ $hasResults ? 'play_arrow' : 'visibility' }}</span> {{ $hasResults ? 'Continue' : 'View' }}
            </a>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;padding:48px 20px">
          <span class="material-symbols-outlined" style="font-size:52px;color:#d97706;opacity:.4">science</span>
          <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No Patients Awaiting Lab</h5>
          <p style="color:#94a3b8;font-size:13px">No patients are currently waiting for lab results.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
