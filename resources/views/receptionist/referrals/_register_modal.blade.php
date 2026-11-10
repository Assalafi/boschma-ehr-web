<!-- Register Referral Confirmation Modal -->
<div class="modal fade" id="registerReferralModal" tabindex="-1" aria-labelledby="registerReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form id="registerReferralForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 bg-light py-3 px-4">
                    <div class="d-flex align-items-center">
                        <div class="wh-40 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                            <span class="material-symbols-outlined fs-5">person_add</span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="registerReferralModalLabel">Register Referred Patient</h5>
                            <small class="text-muted">Create a new encounter from approved referral</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-primary d-flex align-items-start border-0 bg-primary-subtle text-primary-emphasis mb-3 py-2 px-3 rounded-3">
                        <span class="material-symbols-outlined fs-5 me-2 mt-1 flex-shrink-0">help_outline</span>
                        <div>
                            <span class="fw-bold">Register this referred patient and create an encounter?</span>
                            <div class="small mt-1 text-secondary">A new encounter will be created and this referral will be accepted for triage.</div>
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-3 border">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Patient Name</span>
                                <span class="fw-semibold text-dark" id="modalPatientName">-</span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">BOSCHMA ID</span>
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold" id="modalBoschmaNo">-</span>
                            </div>
                            <div class="col-12 pt-1">
                                <span class="text-muted small d-block">Referred From</span>
                                <span class="fw-medium text-dark" id="modalFromFacility">-</span>
                            </div>
                            <div class="col-12 pt-1" id="modalReasonWrapper">
                                <span class="text-muted small d-block">Referral Reason</span>
                                <div class="small text-secondary bg-white rounded p-2 border mt-1" style="white-space: pre-wrap;" id="modalReferralReason">-</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3 px-4">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnConfirmRegisterReferral" class="btn btn-primary px-4 d-inline-flex align-items-center">
                        <span class="material-symbols-outlined me-1 fs-6 align-middle">how_to_reg</span> Yes, Register Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const registerModalEl = document.getElementById('registerReferralModal');
    if (registerModalEl) {
        registerModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const action = button.getAttribute('data-action') || '';
            const name = button.getAttribute('data-name') || 'N/A';
            const boschma = button.getAttribute('data-boschma') || 'N/A';
            const facility = button.getAttribute('data-facility') || 'N/A';
            const reason = button.getAttribute('data-reason') || 'None provided';

            const form = document.getElementById('registerReferralForm');
            if (form && action) {
                form.setAttribute('action', action);
            }

            const nameEl = document.getElementById('modalPatientName');
            if (nameEl) nameEl.textContent = name;

            const boschmaEl = document.getElementById('modalBoschmaNo');
            if (boschmaEl) boschmaEl.textContent = boschma;

            const facilityEl = document.getElementById('modalFromFacility');
            if (facilityEl) facilityEl.textContent = facility;

            const reasonEl = document.getElementById('modalReferralReason');
            if (reasonEl) reasonEl.textContent = reason;

            const submitBtn = document.getElementById('btnConfirmRegisterReferral');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="material-symbols-outlined me-1 fs-6 align-middle">how_to_reg</span> Yes, Register Patient';
            }
        });

        const form = document.getElementById('registerReferralForm');
        if (form) {
            form.addEventListener('submit', function () {
                const submitBtn = document.getElementById('btnConfirmRegisterReferral');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Registering...';
                }
            });
        }
    }
});
</script>
@endpush
