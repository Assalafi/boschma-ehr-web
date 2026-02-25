<div style="overflow-x:auto">
  <table style="width:100%;border-collapse:separate;border-spacing:0">
    <thead>
      <tr>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Patient</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Program</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Prescriptions</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Prescribed By</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Wait</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;text-align:center">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($encounters as $encounter)
      @php
        $consultation = $encounter->consultations->first();
        $prescriptions = $consultation ? \App\Models\Prescription::where('clinical_consultation_id', $consultation->id)->with('items.drug')->get() : collect();
        $startTime = $prescriptions->first()?->created_at ?? $encounter->updated_at;
        $waitMinutes = $startTime->diffInMinutes();
      @endphp
      <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafb'" onmouseout="this.style.background=''">
        <td style="padding:12px 14px;vertical-align:middle">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:#e6f5ed">
              @if($encounter->patient->enrollee_photo ?? false)
                <img src="{{ 'http://eboschma.bornostate.gov.ng/storage/'$encounter->patient->enrollee_photo) }}" style="width:100%;height:100%;object-fit:cover" alt="">
              @else
                <span class="material-symbols-outlined" style="font-size:18px;color:#016634">person</span>
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
          @if($prescriptions->isNotEmpty())
          <div style="display:flex;flex-wrap:wrap;gap:4px">
            @foreach($prescriptions->take(2) as $prescription)
              @foreach($prescription->items->take(2) as $item)
              <span style="display:inline-flex;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">{{ $item->drug->name ?? 'Drug' }}</span>
              @endforeach
              @if($prescription->items->count() > 2)
              <span style="font-size:10px;color:#94a3b8;padding:2px 4px">+{{ $prescription->items->count() - 2 }}</span>
              @endif
            @endforeach
          </div>
          @else <span style="color:#94a3b8;font-size:12px">No prescriptions</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle;font-size:12px;color:#64748b">{{ $consultation?->doctor?->name ?? 'N/A' }}</td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $waitMinutes > 90 ? '#fee2e2' : ($waitMinutes > 45 ? '#fef3c7' : '#f1f5f9') }};color:{{ $waitMinutes > 90 ? '#991b1b' : ($waitMinutes > 45 ? '#92400e' : '#475569') }}">{{ $startTime->diffForHumans(null, true) }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle;text-align:center">
          <a href="{{ route('doctor.consultation.start', $encounter) }}" style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:transparent;border:1.5px solid #e2e8f0;color:#64748b;text-decoration:none">
            <span class="material-symbols-outlined" style="font-size:14px">visibility</span> View
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" style="text-align:center;padding:48px 20px">
          <span class="material-symbols-outlined" style="font-size:52px;color:#64748b;opacity:.4">medication</span>
          <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No Patients Awaiting Pharmacy</h5>
          <p style="color:#94a3b8;font-size:13px">No patients are currently waiting for medications.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
