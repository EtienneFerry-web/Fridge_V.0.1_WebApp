function initCheckIngredients() {
    document.querySelectorAll('.check-ingredient').forEach(checkbox => {
        checkbox.addEventListener('change', async function () {
            const id = this.dataset.id;
            const isCoche = this.checked;
            const row = this.closest('li');
            const label = row.querySelector('.form-check-label');

            try {
                const response = await fetch(`/liste-courses/check/${id}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (response.ok) {
                    if (isCoche) {
                        label.classList.add('text-decoration-line-through', 'text-muted');
                        label.classList.remove('fw-bold');
                    } else {
                        label.classList.remove('text-decoration-line-through', 'text-muted');
                        label.classList.add('fw-bold');
                    }
                }
            } catch (error) {
                console.error('Erreur lors de la mise à jour:', error);
                this.checked = !isCoche;
            }
        });
    });
}
document.addEventListener('turbo:load', initCheckIngredients);
