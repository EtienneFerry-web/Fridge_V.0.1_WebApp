let strCurrentJour   = null;
let strCurrentMoment = null;
let objCurrentBtn    = null;

function ouvrirPopup(strJour, strMoment, strLabel, objBtn) {
    strCurrentJour   = strJour;
    strCurrentMoment = strMoment;
    objCurrentBtn    = objBtn;
    document.getElementById('modalLabel').textContent = strLabel + ' — ' + strJour;
    switchTab('likes');
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

function switchTab(strTab) {
    document.getElementById('tabLikes').classList.toggle('d-none', strTab !== 'likes');
    document.getElementById('tabFavoris').classList.toggle('d-none', strTab !== 'favoris');
    document.querySelectorAll('#planningTabs .nav-link').forEach((btn, i) => {
        btn.classList.toggle('active', (i === 0 && strTab === 'likes') || (i === 1 && strTab === 'favoris'));
    });
}

async function ajouterAuPlanning(intRecetteId, strTitre, objCard) {
    const objResponse = await fetch(window.FRIDGE_URLS.planningAdd, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
            jour:       strCurrentJour,
            moment:     strCurrentMoment,
            recette_id: intRecetteId
        })
    });

    const objData = await objResponse.json();
    if (!objData.success) return;

    bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();

    const objTd = objCurrentBtn.closest('td');
    const strPhotoUrl = objData.photo
        ? (objData.photo.startsWith('http://') || objData.photo.startsWith('https://') || objData.photo.startsWith('//')
            ? objData.photo
            : '/uploads/recettes/' + objData.photo)
        : null;
    const strPhoto = strPhotoUrl
        ? `<img src="${strPhotoUrl}" class="rounded-circle" style="width:42px;height:42px;object-fit:cover;" alt="${objData.titre}">`
        : '';

    objTd.innerHTML = `
        <div class="d-flex flex-column align-items-center gap-1 h-100 justify-content-center">
            ${strPhoto}
            <span class="small fw-bold text-dark" style="font-size:0.72rem;line-height:1.2;">
                ${objData.titre.substring(0, 20)}${objData.titre.length > 20 ? '…' : ''}
            </span>
            <button class="btn btn-sm p-0 text-danger" style="font-size:0.75rem;"
                    onclick="supprimerPlanning(${objData.id}, this)" title="Retirer">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>`;
}

async function supprimerPlanning(intId, objBtn) {
    const strUrl = window.FRIDGE_URLS.planningDelete.replace('__ID__', intId);
    const objResponse = await fetch(strUrl, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const objData = await objResponse.json();
    if (!objData.success) return;

    const objTd = objBtn.closest('td');
    objTd.innerHTML = `
        <button class="btn-add w-100 h-100 border-0 bg-transparent"
                onclick="ouvrirPopup('${objTd.dataset.jour}', '${objTd.dataset.moment}', '${objTd.dataset.label}', this)"
                title="Ajouter une recette">
            <i class="bi bi-plus-lg text-muted"></i>
        </button>`;
}

// ========================================
// FILTRAGE DES RECETTES DANS LA MODAL
// ========================================

function appliquerFiltresPlanning() {
    const strSearch   = document.getElementById('planningFilterSearch').value.toLowerCase().trim();
    const strRegime   = document.getElementById('planningFilterRegime').value;
    const intTempsMax = parseInt(document.getElementById('planningFilterTemps').value, 10);

    const arrCartes = document.querySelectorAll('.planning-recette-item');
    let intVisibles = 0;

    arrCartes.forEach(objItem => {
        const objCard = objItem.querySelector('.planning-recette-card');
        if (!objCard) return;

        const strLibelle = objCard.dataset.recetteLibelle || '';
        const intTemps   = parseInt(objCard.dataset.recetteTemps || '0', 10);
        const strRegimes = objCard.dataset.recetteRegimes || '';

        let bMatch = true;

        if (strSearch !== '' && !strLibelle.includes(strSearch)) bMatch = false;
        if (strRegime !== 'all' && !strRegimes.includes(strRegime + '|')) bMatch = false;
        if (intTempsMax > 0 && intTemps > intTempsMax) bMatch = false;

        objItem.style.display = bMatch ? '' : 'none';
        if (bMatch) intVisibles++;
    });

    const objCounter = document.getElementById('planningFilterCounter');
    if (objCounter) {
        objCounter.textContent = intVisibles + ' recette' + (intVisibles > 1 ? 's' : '') + ' trouvée' + (intVisibles > 1 ? 's' : '');
    }
}

function reinitFiltresPlanning() {
    const objSearch = document.getElementById('planningFilterSearch');
    const objRegime = document.getElementById('planningFilterRegime');
    const objTemps  = document.getElementById('planningFilterTemps');
    if (objSearch) objSearch.value = '';
    if (objRegime) objRegime.value = 'all';
    if (objTemps)  objTemps.value  = '0';
    appliquerFiltresPlanning();
}

document.addEventListener('DOMContentLoaded', () => {
    const objSearch = document.getElementById('planningFilterSearch');
    const objRegime = document.getElementById('planningFilterRegime');
    const objTemps  = document.getElementById('planningFilterTemps');

    if (objSearch) objSearch.addEventListener('input', appliquerFiltresPlanning);
    if (objRegime) objRegime.addEventListener('change', appliquerFiltresPlanning);
    if (objTemps)  objTemps.addEventListener('change', appliquerFiltresPlanning);
});

const objAddModal = document.getElementById('addModal');
if (objAddModal) {
    objAddModal.addEventListener('shown.bs.modal', reinitFiltresPlanning);
}
