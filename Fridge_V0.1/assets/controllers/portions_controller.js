import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['quantity', 'display'];
    static values  = { base: Number };

    connect() {
        this.current = this.baseValue || 1;
        this.render();
    }

    decrement() {
        if (this.current > 1) {
            this.current--;
            this.render();
        }
    }

    increment() {
        if (this.current < 20) {
            this.current++;
            this.render();
        }
    }

    render() {
        this.displayTarget.textContent = this.current;

        this.quantityTargets.forEach(el => {
            const base = parseFloat(el.dataset.base);
            if (isNaN(base)) return;

            const ratio  = this.current / this.baseValue;
            const result = base * ratio;
            el.textContent = Number.isInteger(result) ? result : parseFloat(result.toFixed(2));
        });
    }
}
