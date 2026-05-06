import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';

export default class extends Controller {
    connect() {
        this.initSplide();
    }

    initSplide() {
        if (this.element.classList.contains('is-initialized')) return;

        new Splide(this.element, {
            type: 'loop',
            perPage: 4,
            perMove: 1,
            gap: '2rem',
            pagination: false,
            arrows: true,
            breakpoints: {
                1200: { perPage: 3 },
                992: { perPage: 2 },
                576: { perPage: 1 },
            },
        }).mount();

        this.element.classList.add('is-initialized');
    }
}
