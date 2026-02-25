<div style="overflow-x:auto">
  <table class="doc-table" style="width:100%;border-collapse:separate;border-spacing:0">
    <thead>
      <tr>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;width:50px">Queue</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;width:80px">Priority</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Patient</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Program</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Complaint</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Vitals</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Wait</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;text-align:center">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($encounters as $index => $encounter)
      @php
        $vitalSign = $encounter->vitalSigns->first();
        $rawPriority = strtolower($vitalSign?->overall_priority ?? '');
        $priority = in_array($rawPriority, ['red','critical','high','1','2']) ? 'Red'
                  : (in_array($rawPriority, ['yellow','urgent','orange','3']) ? 'Yellow' : 'Green');
        $priorityLabel = ['Red'=>'Critical','Yellow'=>'Urgent','Green'=>'Normal'];
        $bg = ['Red'=>'#fee2e2','Yellow'=>'#fef3c7','Green'=>'#e6f5ed'];
        $fg = ['Red'=>'#dc2626','Yellow'=>'#d97706','Green'=>'#016634'];
        $waitMinutes = $encounter->created_at->diffInMinutes();
      @endphp
      <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s{{ $priority == 'Red' ? ';background:#fff5f5' : '' }}" onmouseover="this.style.background='#f8fafb'" onmouseout="this.style.background='{{ $priority == 'Red' ? '#fff5f5' : '' }}'">
        <td style="padding:12px 14px;font-size:13px;vertical-align:middle">
          <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569">{{ $index + 1 }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $bg[$priority] }};color:{{ $fg[$priority] }}">
            @if($priority == 'Red')<span class="material-symbols-outlined" style="font-size:12px">warning</span>@endif
            {{ $priorityLabel[$priority] ?? $priority }}
          </span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:{{ $bg[$priority] }}">
              @if($encounter->patient->enrollee_photo ?? false)
                <img src="{{ asset('storage/' . $encounter->patient->enrollee_photo) }}" style="width:100%;height:100%;object-fit:cover" alt="">
              @else
                <span class="material-symbols-outlined" style="font-size:18px;color:{{ $fg[$priority] }}">person</span>
              @endif
            </div>
            <div>
              <div style="font-weight:600;color:#1e293b;font-size:13px">{{ $encounter->patient->enrollee_name ?? 'N/A' }}</div>
              <div style="font-size:11px;color:#94a3b8">{{ $encounter->patient->enrollee_number ?? '' }}</div>
              <div style="font-size:10px;color:#94a3b8">{{ $encounter->patient->enrollee_gender ?? '' }} | {{ $encounter->patient->enrollee_dob ? \Carbon\Carbon::parse($encounter->patient->enrollee_dob)->age . ' yrs' : '' }}</div>
            </div>
          </div>
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569">{{ $encounter->program->name ?? 'N/A' }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle;font-size:12px;color:#475569;max-width:160px">{{ Str::limit($encounter->reason_for_visit, 50) }}</td>
        <td style="padding:12px 14px;vertical-align:middle">
          @if($vitalSign)
          <div style="font-size:11px;color:#475569;line-height:1.6">
            <span><strong>T:</strong> {{ $vitalSign->temperature }}°C</span><br>
            <span><strong>BP:</strong> {{ $vitalSign->blood_pressure_systolic }}/{{ $vitalSign->blood_pressure_diastolic }}</span><br>
            <span><strong>P:</strong> {{ $vitalSign->pulse_rate }} | <strong>SpO2:</strong> {{ $vitalSign->spo2 }}%</span>
          </div>
          @else <span style="color:#94a3b8;font-size:12px">No vitals</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $waitMinutes > 60 ? '#fee2e2' : ($waitMinutes > 30 ? '#fef3c7' : '#f1f5f9') }};color:{{ $waitMinutes > 60 ? '#991b1b' : ($waitMinutes > 30 ? '#92400e' : '#475569') }}">{{ $encounter->created_at->diffForHumans(null, true) }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle;text-align:center">
          <a href="{{ route('doctor.consultation.start', $encounter) }}" style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;background:#016634;color:#fff;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#01552b'" onmouseout="this.style.background='#016634'">
            <span class="material-symbols-outlined" style="font-size:14px">play_arrow</span> Start
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="8" style="text-align:center;padding:48px 20px">
          <span class="material-symbols-outlined" style="font-size:52px;color:#059669;opacity:.5">check_circle</span>
          <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No Patients Awaiting Consultation</h5>
          <p style="color:#94a3b8;font-size:13px">All triaged patients have been attended to.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
