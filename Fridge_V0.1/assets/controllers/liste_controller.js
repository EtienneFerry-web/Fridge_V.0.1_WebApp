import { Controller } from '@hotwired/stimulus';

/**
 * Gère le modal "Ajouter à une liste" sur la page de détail recette.
 *
 * data-liste-fetch-url  : URL GET /liste/mes-listes/{recetteId}
 * data-liste-create-url : URL POST /liste/nouvelle
 * data-liste-recette-id : ID de la recette courante
 */
export default class extends Controller {
    static targets = [
        'modal',
        'listesContainer',
        'createForm',
        'nomInput',
        'createError',
        'triggerIcon',
    ];

    connect() {
        // Fermeture du modal sur clic extérieur
        this.element.addEventListener('click', (e) => {
            if (e.target === this.modalTarget) this.closeModal();
        });
    }

    async openModal(event) {
        event.preventDefault();

        this.modalTarget.classList.remove('d-none');
        this.listesContainerTarget.innerHTML = '<p class="text-muted small text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Chargement…</p>';
        this.createFormTarget.classList.add('d-none');
        this.nomInputTarget.value = '';
        this.createErrorTarget.textContent = '';

        const strUrl  = this.element.dataset.listeFetchUrl;
        const objResp = await fetch(strUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const objData = await objResp.json();

        this._renderListes(objData.listes, objData.canCreate);
    }

    closeModal() {
        this.modalTarget.classList.add('d-none');
    }

    _renderListes(arrListes, boolCanCreate) {
        if (arrListes.length === 0) {
            this.listesContainerTarget.innerHTML = '<p class="text-muted small text-center py-2">Aucune liste pour l\'instant.</p>';
        } else {
            this.listesContainerTarget.innerHTML = arrListes.map(objL => `
                <button class="liste-item w-100 text-start btn btn-light d-flex align-items-center justify-content-between gap-2 px-3 py-2 rounded-2 mb-2"
                        data-id="${objL.id}" data-action="liste#toggleItem">
                    <span>
                        <i class="bi bi-collection-fill text-primary me-2" style="font-size:0.85rem;"></i>
                        <span class="fw-semibold small">${objL.nom}</span>
                        <span class="text-muted small ms-2">${objL.count} recette${objL.count > 1 ? 's' : ''}</span>
                    </span>
                    <i class="bi ${objL.contains ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'}"></i>
                </button>
            `).join('');
        }

        const btnCreate = this.listesContainerTarget.querySelector('[data-action="liste#showCreate"]');
        if (!btnCreate) {
            const divNew = document.createElement('div');
            divNew.className = 'mt-2';
            if (boolCanCreate) {
                divNew.innerHTML = `<button class="btn btn-outline-primary btn-sm w-100" data-action="liste#showCreate">
                    <i class="bi bi-plus-circle me-1"></i>Nouvelle liste
                </button>`;
            } else {
                divNew.innerHTML = `<p class="text-muted small text-center mt-2">Limite de listes atteinte.</p>`;
            }
            this.listesContainerTarget.appendChild(divNew);
        }
    }

    showCreate() {
        this.createFormTarget.classList.remove('d-none');
        this.nomInputTarget.focus();
    }

    async toggleItem(event) {
        const objBtn   = event.currentTarget;
        const intId    = objBtn.dataset.id;
        const intRecId = this.element.dataset.listeRecetteId;

        const strToggleUrl = this.element.dataset.listeToggleBase.replace('__ID__', intId).replace('__REC__', intRecId);

        const objResp = await fetch(strToggleUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!objResp.ok) return;
        const objData = await objResp.json();

        // Mise à jour de l'icône
        const icon = objBtn.querySelector('i:last-child');
        icon.className = objData.contains ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted';

        // Mise à jour du compteur
        const countSpan = objBtn.querySelector('.text-muted.small');
        countSpan.textContent = `${objData.count} recette${objData.count > 1 ? 's' : ''}`;

        // Mise à jour de l'icône du bouton déclencheur
        this._refreshTriggerIcon();
    }

    async createListe(event) {
        event.preventDefault();
        this.createErrorTarget.textContent = '';

        const strNom   = this.nomInputTarget.value.trim();
        const intRecId = this.element.dataset.listeRecetteId;

        if (!strNom) {
            this.createErrorTarget.textContent = 'Entrez un nom de liste.';
            return;
        }

        const objBody = new FormData();
        objBody.append('nom', strNom);
        objBody.append('recette_id', intRecId);

        const objResp = await fetch(this.element.dataset.listeCreateUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: objBody,
        });

        const objData = await objResp.json();

        if (!objResp.ok) {
            this.createErrorTarget.textContent = objData.error ?? 'Erreur.';
            return;
        }

        // Rechargement de la liste depuis le serveur
        const strUrl   = this.element.dataset.listeFetchUrl;
        const objResp2 = await fetch(strUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const objData2 = await objResp2.json();
        this._renderListes(objData2.listes, objData2.canCreate);
        this.createFormTarget.classList.add('d-none');
        this.nomInputTarget.value = '';
        this._refreshTriggerIcon();
    }

    _refreshTriggerIcon() {
        if (!this.hasTriggerIconTarget) return;
        // On indique visuellement qu'au moins une liste contient la recette
        const boolAny = this.listesContainerTarget
            .querySelectorAll('.bi-check-circle-fill').length > 0;
        this.triggerIconTarget.className = boolAny
            ? 'bi bi-bookmark-fill text-warning'
            : 'bi bi-bookmark text-muted';
    }
}
