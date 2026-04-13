import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['likeIcon', 'likeCount', 'favoriIcon'];
    static values  = { liked: Boolean, favori: Boolean };

    async toggleLike(event) {
        event.preventDefault();

        const strUrl = this.element.dataset.likeUrl;
        const objResponse = await fetch(strUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const objData = await objResponse.json();

        this.likedValue = objData.liked;
        this.likeCountTarget.textContent = objData.count;
        this.likeIconTarget.className = objData.liked
            ? 'bi bi-heart-fill text-danger'
            : 'bi bi-heart text-muted';
    }

    async toggleFavori(event) {
        event.preventDefault();

        const strUrl = this.element.dataset.favoriUrl;
        const objResponse = await fetch(strUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const objData = await objResponse.json();

        this.favoriValue = objData.favori;
        this.favoriIconTarget.className = objData.favori
            ? 'bi bi-bookmark-fill text-warning'
            : 'bi bi-bookmark text-muted';
    }
}