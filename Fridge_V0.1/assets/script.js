document.addEventListener('DOMContentLoaded', function () {

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

    // --- 1. Initialisation des Carrousels Splide ---
    if (document.querySelector('#recipe-carousel')) {
        new Splide('#recipe-carousel', {
            type: 'loop', perPage: 3, gap: '1.5rem', autoplay: true,
            breakpoints: { 768: { perPage: 1 }, 1024: { perPage: 2 } }
        }).mount();
    }
    if (document.querySelector('#like-slider')) {
        new Splide('#like-slider', {
            perPage: 4, gap: '1rem', arrows: true, pagination: false,
            breakpoints: { 992: { perPage: 3 }, 768: { perPage: 2 }, 480: { perPage: 1 } }
        }).mount();
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
    if (confirm("Voulez-vous vraiment vider tout le planning ?")) {
        document.querySelectorAll('.meal-cell').forEach(cell => {
            cell.innerHTML = `<div class="btn-add" onclick="openModal(this)"><i class="bi bi-plus-lg"></i></div>`;
            cell.onclick = null;
        });
    }
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
document.addEventListener('DOMContentLoaded', initPlanningTable);

// 1. Structure des données (Base de données simulée)
const recipesData = [
    { id: 1, titre: "Burger Végétarien au Porto", image: "https://images.unsplash.com/photo-1520072959219-c595dc870360?w=500", popularite: 95, date: 1711987200000, regime: "Végétarien" },
    { id: 2, titre: "Curry de Lentilles Corail", image: "https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=500", popularite: 88, date: 1712160000000, regime: "Vegan" },
    { id: 3, titre: "Poulet Rôti du Dimanche", image: "https://images.unsplash.com/photo-1598514982205-f36b96d1e8d4?w=500", popularite: 92, date: 1711555200000, regime: "Carné" },
    { id: 4, titre: "Salade Quinoa & Avocat", image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=500", popularite: 85, date: 1712332800000, regime: "Vegan" },
    { id: 5, titre: "Bœuf Bourguignon", image: "https://images.unsplash.com/photo-1600891964092-4316c288032e?w=500", popularite: 98, date: 1711000000000, regime: "Carné" },
    { id: 6, titre: "Lasagnes aux Épinards", image: "https://images.unsplash.com/photo-1619895092538-128341789043?w=500", popularite: 89, date: 1712419200000, regime: "Végétarien" }
];

// 2. Ciblage des éléments du DOM
const recipeGrid = document.getElementById('recipeGrid');
const filterSelect = document.getElementById('filterSelect');
const sortSelect = document.getElementById('sortSelect');
const noResultMsg = document.getElementById('noResultMsg');

// 3. Fonction de rendu des cartes
function renderRecipes(recipes) {
    recipeGrid.innerHTML = ''; // Nettoyage de la grille

    if (recipes.length === 0) {
        noResultMsg.classList.remove('d-none');
        return;
    } else {
        noResultMsg.classList.add('d-none');
    }

    recipes.forEach(recipe => {
        // Définition de la couleur du badge selon le régime
        let badgeColor = 'bg-secondary';
        if (recipe.regime === 'Vegan') badgeColor = 'bg-success';
        if (recipe.regime === 'Végétarien') badgeColor = 'bg-warning text-dark';
        if (recipe.regime === 'Carné') badgeColor = 'bg-danger';

        // Création dynamique du HTML
        const cardHTML = `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 transition hover-shadow">
                    <img src="${recipe.image}" class="card-img-top" alt="${recipe.titre}" style="height: 220px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge ${badgeColor}">${recipe.regime}</span>
                        </div>
                        <h5 class="card-title fw-bold text-dark">${recipe.titre}</h5>
                        <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="small text-muted fw-semibold">
                                <i class="bi bi-star-fill text-warning me-1"></i> ${recipe.popularite} pts
                            </span>
                            <a href="recipe.html?id=${recipe.id}" class="btn btn-sm btn-outline-success rounded-pill px-3">Découvrir</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        recipeGrid.insertAdjacentHTML('beforeend', cardHTML);
    });
}

// 4. Fonction principale de tri et de filtrage
function updateCatalog() {
    const filterValue = filterSelect.value;
    const sortValue = sortSelect.value;

    // A. Filtrage
    let filteredRecipes = recipesData.filter(recipe => {
        if (filterValue === 'all') return true;
        return recipe.regime === filterValue;
    });

    // B. Tri
    filteredRecipes.sort((a, b) => {
        if (sortValue === 'recent') {
            return b.date - a.date; // Du plus récent au plus ancien
        } else if (sortValue === 'popular') {
            return b.popularite - a.popularite; // Du meilleur score au moins bon
        }
    });

    // C. Affichage
    renderRecipes(filteredRecipes);
}

// 5. Écouteurs d'événements
filterSelect.addEventListener('change', updateCatalog);
sortSelect.addEventListener('change', updateCatalog);

// 6. Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    updateCatalog();
});

// On s'assure que le code ne s'exécute que sur la page de recherche
if (document.getElementById('filterForm')) {

    // 1. Base de données simulée pour la recherche
    const searchRecipes = [
        { id: 1, titre: "Bowl de Quinoa & Avocat", image: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400", time: 25, isVegetarian: true, isVegan: true, isGlutenFree: true, difficulty: "diffEasy", origin: "other", date: 1712332800000, pertinence: 99 },
        { id: 2, titre: "Pâtes Carbonara", image: "https://images.unsplash.com/photo-1612874742237-6526221588e3?w=400", time: 20, isVegetarian: false, isVegan: false, isGlutenFree: false, difficulty: "diffMedium", origin: "it", date: 1711000000000, pertinence: 85 },
        { id: 3, titre: "Curry Vert Thaï", image: "https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=400", time: 45, isVegetarian: true, isVegan: true, isGlutenFree: true, difficulty: "diffMedium", origin: "asia", date: 1712160000000, pertinence: 92 },
        { id: 4, titre: "Bœuf Bourguignon", image: "https://images.unsplash.com/photo-1600891964092-4316c288032e?w=400", time: 120, isVegetarian: false, isVegan: false, isGlutenFree: false, difficulty: "diffHard", origin: "fr", date: 1710000000000, pertinence: 95 },
        { id: 5, titre: "Tacos Al Pastor", image: "https://images.unsplash.com/photo-1551504734-5ee1c4a1479b?w=400", time: 60, isVegetarian: false, isVegan: false, isGlutenFree: true, difficulty: "diffMedium", origin: "mex", date: 1711500000000, pertinence: 88 },
        { id: 6, titre: "Couscous aux Légumes", image: "https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400", time: 90, isVegetarian: true, isVegan: true, isGlutenFree: false, difficulty: "diffHard", origin: "orient", date: 1711900000000, pertinence: 90 }
    ];

    // 2. Ciblage des éléments DOM
    const grid = document.getElementById('searchResultsGrid');
    const count = document.getElementById('resultsCount');
    const form = document.getElementById('filterForm');
    const sortSelect = document.getElementById('sortResults');

    // 3. Fonction pour générer l'affichage
    function renderSearchRecipes(recipes) {
        grid.innerHTML = '';
        count.innerText = recipes.length;

        if (recipes.length === 0) {
            grid.innerHTML = `<div class="col-12 text-center py-5"><h5 class="text-muted">Aucune recette trouvée avec ces critères.</h5></div>`;
            return;
        }

        recipes.forEach(recipe => {
            // Détermine le badge principal
            let badgeHtml = '';
            if (recipe.isVegan) {
                badgeHtml = `<span class="badge bg-success mb-2">Vegan</span>`;
            } else if (recipe.isVegetarian) {
                badgeHtml = `<span class="badge bg-warning text-dark mb-2">Végétarien</span>`;
            } else {
                badgeHtml = `<span class="badge bg-danger mb-2">Carné</span>`;
            }

            // Traduction de l'origine
            const originesMap = { "fr": "Française", "it": "Italienne", "asia": "Asiatique", "mex": "Mexicaine", "orient": "Orientale", "other": "Internationale" };
            const originText = originesMap[recipe.origin] || "Internationale";

            const cardHTML = `
                <div class="col">
                    <a href="recipe.html?id=${recipe.id}" class="text-decoration-none text-dark">
                        <div class="card recipe-card shadow-sm h-100 transition hover-shadow border-0">
                            <div class="position-absolute top-0 end-0 bg-dark text-white p-1 rounded-bottom-start shadow-sm small z-1">
                                <i class="bi bi-clock me-1"></i>${recipe.time} min
                            </div>
                            <img src="${recipe.image}" class="card-img-top" alt="${recipe.titre}" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                ${badgeHtml}
                                <h6 class="fw-bold mb-1 text-truncate">${recipe.titre}</h6>
                                <div class="small text-muted mt-2"><i class="bi bi-globe me-1"></i> ${originText}</div>
                            </div>
                        </div>
                    </a>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', cardHTML);
        });
    }

    // 4. Fonction principale de filtrage et de tri
    function applyFilters() {
        // Récupération des valeurs des filtres
        const isVeg = document.getElementById('filterVegetarian').checked;
        const isVegan = document.getElementById('filterVegan').checked;
        const isGF = document.getElementById('filterGlutenFree').checked;

        const diffEasy = document.getElementById('diffEasy').checked;
        const diffMedium = document.getElementById('diffMedium').checked;
        const diffHard = document.getElementById('diffHard').checked;

        const origin = document.getElementById('filterOrigin').value;
        const maxTime = parseInt(document.getElementById('timeRange').value);
        const sortBy = sortSelect.value;

        // Filtrage
        let filtered = searchRecipes.filter(r => {
            // Régime (Si la case est cochée, la recette DOIT avoir ce régime)
            if (isVeg && !r.isVegetarian) return false;
            if (isVegan && !r.isVegan) return false;
            if (isGF && !r.isGlutenFree) return false;

            // Difficulté (Logique "OU" : si aucune case cochée, on prend tout. Sinon, la recette doit correspondre à au moins une case cochée)
            const diffSelected = diffEasy || diffMedium || diffHard;
            if (diffSelected) {
                if (!(
                    (diffEasy && r.difficulty === 'diffEasy') ||
                    (diffMedium && r.difficulty === 'diffMedium') ||
                    (diffHard && r.difficulty === 'diffHard')
                )) return false;
            }

            // Origine
            if (origin !== "" && r.origin !== origin) return false;

            // Temps
            if (r.time > maxTime) return false;

            return true;
        });

        // Tri
        filtered.sort((a, b) => {
            if (sortBy === 'pertinence') return b.pertinence - a.pertinence;
            if (sortBy === 'recent') return b.date - a.date;
            if (sortBy === 'time') return a.time - b.time; // Temps le plus court en premier
        });

        // Affichage
        renderSearchRecipes(filtered);
    }

    // 5. Écouteurs d'événements
    // Dès qu'on change un champ dans le formulaire, on met à jour
    form.addEventListener('change', applyFilters);
    // Cas spécifique pour le slider de temps (pour qu'il réagisse pendant qu'on le glisse)
    document.getElementById('timeRange').addEventListener('input', applyFilters);
    // Pour le tri
    sortSelect.addEventListener('change', applyFilters);

    // Gérer le bouton "Réinitialiser"
    form.addEventListener('reset', () => {
        // Le reset du formulaire a besoin d'un micro-délai pour s'appliquer avant qu'on relance le filtre
        setTimeout(() => {
            document.getElementById('timeValue').innerText = document.getElementById('timeRange').value + ' min';
            applyFilters();
        }, 10);
    });

    // 6. Initialisation au premier chargement
    document.getElementById('timeRange').value = 60; // Valeur par défaut
    document.getElementById('timeValue').innerText = '60 min';
    applyFilters();
}