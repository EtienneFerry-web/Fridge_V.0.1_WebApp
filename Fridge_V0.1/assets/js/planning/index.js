let strCurrentJour   = null;
let strCurrentMoment = null;
let objCurrentBtn    = null;
let objDraggedItem   = null;
let objDragSourceCell = null;

// ========================================
// MODAL — OUVERTURE / TABS
// ========================================

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

// ========================================
// AJOUT / SUPPRESSION DE RECETTES
// ========================================

async function ajouterAuPlanning(intRecetteId, strTitre, objCard) {
    const objResponse = await fetch(window.FRIDGE_URLS.planningAdd, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
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

    // Créer l'élément DOM proprement pour pouvoir y attacher les événements drag
    const objNouvelItem = document.createElement('div');
    objNouvelItem.className = 'd-flex flex-column align-items-center gap-1 h-100 justify-content-center planning-drag-item';
    objNouvelItem.dataset.planningId = objData.id;
    objNouvelItem.innerHTML = `
        ${strPhoto}
        <span class="small fw-bold text-dark" style="font-size:0.72rem;line-height:1.2;">
            ${objData.titre.substring(0, 20)}${objData.titre.length > 20 ? '…' : ''}
        </span>
        <button class="btn btn-sm p-0 text-danger" style="font-size:0.75rem;"
                onclick="supprimerPlanning(${objData.id}, this)" title="Retirer">
            <i class="bi bi-x-circle"></i>
        </button>`;

    objTd.innerHTML = '';
    objTd.appendChild(objNouvelItem);

    // Activer immédiatement le drag sur le nouvel élément
    activerDrag(objNouvelItem);
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

// ========================================
// DRAG & DROP — API HTML5 Native
// ========================================

/**
 * Initialise le drag & drop sur tous les éléments et cellules existants au chargement.
 * Les éléments ajoutés dynamiquement (ajouterAuPlanning) appellent activerDrag() directement.
 */
function initDragAndDrop() {
    // Activer le drag sur les éléments déjà présents dans le DOM (chargement initial)
    document.querySelectorAll('.planning-drag-item').forEach(objItem => {
        activerDrag(objItem);
    });

    // Activer le drop sur toutes les cellules
    document.querySelectorAll('.meal-cell').forEach(objTd => {
        activerDrop(objTd);
    });
}

/**
 * Active les événements dragstart / dragend sur un élément .planning-drag-item.
 * Appelé au chargement et après ajout dynamique d'une recette.
 */
function activerDrag(objItem) {
    objItem.setAttribute('draggable', 'true');

    objItem.addEventListener('dragstart', (evt) => {
        objDraggedItem    = objItem;
        objDragSourceCell = objItem.closest('td');
        evt.dataTransfer.effectAllowed = 'move';
        // Petit délai pour que le navigateur capture le ghost avant d'appliquer le style
        setTimeout(() => objItem.classList.add('drag-chosen'), 0);
    });

    objItem.addEventListener('dragend', () => {
        objItem.classList.remove('drag-chosen');
        // Nettoyer le highlight de toutes les cellules
        document.querySelectorAll('.meal-cell').forEach(td => {
            td.classList.remove('sortable-over');
        });
        objDraggedItem    = null;
        objDragSourceCell = null;
    });
}

/**
 * Active les événements dragover / dragleave / drop sur une cellule <td>.
 * Appelé au chargement pour toutes les cellules.
 */
function activerDrop(objTd) {
    objTd.addEventListener('dragenter', (evt) => {
        evt.preventDefault();
        if (objDraggedItem && objTd !== objDragSourceCell) {
            objTd.classList.add('sortable-over');
        }
    });

    objTd.addEventListener('dragover', (evt) => {
        evt.preventDefault();
        evt.dataTransfer.dropEffect = 'move';
    });

    objTd.addEventListener('dragleave', (evt) => {
        if (!objTd.contains(evt.relatedTarget)) {
            objTd.classList.remove('sortable-over');
        }
    });

    objTd.addEventListener('drop', async (evt) => {
        evt.preventDefault();
        objTd.classList.remove('sortable-over');
        
        if (!objDraggedItem || objTd === objDragSourceCell) return;
        
        const intPlanningId    = parseInt(objDraggedItem.dataset.planningId, 10);
        const strNouveauJour   = objTd.dataset.jour;
        const strNouveauMoment = objTd.dataset.moment;
        
        const strContenuCible = objTd.innerHTML;
        const objSource       = objDragSourceCell;
        
        // --- Mise à jour optimiste ---
        objTd.innerHTML = '';
        objTd.appendChild(objDraggedItem);
        nettoyerCellulesVides(objSource);
        
        // --- Appel AJAX ---
        try {
            const objResponse = await fetch(window.FRIDGE_URLS.planningMove, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    planning_id:    intPlanningId,
                    nouveau_jour:   strNouveauJour,
                    nouveau_moment: strNouveauMoment,
                })
            });
            const objData = await objResponse.json();
            if (!objData.success) {
                console.error('Erreur déplacement :', objData.error);
                annulerDeplacement(objDraggedItem, objSource, objTd, strContenuCible);
            }
        } catch (e) {
            console.error('Erreur réseau :', e);
            annulerDeplacement(objDraggedItem, objSource, objTd, strContenuCible);
        }
    });
}

/**
 * Si la cellule est vide après un drag, remplace son contenu par le bouton "+".
 */
function nettoyerCellulesVides(objTd) {
    if (!objTd) return;
    if (!objTd.querySelector('.planning-drag-item')) {
        const strJour   = objTd.dataset.jour;
        const strMoment = objTd.dataset.moment;
        const strLabel  = objTd.dataset.label;
        objTd.innerHTML = `
            <button class="btn-add w-100 h-100 border-0 bg-transparent"
                    onclick="ouvrirPopup('${strJour}', '${strMoment}', '${strLabel}', this)"
                    title="Ajouter une recette">
                <i class="bi bi-plus-lg text-muted"></i>
            </button>`;
    }
}

/**
 * Annule un déplacement en cas d'échec AJAX :
 * remet l'élément dans sa cellule source et restaure la cellule cible.
 */
function annulerDeplacement(objItem, objTdSource, objTdCible, strContenuCibleOriginal) {
    // Remettre l'élément dans la source
    objTdSource.innerHTML = '';
    objTdSource.appendChild(objItem);

    // Restaurer la cellule cible
    objTdCible.innerHTML = strContenuCibleOriginal;

    // Réactiver le drop sur la cellule cible restaurée
    activerDrop(objTdCible);

    // Flash rouge pour signaler l'erreur
    objItem.style.outline = '2px solid red';
    setTimeout(() => { objItem.style.outline = ''; }, 1500);
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', initDragAndDrop);