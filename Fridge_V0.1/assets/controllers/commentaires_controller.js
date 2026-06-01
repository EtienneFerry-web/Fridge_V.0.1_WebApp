import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'starsInput', 'starsList', 'avgDisplay', 'countDisplay',
        'commentForm', 'commentInput', 'commentsList', 'submitBtn'
    ];
    static values = { addUrl: String, noteUrl: String, deleteBase: String };

    async rateRecette(event) {
        const valeur = parseInt(event.currentTarget.dataset.value, 10);
        this.starsInputTarget.value = valeur;
        this._applyStarColors(valeur);

        const body = new FormData();
        body.append('valeur', valeur);

        const res = await fetch(this.noteUrlValue, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body,
        });

        if (!res.ok) return;

        const data = await res.json();
        this.avgDisplayTarget.textContent  = data.average;
        this.countDisplayTarget.textContent = data.count;
    }

    hoverStar(event) {
        const val = parseInt(event.currentTarget.dataset.value, 10);
        this._applyStarColors(val, true);
    }

    resetHover() {
        const current = parseInt(this.starsInputTarget.value, 10);
        this._applyStarColors(current);
    }

    _applyStarColors(activeVal, isHover = false) {
        this.starsListTarget.querySelectorAll('.star-btn').forEach(btn => {
            const v   = parseInt(btn.dataset.value, 10);
            const ico = btn.querySelector('i');
            if (v <= activeVal) {
                btn.style.color = 'var(--warning)';
                ico.className   = 'bi bi-star-fill';
            } else {
                btn.style.color = isHover ? 'var(--text-muted)' : 'var(--text-muted)';
                ico.className   = 'bi bi-star';
            }
        });
    }

    async submitComment(event) {
        const contenu = this.commentInputTarget.value.trim();
        if (!contenu) return;

        this.submitBtnTarget.disabled = true;

        const body = new FormData();
        body.append('contenu', contenu);

        const res = await fetch(this.addUrlValue, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body,
        });

        this.submitBtnTarget.disabled = false;

        if (!res.ok) return;

        const data = await res.json();
        this.commentInputTarget.value = '';

        const noMsg = this.commentsListTarget.querySelector('#no-comments-msg');
        if (noMsg) noMsg.remove();

        const html = this._buildCommentHtml(data);
        this.commentsListTarget.insertAdjacentHTML('afterbegin', html);
    }

    async deleteComment(event) {
        const id  = event.currentTarget.dataset.id;
        const url = this.deleteBaseValue.replace('__ID__', id);

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!res.ok) return;

        const data = await res.json();
        if (data.deleted) {
            const el = this.commentsListTarget.querySelector(`[data-comment-id="${id}"]`);
            if (el) el.remove();
        }
    }

    _buildCommentHtml(data) {
        const avatar = `<div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
             style="width:40px;height:40px;background:var(--primary);font-size:.9rem;">${data.avatar}</div>`;

        const deleteBtn = `<button class="btn btn-sm btn-link text-danger p-0"
                data-action="click->commentaires#deleteComment"
                data-id="${data.id}"
                title="Supprimer">
            <i class="bi bi-trash3"></i>
        </button>`;

        return `<div class="d-flex gap-3 mb-4 pb-4 border-bottom" data-comment-id="${data.id}">
            ${avatar}
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="fw-semibold">${data.username}</span>
                        <span class="text-muted small ms-2">${data.createdAt}</span>
                    </div>
                    ${deleteBtn}
                </div>
                <p class="mt-1 mb-0" style="line-height:1.6;">${this._escape(data.contenu)}</p>
            </div>
        </div>`;
    }

    _escape(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }
}
