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

// --- 4. Logique du Planning Hebdomadaire ---
window.openModal = function (cellElement) {
    const modal = document.getElementById('recipeModal');
    const overlay = document.getElementById('modalOverlay');
    if (!modal || !overlay) return;
    window.currentCellTarget = cellElement.closest('.meal-cell');
    modal.classList.add('show');
    overlay.classList.add('show');
    if (typeof applyFilters === 'function') applyFilters();
};

window.closeModal = function () {
    const modal = document.getElementById('recipeModal');
    const overlay = document.getElementById('modalOverlay');
    if (modal) modal.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    window.currentCellTarget = null;
};

window.switchTab = function (tabName) {
    window.currentTab = tabName;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    if (typeof applyFilters === 'function') applyFilters();
};

window.selectRecipe = function (recipe) {
    if (!window.currentCellTarget) return;
    window.currentCellTarget.innerHTML = `
        <div class="selected-recipe">
            <img src="${recipe.img}" alt="${recipe.title}">
            <span class="text-truncate w-100 px-1" title="${recipe.title}">${recipe.title}</span>
        </div>
    `;
    window.currentCellTarget.onclick = function () { openModal(this); };
    closeModal();
};

window.clearPlanning = function () {
    document.querySelectorAll('.meal-cell').forEach(cell => {
        cell.innerHTML = `<div class="btn-add" onclick="openModal(this)"><i class="bi bi-plus-lg"></i></div>`;
        cell.onclick = null;
    });
};

// --- 5. Données et Logique Planning (Mock) ---
window.mockRecipes = [
    { id: 1, title: "Bowl de Quinoa Rose", img: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150", type: "likes", category: "Déjeuner" },
    { id: 2, title: "Salade Méditerranéenne", img: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=150", type: "likes", category: "Déjeuner" },
    { id: 3, title: "Pancakes Banane", img: "https://images.unsplash.com/photo-1567620905732-2d1ec7bb7445?w=150", type: "favoris", category: "Petit-Déjeuner" },
    { id: 4, title: "Pizza Veggie", img: "https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=150", type: "favoris", category: "Dîner" },
    { id: 5, title: "Porridge aux fruits", img: "https://images.unsplash.com/photo-1517673132405-a56a62b18caf?w=150", type: "likes", category: "Petit-Déjeuner" },
    { id: 6, title: "Curry de Pois Chiches", img: "https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=150", type: "favoris", category: "Dîner" },
    { id: 7, title: "Muffins Myrtilles", img: "https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=150", type: "likes", category: "Collation" }
];

window.currentCellTarget = null;
window.currentTab = 'likes';

window.applyFilters = function () {
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const grid = document.getElementById('recipeGrid');
    if (!grid) return;
    const searchQuery = searchInput ? searchInput.value.toLowerCase() : "";
    const sortOption = sortSelect ? sortSelect.value : "az";
    let filteredRecipes = window.mockRecipes.filter(recipe => {
        const matchTab = recipe.type === window.currentTab;
        const matchSearch = recipe.title.toLowerCase().includes(searchQuery);
        return matchTab && matchSearch;
    });
    filteredRecipes.sort((a, b) => {
        if (sortOption === 'az') return a.title.localeCompare(b.title);
        if (sortOption === 'za') return b.title.localeCompare(a.title);
        if (sortOption === 'cat') return a.category.localeCompare(b.category);
        return 0;
    });
    grid.innerHTML = '';
    if (filteredRecipes.length === 0) {
        grid.innerHTML = `<p class="text-muted text-center w-100 mt-3" style="grid-column: 1 / -1;">Aucune recette trouvée.</p>`;
        return;
    }
    filteredRecipes.forEach(recipe => {
        const card = document.createElement('div');
        card.className = 'modal-recipe-card shadow-sm';
        card.onclick = () => selectRecipe(recipe);
        card.innerHTML = `<img src="${recipe.img}" alt="${recipe.title}"><span class="text-truncate" title="${recipe.title}">${recipe.title}</span><small>${recipe.category}</small>`;
        grid.appendChild(card);
    });
};

function initPlanningTable() {
    const tbody = document.getElementById("planningBody");
    if (!tbody) return;
    tbody.innerHTML = "";
    const categories = ["Petit-Déjeuner", "Déjeuner", "Dîner", "Dessert / Collation"];
    categories.forEach((cat) => {
        const trCat = document.createElement("tr"); trCat.className = "category-row";
        trCat.innerHTML = `<td colspan="7">${cat}</td>`; tbody.appendChild(trCat);
        const trDays = document.createElement("tr");
        for (let i = 0; i < 7; i++) {
            const td = document.createElement("td"); td.className = "meal-cell";
            td.innerHTML = `<div class="btn-add" onclick="openModal(this)"><i class="bi bi-plus-lg"></i></div>`;
            trDays.appendChild(td);
        }
        tbody.appendChild(trDays);
    });
}
document.addEventListener('turbo:load', initPlanningTable);
