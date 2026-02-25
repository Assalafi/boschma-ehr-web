<div style="overflow-x:auto">
  <table style="width:100%;border-collapse:separate;border-spacing:0">
    <thead>
      <tr>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Patient</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Program</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Doctor</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Complaint</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Started</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Duration</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;text-align:center">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($encounters as $encounter)
      @php
        $consultation = $encounter->consultations->first();
        $doctor = $consultation?->doctor;
        $startTime = $consultation?->created_at ?? $encounter->updated_at;
      @endphp
      <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f8fafb'" onmouseout="this.style.background=''">
        <td style="padding:12px 14px;vertical-align:middle">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:#dbeafe">
              @if($encounter->patient->enrollee_photo ?? false)
                <img src="{{ $encounter->patient->enrollee_photo }}" style="width:100%;height:100%;object-fit:cover" alt="">
              @else
                <span class="material-symbols-outlined" style="font-size:18px;color:#2563eb">person</span>
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
          @if($doctor)
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:#e6f5ed;display:flex;align-items:center;justify-content:center">
              <span class="material-symbols-outlined" style="font-size:15px;color:#016634">person</span>
            </div>
            <div>
              <div style="font-weight:500;font-size:12px;color:#1e293b">{{ $doctor->name }}</div>
              <div style="font-size:10px;color:#94a3b8">{{ $doctor->title ?? 'Doctor' }}</div>
            </div>
          </div>
          @else <span style="color:#94a3b8;font-size:12px">Not assigned</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle;font-size:12px;color:#475569;max-width:160px">{{ Str::limit($encounter->reason_for_visit, 50) }}</td>
        <td style="padding:12px 14px;vertical-align:middle;font-size:12px;color:#64748b">{{ $consultation?->created_at->format('H:i') ?? $encounter->updated_at->format('H:i') }}</td>
        <td style="padding:12px 14px;vertical-align:middle">
          <span style="display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $startTime->diffForHumans(null, true) }}</span>
        </td>
        <td style="padding:12px 14px;vertical-align:middle;text-align:center">
          @if($consultation && $consultation->status !== \App\Models\ClinicalConsultation::STATUS_COMPLETED)
          <a href="{{ route('doctor.consultation.start', $encounter) }}" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;background:#016634;color:#fff;text-decoration:none">
            <span class="material-symbols-outlined" style="font-size:14px">play_arrow</span> Continue
          </a>
          @else
          <a href="{{ route('doctor.consultation.show', $consultation) }}" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;background:#059669;color:#fff;text-decoration:none">
            <span class="material-symbols-outlined" style="font-size:14px">visibility</span> View
          </a>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;padding:48px 20px">
          <span class="material-symbols-outlined" style="font-size:52px;color:#2563eb;opacity:.4">medical_services</span>
          <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No Active Consultations</h5>
          <p style="color:#94a3b8;font-size:13px">No patients are currently in consultation.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
