(function () {
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (tab) {
        const trigger = document.querySelector('[data-bs-target="#' + tab + '"]');
        if (trigger) {
            bootstrap.Tab.getOrCreateInstance(trigger).show();
        }
    }
})();

document.getElementById('modalBan').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const userId = btn.dataset.id;
    document.getElementById('modalPseudoDisplay').textContent = btn.dataset.pseudo;
    document.getElementById('banForm').action = '/dashboard/user/' + userId + '/ban';
    document.getElementById('banToken').value = btn.dataset.token;
});
