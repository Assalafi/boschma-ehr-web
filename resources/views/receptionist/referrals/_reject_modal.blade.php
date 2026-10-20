<!-- Reject Referral Modal -->
<div class="modal fade" id="rejectReferralModal" tabindex="-1" aria-labelledby="rejectReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form id="rejectReferralForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 bg-danger-subtle text-danger py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="wh-40 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                            <span class="material-symbols-outlined fs-5">cancel</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-danger mb-0" id="rejectReferralModalLabel">Reject Patient Referral</h5>
                            <small class="text-danger-emphasis">Provide a clear reason for rejecting this referral</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light rounded-3 p-3 border mb-3">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Patient Name</span>
                                <span class="fw-semibold text-dark" id="rejectModalPatientName">-</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">BOSCHMA ID</span>
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold" id="rejectModalBoschmaNo">-</span>
                            </div>
                            <div class="col-12 pt-1">
                                <span class="text-muted small d-block">Referring Facility</span>
                                <span class="fw-medium text-dark" id="rejectModalFacility">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="rejectModalReasonInput" class="form-label fw-bold text-dark">
                            Reason for Rejection <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                            id="rejectModalReasonInput" 
                            name="rejection_reason" 
                            rows="3" 
                            required 
                            placeholder="Please explain why this patient referral is being rejected..."></textarea>
                    </div>

                    <!-- Quick reason pills -->
                    <div class="mb-2">
                        <small class="text-muted d-block mb-1 fw-semibold">Quick Reasons (Click to apply):</small>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-reason-btn" style="font-size:11px">No bed capacity available</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-reason-btn" style="font-size:11px">Required specialist unavailable</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-reason-btn" style="font-size:11px">Patient declined admission/service</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-reason-btn" style="font-size:11px">Requires tertiary facility care</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-reason-btn" style="font-size:11px">Duplicate referral</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-reason-btn" style="font-size:11px">Incomplete clinical documentation</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3 px-4">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnConfirmRejectReferral" class="btn btn-danger px-4 d-inline-flex align-items-center">
                        <span class="material-symbols-outlined me-1 fs-6 align-middle">cancel</span> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rejectModalEl = document.getElementById('rejectReferralModal');
    if (rejectModalEl) {
        rejectModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const action = button.getAttribute('data-action') || '';
            const name = button.getAttribute('data-name') || 'N/A';
            const boschma = button.getAttribute('data-boschma') || 'N/A';
            const facility = button.getAttribute('data-facility') || 'Referring Facility';

            const form = document.getElementById('rejectReferralForm');
            if (form) form.action = action;

            const nameEl = document.getElementById('rejectModalPatientName');
            if (nameEl) nameEl.textContent = name;

            const boschmaEl = document.getElementById('rejectModalBoschmaNo');
            if (boschmaEl) boschmaEl.textContent = boschma;

            const facilityEl = document.getElementById('rejectModalFacility');
            if (facilityEl) facilityEl.textContent = facility;

            const reasonInput = document.getElementById('rejectModalReasonInput');
            if (reasonInput) {
                reasonInput.value = '';
                setTimeout(() => reasonInput.focus(), 300);
            }
        });

        // Quick reason buttons
        document.querySelectorAll('.quick-reason-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const reasonInput = document.getElementById('rejectModalReasonInput');
                if (reasonInput) {
                    reasonInput.value = this.textContent.trim();
                    reasonInput.focus();
                }
            });
        });
    }
});
</script>
@endpush
