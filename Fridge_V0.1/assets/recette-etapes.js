// ==========================================
// Gestion des ingrédients
// ==========================================
document.addEventListener('DOMContentLoaded', function () {

    const SEARCH_URL  = '/ingredient/search';
    const container   = document.getElementById('contenirs-container');
    const btnAjouter  = document.getElementById('btn-ajouter-contenir');
    const prototypeEl = document.getElementById('contenirs-prototype');

    if (!container || !btnAjouter || !prototypeEl) return;

    let index = container.querySelectorAll('.contenir-row').length;

    // ─── TOM SELECT ───────────────────────────────────────────
    function initTomSelect(row) {
        const tsInput  = row.querySelector('.ingredient-ts-input');
        const hiddenId = row.querySelector('[data-ingredient-hidden]');
        if (!tsInput || !hiddenId || tsInput._tomSelect) return;

        new TomSelect(tsInput, {
            valueField : 'id',
            labelField : 'libelle',
            searchField: 'libelle',
            placeholder: 'Rechercher un ingrédient…',
            load(query, callback) {
                if (query.length < 2) return callback();
                fetch(`${SEARCH_URL}?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(callback)
                    .catch(() => callback());
            },
            onChange(value) {
                hiddenId.value = value;
            },
            create: false,
        });
    }

    // ─── CONFIRMER un ingrédient ──────────────────────────────
    function confirmerLigne(row) {
        const tsInput   = row.querySelector('.ingredient-ts-input');
        const hiddenId  = row.querySelector('[data-ingredient-hidden]');
        const qteInput  = row.querySelector('.input-quantite');
        const uniteInput= row.querySelector('.input-unite');

        const ts = tsInput?._tomSelect || tsInput?.tomselect;

        // Validation minimale
        if (!hiddenId?.value || !qteInput?.value) {
            qteInput?.classList.add('is-invalid');
            return;
        }
        qteInput.classList.remove('is-invalid');

        const libelle = ts ? ts.getItem(hiddenId.value)?.textContent?.trim()
                           : tsInput?.value;
        const qte     = qteInput.value;
        const unite   = uniteInput?.value || '';

        // Écrit dans les champs Symfony hidden
        const sfQte   = row.querySelector('.contenir-quantite-hidden');
        const sfUnite = row.querySelector('.contenir-unite-hidden');
        if (sfQte)   sfQte.value   = qte;
        if (sfUnite) sfUnite.value = unite;

        // Met à jour le label affiché
        const label = row.querySelector('.contenir-label');
        const badge = row.querySelector('.contenir-badge');
        if (label) label.textContent = libelle;
        if (badge) badge.textContent = `${qte} ${unite}`;

        // Bascule en mode confirmé
        row.querySelector('.contenir-form').classList.add('d-none');
        row.querySelector('.contenir-form').classList.remove('d-flex');
        row.querySelector('.contenir-display').classList.remove('d-none');
        row.setAttribute('data-confirmed', 'true');
    }

    // ─── MODIFIER une ligne confirmée ─────────────────────────
    function editerLigne(row) {
        const sfQte   = row.querySelector('.contenir-quantite-hidden');
        const sfUnite = row.querySelector('.contenir-unite-hidden');

        // Pré-remplit les champs du formulaire
        const qteInput   = row.querySelector('.input-quantite');
        const uniteInput = row.querySelector('.input-unite');
        if (qteInput && sfQte)     qteInput.value   = sfQte.value;
        if (uniteInput && sfUnite) uniteInput.value  = sfUnite.value;

        // Bascule en mode formulaire
        row.querySelector('.contenir-display').classList.add('d-none');
        row.querySelector('.contenir-form').classList.remove('d-none');
        row.querySelector('.contenir-form').classList.add('d-flex');
        row.setAttribute('data-confirmed', 'false');

        initTomSelect(row);
        setTimeout(() => row.querySelector('.ts-input input')?.focus(), 50);
    }

    // ─── CRÉER une nouvelle ligne (prototype) ─────────────────
    function creerLigne(html) {
        const wrapper = document.createElement('div');
        wrapper.className = 'contenir-row mb-2';

        // Parse le HTML du prototype pour récupérer les champs Symfony
        const temp = document.createElement('div');
        temp.innerHTML = html;

        const hidden = temp.querySelector('input[type="hidden"]');
        if (hidden) hidden.setAttribute('data-ingredient-hidden', '1');

        // Récupère les noms des champs générés par Symfony
        const sfQteEl   = temp.querySelector('input[type="number"], input[id*="quantite"]');
        const sfUniteEl = temp.querySelector('select');

        const sfQteHtml   = sfQteEl
            ? `<input type="hidden" name="${sfQteEl.name}" class="contenir-quantite-hidden">`
            : '';
        const sfUniteHtml = sfUniteEl
            ? `<select name="${sfUniteEl.name}" class="contenir-unite-hidden d-none">${sfUniteEl.innerHTML}</select>`
            : '';

        wrapper.innerHTML = `
            ${hidden?.outerHTML ?? ''}
            ${sfQteHtml}
            ${sfUniteHtml}

            <!-- Mode confirmé (caché au départ) -->
            <div class="contenir-display d-none align-items-center gap-2 p-2 rounded list-group-item d-flex justify-content-between "
                style="background: white; border: 0.5px solid #e0d5cc;">
                <i class="bi bi-check2-circle me-1" style="color: var(--tomato-jam);"></i>
                <span class="flex-grow-1 fw-medium contenir-label" style="color: #333;"></span>
                <span class="badge rounded-pill contenir-badge"
                    style="background-color: var(--tomato-jam); color: var(--linen); border: 1.5px solid var(--tomato-dark);">
                </span>
                <button type="button" class="btn btn-sm btn-modifier-contenir"
                        style="color: var(--tomato-jam); border-color: var(--tomato-jam);">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-supprimer-contenir"
                        style="color: var(--tomato-jam); border-color: var(--tomato-jam);">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <!-- Mode formulaire (visible au départ) -->
            <div class="contenir-form d-flex gap-2 align-items-center p-2 rounded"
                 style="background-color: #e0d3c6;">
                <div class="flex-grow-1" style="min-width:160px;">
                    <input type="text" class="form-control ingredient-ts-input"
                           placeholder="Rechercher un ingrédient…">
                </div>
                <input type="number" class="form-control input-quantite"
                       placeholder="Qté" style="width:80px; flex-shrink:0;">
                <select class="form-select input-unite" style="width:120px; flex-shrink:0;">
                    <option value="">Unité</option>
                    <option value="g">g</option>
                    <option value="kg">kg</option>
                    <option value="ml">ml</option>
                    <option value="l">l</option>
                    <option value="tsp">tsp</option>
                    <option value="tbsp">tbsp</option>
                    <option value="pièce(s)">pièce(s)</option>
                    <option value="gousse(s)">gousse(s)</option>
                    <option value="bouquet">bouquet</option>
                    <option value="pincée">pincée</option>
                    <option value="tranche(s)">tranche(s)</option>
                </select>
                <button type="button" class="btn btn-sm btn-confirmer-contenir flex-shrink-0"
                        style="color: var(--teal-text); border-color: var(--teal-text);" title="Confirmer">
                    <i class="bi bi-check-lg"></i>
                </button>
                <button type="button" class="btn btn-sm btn-annuler-contenir flex-shrink-0"
                        style="color: var(--tomato-dark); border-color: var(--tomato-dark);" title="Annuler">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;

        return wrapper;
    }

    // ─── AJOUTER ──────────────────────────────────────────────
    btnAjouter.addEventListener('click', function () {
        const html    = prototypeEl.dataset.prototype.replace(/__name__/g, index);
        index++;
        const wrapper = creerLigne(html);
        container.appendChild(wrapper);
        initTomSelect(wrapper);
        setTimeout(() => wrapper.querySelector('.ts-input input')?.focus(), 50);
    });

    // ─── DÉLÉGATION D'ÉVÉNEMENTS ──────────────────────────────
    container.addEventListener('click', function (e) {

        // Confirmer
        const btnConfirmer = e.target.closest('.btn-confirmer-contenir');
        if (btnConfirmer) {
            confirmerLigne(btnConfirmer.closest('.contenir-row'));
            return;
        }

        // Annuler (supprime si jamais confirmé, repasse en display sinon)
        const btnAnnuler = e.target.closest('.btn-annuler-contenir');
        if (btnAnnuler) {
            const row = btnAnnuler.closest('.contenir-row');
            if (row.getAttribute('data-confirmed') === 'true') {
                // Repasse en mode confirmé sans rien changer
                row.querySelector('.contenir-form').classList.add('d-none');
                row.querySelector('.contenir-form').classList.remove('d-flex');
                row.querySelector('.contenir-display').classList.remove('d-none');
            } else {
                row.remove();
            }
            return;
        }

        // Modifier
        const btnModifier = e.target.closest('.btn-modifier-contenir');
        if (btnModifier) {
            editerLigne(btnModifier.closest('.contenir-row'));
            return;
        }

        // Supprimer
        const btnSupprimer = e.target.closest('.btn-supprimer-contenir');
        if (btnSupprimer) {
            btnSupprimer.closest('.contenir-row').remove();
            return;
        }
    });

    // ─── LIGNES EXISTANTES (mode édition de recette) ──────────
    container.querySelectorAll('.contenir-row[data-confirmed="true"]').forEach(row => {
        const hidden = row.querySelector('.ingredient-id-hidden');
        if (hidden) hidden.setAttribute('data-ingredient-hidden', '1');

    });
});


// ==========================================
// Gestion des étapes  
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    const container  = document.getElementById('etapes-container');
    const btnAjouter = document.getElementById('btn-ajouter-etape');

    if (!container || !btnAjouter) return;

    const prototype = document.getElementById('etapes-prototype').dataset.prototype;
    let index       = container.querySelectorAll('.etape-row').length;

    function mettreAJourNumeros() {
        container.querySelectorAll('.etape-numero').forEach((badge, i) => {
            badge.textContent = 'Étape ' + (i + 1);
        });
    }

    function ajouterEtape() {
        const html = prototype.replace(/__name__/g, index);
        const div  = document.createElement('div');
        div.classList.add('etape-row', 'card', 'mb-2', 'border-0', 'shadow-sm');
        div.innerHTML = `
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-dark me-2 etape-numero">Étape ${index + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto btn-supprimer-etape">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                ${html}
            </div>`;

        div.querySelectorAll('input, textarea').forEach(el => el.classList.add('form-control', 'mb-2'));
        container.appendChild(div);
        index++;
        mettreAJourNumeros();
        bindSupprimer(div.querySelector('.btn-supprimer-etape'));
    }

    function bindSupprimer(btn) {
        btn.addEventListener('click', function () {
            btn.closest('.etape-row').remove();
            mettreAJourNumeros();
        });
    }

    container.querySelectorAll('.btn-supprimer-etape').forEach(bindSupprimer);
    mettreAJourNumeros();
    btnAjouter.addEventListener('click', ajouterEtape);
});