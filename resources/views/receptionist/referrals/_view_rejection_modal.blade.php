<!-- View Rejection Details Modal -->
<div class="modal fade" id="viewRejectionModal" tabindex="-1" aria-labelledby="viewRejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-0 bg-danger-subtle text-danger py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="wh-40 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                        <span class="material-symbols-outlined fs-5">cancel</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0" id="viewRejectionModalLabel">Rejection Details</h5>
                        <small class="text-danger-emphasis">Referral rejection information</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Patient Info Card -->
                <div class="bg-light rounded-3 p-3 border mb-3">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Patient Name</span>
                            <span class="fw-semibold text-dark" id="viewRejectPatientName">-</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">BOSCHMA ID</span>
                            <span class="badge bg-secondary-subtle text-secondary fw-semibold" id="viewRejectBoschmaNo">-</span>
                        </div>
                        <div class="col-12 pt-1">
                            <span class="text-muted small d-block">Facility</span>
                            <span class="fw-medium text-dark" id="viewRejectFacility">-</span>
                        </div>
                        <div class="col-12 pt-1">
                            <span class="text-muted small d-block">Original Clinical Reason</span>
                            <div class="small text-secondary bg-white rounded p-2 border mt-1" id="viewRejectOriginalReason">-</div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason Card -->
                <div class="alert alert-danger border-danger-subtle bg-danger-subtle text-danger-emphasis p-3 rounded-3 mb-3">
                    <div class="d-flex align-items-center gap-2 fw-bold text-danger mb-2">
                        <span class="material-symbols-outlined fs-5">error</span>
                        <span class="fs-6">Reason for Rejection</span>
                    </div>
                    <div class="bg-white p-3 rounded-2 border border-danger-subtle text-dark fs-6 fw-medium text-break" style="white-space: pre-wrap;" id="viewRejectReasonText">-</div>
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 pt-2 border-top border-danger-subtle small text-muted">
                        <div>
                            <span class="fw-semibold">Rejected On:</span> <span id="viewRejectDate">-</span>
                        </div>
                        <div id="viewRejectByWrapper">
                            <span class="fw-semibold">Rejected By:</span> <span id="viewRejectBy">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3 px-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewRejectModalEl = document.getElementById('viewRejectionModal');
    if (viewRejectModalEl) {
        viewRejectModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const name = button.getAttribute('data-name') || 'N/A';
            const boschma = button.getAttribute('data-boschma') || 'N/A';
            const facility = button.getAttribute('data-facility') || 'N/A';
            const reason = button.getAttribute('data-reason') || 'N/A';
            const rejectionReason = button.getAttribute('data-rejection-reason') || 'No reason specified';
            const rejectedAt = button.getAttribute('data-rejected-at') || 'N/A';
            const rejectedBy = button.getAttribute('data-rejected-by') || '';

            const nameEl = document.getElementById('viewRejectPatientName');
            if (nameEl) nameEl.textContent = name;

            const boschmaEl = document.getElementById('viewRejectBoschmaNo');
            if (boschmaEl) boschmaEl.textContent = boschma;

            const facilityEl = document.getElementById('viewRejectFacility');
            if (facilityEl) facilityEl.textContent = facility;

            const origReasonEl = document.getElementById('viewRejectOriginalReason');
            if (origReasonEl) origReasonEl.textContent = reason;

            const rejectReasonEl = document.getElementById('viewRejectReasonText');
            if (rejectReasonEl) rejectReasonEl.textContent = rejectionReason;

            const dateEl = document.getElementById('viewRejectDate');
            if (dateEl) dateEl.textContent = rejectedAt || 'N/A';

            const byEl = document.getElementById('viewRejectBy');
            const byWrapper = document.getElementById('viewRejectByWrapper');
            if (byEl && byWrapper) {
                if (rejectedBy) {
                    byEl.textContent = rejectedBy;
                    byWrapper.style.display = 'block';
                } else {
                    byWrapper.style.display = 'none';
                }
            }
        });
    }
});
</script>
@endpush
