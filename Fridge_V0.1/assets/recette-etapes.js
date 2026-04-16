document.addEventListener('DOMContentLoaded', function () {
    const container  = document.getElementById('etapes-container');
    const btnAjouter = document.getElementById('btn-ajouter-etape');

    if (!container || !btnAjouter) return;

    const prototype  = document.getElementById('etapes-prototype').dataset.prototype;
    let index        = container.querySelectorAll('.etape-row').length;

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