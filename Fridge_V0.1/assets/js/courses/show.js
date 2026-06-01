function attachCheckListener(checkbox) {
    checkbox.addEventListener('change', async function () {
        const id = this.dataset.id;
        const isCoche = this.checked;
        const row = this.closest('.list-group-item');
        const label = row.querySelector('.form-check-label');

        try {
            const response = await fetch(`/liste-courses/check/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (response.ok) {
                if (isCoche) {
                    label.classList.add('text-decoration-line-through', 'opacity-50');
                    label.classList.remove('fw-bold');
                } else {
                    label.classList.remove('text-decoration-line-through', 'opacity-50');
                    label.classList.add('fw-bold');
                }
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour:', error);
            this.checked = !isCoche;
        }
    });
}

function initCheckIngredients() {
    document.querySelectorAll('.check-ingredient').forEach(attachCheckListener);
}

function appendArticleManuel(data) {
    let group = document.querySelector('[data-categorie="Autre"] .list-group');
    if (!group) {
        const container = document.querySelector('.bento-grid .col-lg-12');
        const section = document.createElement('div');
        section.className = 'mb-5';
        section.setAttribute('data-categorie', 'Autre');
        section.innerHTML = `
            <h3 class="h4 fw-bold mb-4" style="font-family: var(--font-display); color: var(--text-secondary);">
                <i class="bi bi-tag text-primary me-2"></i>Autre
            </h3>
            <div class="list-group list-group-flush rounded-4 overflow-hidden border"></div>`;
        const emptyState = container.querySelector('.text-center.py-5');
        if (emptyState) emptyState.remove();
        container.appendChild(section);
        group = section.querySelector('.list-group');
    }

    const quantiteStr = data.quantite !== null ? `${data.quantite} ${data.unite ?? ''}`.trim() : '';
    const id = data.id;

    const item = document.createElement('div');
    item.className = 'list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-transparent';
    item.innerHTML = `
        <div class="d-flex align-items-center flex-grow-1">
            <div class="form-check me-3">
                <input class="form-check-input ms-0 check-ingredient" type="checkbox"
                       data-id="${id}" id="check-${id}"
                       style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
            </div>
            <label class="form-check-label mb-0 fw-bold"
                   for="check-${id}" style="cursor: pointer; font-size: 1.1rem;">
                ${data.libelle}
            </label>
        </div>
        <div class="text-end">
            <span class="badge rounded-pill px-3 py-2 shadow-sm" style="background-color: var(--primary); color: var(--text); font-size: 0.9rem;">
                ${quantiteStr}
            </span>
        </div>`;

    group.appendChild(item);
    attachCheckListener(item.querySelector('.check-ingredient'));
}

function initFormAjoutManuel() {
    const form = document.getElementById('form-ajout-manuel');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const libelle  = document.getElementById('manuel-libelle').value.trim();
        const quantite = document.getElementById('manuel-quantite').value.trim();
        const unite    = document.getElementById('manuel-unite').value;
        const erreur   = document.getElementById('ajout-erreur');

        erreur.classList.add('d-none');
        erreur.textContent = '';

        if (!libelle) {
            erreur.textContent = 'Veuillez saisir un libellé.';
            erreur.classList.remove('d-none');
            return;
        }

        const body = new URLSearchParams({ libelle });
        if (quantite) body.append('quantite', quantite);
        if (unite)    body.append('unite', unite);

        try {
            const response = await fetch(window.URL_AJOUTER_MANUEL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });

            const data = await response.json();

            if (data.success) {
                appendArticleManuel(data);
                form.reset();
            } else {
                erreur.textContent = data.error ?? 'Une erreur est survenue.';
                erreur.classList.remove('d-none');
            }
        } catch (err) {
            console.error(err);
            erreur.textContent = 'Erreur réseau.';
            erreur.classList.remove('d-none');
        }
    });
}

document.addEventListener('turbo:load', () => {
    initCheckIngredients();
    initFormAjoutManuel();
});
