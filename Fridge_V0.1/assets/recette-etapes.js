// ==========================================
// Gestion des ingrédients
// ==========================================

document.addEventListener('DOMContentLoaded', function () {

    const SEARCH_URL      = '/ingredient/search';
    const container       = document.getElementById('contenirs-container');
    const btnAjouter      = document.getElementById('btn-ajouter-contenir');
    const prototypeEl     = document.getElementById('contenirs-prototype');

    if (!container || !btnAjouter || !prototypeEl) return;

    let index = container.querySelectorAll('.contenir-row').length;


    function initTomSelect(row) {
        const tsInput  = row.querySelector('.ingredient-ts-input');
        const hiddenId = row.querySelector('[data-ingredient-hidden]');

        if (!tsInput || !hiddenId) return;

        new TomSelect(tsInput, {
            valueField : 'id',
            labelField : 'libelle',
            searchField: 'libelle',
            preload    : false,
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


    function creerLigne(html) {
        const wrapper = document.createElement('div');
        wrapper.className = 'contenir-row d-flex gap-2 align-items-center mb-2 p-2 rounded bg-body-secondary';


        const temp = document.createElement('div');
        temp.innerHTML = html;


        const hidden = temp.querySelector('input[type="hidden"]');
        if (hidden) {
            hidden.setAttribute('data-ingredient-hidden', '1');
            hidden.removeAttribute('class'); 
        }

        wrapper.innerHTML = `
            ${temp.innerHTML}
            <div class="flex-grow-1" style="min-width: 160px;">
                <input type="text"
                       class="form-control ingredient-ts-input"
                       placeholder="Rechercher un ingrédient…">
            </div>
            <button type="button"
                    class="btn btn-sm btn-outline-danger btn-supprimer-contenir flex-shrink-0"
                    title="Supprimer">
                <i class="bi bi-trash"></i>
            </button>
        `;


        wrapper.querySelectorAll('input[type="number"], select').forEach(el => {
            el.classList.add('form-control');
        });
        const select = wrapper.querySelector('select');
        if (select) {
            select.classList.replace('form-control', 'form-select');
            select.style.width = '120px';
            select.style.flexShrink = '0';
        }
        const numberInput = wrapper.querySelector('input[type="number"]');
        if (numberInput) {
            numberInput.style.width = '90px';
            numberInput.style.flexShrink = '0';
            numberInput.setAttribute('placeholder', 'Qté');
        }

        return wrapper;
    }

    // ─── AJOUTER une ligne ────────────────────────────────────
    btnAjouter.addEventListener('click', function () {
        const html    = prototypeEl.dataset.prototype.replace(/__name__/g, index);
        index++;

        const wrapper = creerLigne(html);
        container.appendChild(wrapper);
        initTomSelect(wrapper);

      
        setTimeout(() => wrapper.querySelector('.ts-input input')?.focus(), 50);
    });

    // ─── SUPPRIMER une ligne ──────────────────────────────────
    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-supprimer-contenir');
        if (btn) btn.closest('.contenir-row').remove();
    });



    container.querySelectorAll('.contenir-row').forEach(row => {
        const hidden = row.querySelector('.ingredient-id-hidden');
        if (hidden) hidden.setAttribute('data-ingredient-hidden', '1');
        initTomSelect(row);
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