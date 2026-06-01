import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay', 'stepNumber', 'stepTitle', 'stepDescription', 'stepDuration',
                      'timerBlock', 'timerDisplay', 'timerBtn', 'prevBtn', 'nextBtn',
                      'progressBar', 'progressText'];
    static values  = { steps: Array };

    connect() {
        this.current    = 0;
        this.timerSecs  = 0;
        this.timerRunning = false;
        this.timerInterval = null;
    }

    open() {
        this.current = 0;
        this.overlayTarget.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        this.renderStep();
    }

    close() {
        this.overlayTarget.style.display = 'none';
        document.body.style.overflow = '';
        this.stopTimer();
    }

    prev() {
        if (this.current > 0) {
            this.current--;
            this.stopTimer();
            this.renderStep();
        }
    }

    next() {
        if (this.current < this.stepsValue.length - 1) {
            this.current++;
            this.stopTimer();
            this.renderStep();
        } else {
            this.close();
        }
    }

    toggleTimer() {
        this.timerRunning ? this.stopTimer() : this.startTimer();
    }

    startTimer() {
        if (this.timerSecs <= 0) return;
        this.timerRunning = true;
        this.timerBtnTarget.innerHTML = '<i class="bi bi-pause-fill me-1"></i>Pause';
        this.timerInterval = setInterval(() => {
            this.timerSecs--;
            this.updateTimerDisplay();
            if (this.timerSecs <= 0) {
                this.stopTimer();
                this.timerBtnTarget.textContent = 'Terminé !';
            }
        }, 1000);
    }

    stopTimer() {
        this.timerRunning = false;
        clearInterval(this.timerInterval);
        this.timerInterval = null;
        if (this.hasTimerBtnTarget) {
            this.timerBtnTarget.innerHTML = '<i class="bi bi-play-fill me-1"></i>Démarrer';
        }
    }

    updateTimerDisplay() {
        const m = String(Math.floor(this.timerSecs / 60)).padStart(2, '0');
        const s = String(this.timerSecs % 60).padStart(2, '0');
        this.timerDisplayTarget.textContent = `${m}:${s}`;
    }

    renderStep() {
        const step  = this.stepsValue[this.current];
        const total = this.stepsValue.length;

        this.stepNumberTarget.textContent      = this.current + 1;
        this.stepTitleTarget.textContent       = step.libelle;
        this.stepDescriptionTarget.textContent = step.description;

        const pct = ((this.current + 1) / total) * 100;
        this.progressBarTarget.style.width     = `${pct}%`;
        this.progressTextTarget.textContent    = `Étape ${this.current + 1} / ${total}`;

        this.prevBtnTarget.disabled = this.current === 0;
        this.nextBtnTarget.innerHTML = this.current === total - 1
            ? '<i class="bi bi-check-lg me-1"></i>Terminer'
            : 'Suivant<i class="bi bi-chevron-right ms-1"></i>';

        if (step.duree) {
            this.stepDurationTarget.textContent = `⏱ ${step.duree} min`;
            this.stepDurationTarget.style.display = 'block';
            this.timerBlockTarget.style.display = 'flex';
            this.timerSecs = step.duree * 60;
            this.updateTimerDisplay();
            this.timerBtnTarget.innerHTML = '<i class="bi bi-play-fill me-1"></i>Démarrer';
        } else {
            this.stepDurationTarget.style.display = 'none';
            this.timerBlockTarget.style.display = 'none';
            this.timerSecs = 0;
        }
    }
}
