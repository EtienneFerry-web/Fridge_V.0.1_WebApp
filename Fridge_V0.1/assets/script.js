document.addEventListener('turbo:load', function () {

    // --- 0. Validation des Formulaires (Bootstrap + temps réel) ---

    // Activation de la validation Bootstrap sur tous les formulaires .needs-validation
    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Validation en temps réel : dès qu'un champ perd le focus
    document.querySelectorAll('.needs-validation .form-control').forEach(function (input) {
        input.addEventListener('blur', function () {
            const form = input.closest('form');
            if (form && form.classList.contains('needs-validation')) {
                input.classList.toggle('is-valid', input.validity.valid && input.value !== '');
                input.classList.toggle('is-invalid', !input.validity.valid);
            }
        });
        input.addEventListener('input', function () {
            if (input.classList.contains('is-invalid')) {
                input.classList.toggle('is-valid', input.validity.valid && input.value !== '');
                input.classList.toggle('is-invalid', !input.validity.valid);
            }
        });
    });

    // Validation confirmation mot de passe (createAccount.html)
    const pwdField = document.getElementById('pwd');
    const pwdConfirm = document.getElementById('pwd_confirm');
    if (pwdField && pwdConfirm) {
        function checkPasswordMatch() {
            const feedback = document.getElementById('pwd-confirm-feedback');
            if (pwdConfirm.value === '') {
                pwdConfirm.setCustomValidity('');
                return;
            }
            if (pwdField.value !== pwdConfirm.value) {
                pwdConfirm.setCustomValidity('no-match');
                if (feedback) feedback.textContent = 'Les mots de passe ne correspondent pas.';
            } else {
                pwdConfirm.setCustomValidity('');
                if (feedback) feedback.textContent = 'Veuillez confirmer votre mot de passe.';
            }
        }
        pwdField.addEventListener('input', checkPasswordMatch);
        pwdConfirm.addEventListener('input', checkPasswordMatch);
        pwdConfirm.addEventListener('blur', function () {
            checkPasswordMatch();
            pwdConfirm.classList.toggle('is-valid', pwdConfirm.validity.valid && pwdConfirm.value !== '');
            pwdConfirm.classList.toggle('is-invalid', !pwdConfirm.validity.valid);
        });
    }

    // --- 2. Interactions de Recherche ---
    document.querySelectorAll('.recipe-like-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const icon = this.querySelector('i');
            if (icon.classList.contains('bi-heart')) icon.classList.replace('bi-heart', 'bi-heart-fill');
            else icon.classList.replace('bi-heart-fill', 'bi-heart');
        });
    });

    // --- 3. Gestion des Modales (Admin Dashboard) ---
    const modalBan = document.getElementById('modalBan');
    if (modalBan) {
        modalBan.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const pseudo = button.getAttribute('data-pseudo');
            const modalPseudoDisplay = modalBan.querySelector('#modalPseudoDisplay');
            if (modalPseudoDisplay) modalPseudoDisplay.textContent = pseudo;
        });
    }

});