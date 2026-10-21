<div style="overflow-x:auto">
  <table style="width:100%;border-collapse:separate;border-spacing:0">
    <thead>
      <tr>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Patient</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Program</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Rejection Reason</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Referred Facility</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Doctor</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0">Rejected Date</th>
        <th style="background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0;text-align:center">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($encounters as $encounter)
      @php
        $consultation = $encounter->consultations->first();
        $doctor = $consultation?->doctor;
        $referral = \DB::table('service_referrals')
            ->where('encounter_id', $encounter->id)
            ->where('referral_type', 'patient')
            ->whereNull('service_item_id')
            ->orderByDesc('created_at')
            ->first();
        $toFacility = $referral ? \App\Models\Facility::find($referral->to_facility_id) : null;
        $rejectedByUser = ($referral && $referral->rejected_by) ? \App\Models\User::find($referral->rejected_by) : null;
      @endphp
      <tr style="border-bottom:1px solid #fee2e2;background:#fff8f8" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff8f8'">
        <td style="padding:12px 14px;vertical-align:middle">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;background:#fee2e2">
              @if($encounter->patient->enrollee_photo ?? false)
                <img src="{{ $encounter->patient->enrollee_photo }}" style="width:100%;height:100%;object-fit:cover" alt="">
              @else
                <span class="material-symbols-outlined" style="font-size:18px;color:#dc2626">person</span>
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
        <td style="padding:12px 14px;vertical-align:middle;max-width:260px">
          <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:8px 10px;font-size:12px;color:#991b1b">
            <div style="font-weight:700;display:flex;align-items:center;gap:4px;margin-bottom:3px">
              <span class="material-symbols-outlined" style="font-size:14px">cancel</span>
              <span>Rejection Reason:</span>
            </div>
            <div style="color:#1e293b;font-weight:500;word-break:break-word;line-height:1.35">
              {{ $referral->rejection_reason ?? 'No specific rejection reason provided.' }}
            </div>
            @if($referral && ($referral->rejected_at || $rejectedByUser))
              <div style="font-size:10px;color:#64748b;margin-top:4px;border-top:1px dashed #fca5a5;padding-top:3px">
                @if($referral->rejected_at)
                  {{ \Carbon\Carbon::parse($referral->rejected_at)->format('d M Y H:i') }}
                @endif
                @if($rejectedByUser)
                  • by {{ $rejectedByUser->name }}
                @endif
              </div>
            @endif
          </div>
          @if($referral && $referral->reason)
            <div style="font-size:11px;color:#64748b;margin-top:4px">
              <span style="font-weight:600">Referred for:</span> {{ Str::limit($referral->reason, 45) }}
            </div>
          @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          @if($toFacility)
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:#fee2e2;display:flex;align-items:center;justify-content:center">
              <span class="material-symbols-outlined" style="font-size:15px;color:#dc2626">local_hospital</span>
            </div>
            <div>
              <div style="font-weight:500;font-size:12px;color:#1e293b">{{ $toFacility->name }}</div>
              <span style="display:inline-flex;padding:1px 6px;border-radius:10px;font-size:10px;font-weight:700;background:#fee2e2;color:#991b1b">Rejected</span>
            </div>
          </div>
          @else <span style="color:#94a3b8;font-size:12px">N/A</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle">
          @if($doctor)
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center">
              <span class="material-symbols-outlined" style="font-size:15px;color:#059669">person</span>
            </div>
            <div>
              <div style="font-weight:500;font-size:12px;color:#1e293b">{{ $doctor->name }}</div>
            </div>
          </div>
          @else <span style="color:#94a3b8;font-size:12px">Not assigned</span> @endif
        </td>
        <td style="padding:12px 14px;vertical-align:middle;font-size:12px;color:#64748b">
          {{ ($referral && $referral->rejected_at) ? \Carbon\Carbon::parse($referral->rejected_at)->format('d M Y H:i') : $encounter->updated_at->format('d M Y H:i') }}
        </td>
        <td style="padding:12px 14px;vertical-align:middle;text-align:center">
          <div style="display:flex;flex-direction:column;gap:6px;align-items:center">
            <form method="POST" action="{{ route('doctor.queue.resume-rejected', $encounter) }}" style="margin:0;width:100%">
              @csrf
              <button type="submit" style="display:inline-flex;align-items:center;justify-content:center;gap:4px;width:100%;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:#016634;color:#fff;border:none;cursor:pointer" title="Resume consultation and edit clinical details">
                <span class="material-symbols-outlined" style="font-size:15px">edit_note</span> Continue / Edit
              </button>
            </form>
            @if($consultation)
            <a href="{{ route('doctor.consultation.show', $consultation) }}" style="display:inline-flex;align-items:center;justify-content:center;gap:4px;width:100%;padding:5px 10px;border-radius:8px;font-size:11px;font-weight:600;background:#f8fafc;color:#475569;text-decoration:none;border:1px solid #cbd5e1" title="View consultation summary">
              <span class="material-symbols-outlined" style="font-size:14px">visibility</span> View Summary
            </a>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;padding:48px 20px">
          <span class="material-symbols-outlined" style="font-size:52px;color:#dc2626;opacity:.4">cancel</span>
          <h5 style="font-weight:700;color:#1e293b;margin-top:10px;font-size:15px">No Rejected Patients</h5>
          <p style="color:#94a3b8;font-size:13px">There are currently no rejected referral patients for this facility.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
