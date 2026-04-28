function initConfirmModals() {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        if (form.dataset.confirmBound === 'true') return;
        form.dataset.confirmBound = 'true';
        form.addEventListener('submit', function (e) {
            if (form.dataset.confirmed === 'true') {
                form.dataset.confirmed = 'false';
                return;
            }
            e.preventDefault();
            document.getElementById('confirmModalTitle').textContent = form.dataset.confirmTitle || 'Confirmation';
            document.getElementById('confirmModalBody').textContent = form.dataset.confirm;
            const modalEl = document.getElementById('confirmModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const btn = document.getElementById('confirmModalBtn');
            const fresh = btn.cloneNode(true);
            btn.parentNode.replaceChild(fresh, btn);
            fresh.addEventListener('click', function () {
                modal.hide();
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });
            modal.show();
        });
    });
}
document.addEventListener('turbo:load', initConfirmModals);
