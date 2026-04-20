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
document.addEventListener('turbo:load', () => {
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

/* ============================================================
   STOCK — logique JS de la page Mon Stock
   ============================================================ */

const STORAGE_KEY = 'fridge_stock';
let stockItems = loadStock();
let deleteTargetId = null;

/* ---------- Persistance ---------- */

function loadStock() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
    catch { return []; }
}

function saveStock() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(stockItems));
}

/* ---------- Helpers ---------- */

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2);
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}

function formatDate(isoDate) {
    if (!isoDate) return '—';
    const [y, m, d] = isoDate.split('-');
    return `${d}/${m}/${y}`;
}

/* ---------- Statut d'un article ---------- */

function getItemStatus(item) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (item.expiry) {
        const expDate = new Date(item.expiry);
        expDate.setHours(0, 0, 0, 0);
        const diffDays = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));
        if (diffDays < 0)  return 'expired';
        if (diffDays <= 7) return 'warning';
    }

    if (item.threshold > 0 && parseFloat(item.qty) <= parseFloat(item.threshold)) {
        return 'low';
    }

    return 'ok';
}

/* ---------- Construction d'une carte article ---------- */

function buildItemCard(item) {
    const status = getItemStatus(item);

    const statusConfig = {
        expired: {
            badge:     '<span class="badge bg-danger rounded-pill">périmé</span>',
            cardClass: 'stock-card-expired',
            icon:      'bi-x-circle-fill text-danger'
        },
        warning: {
            badge:     '<span class="badge stock-badge-warning rounded-pill">bientôt périmé</span>',
            cardClass: 'stock-card-warning',
            icon:      'bi-clock-history stock-icon-warning'
        },
        low: {
            badge:     '<span class="badge stock-badge-low rounded-pill">stock bas</span>',
            cardClass: 'stock-card-low',
            icon:      'bi-exclamation-triangle-fill stock-icon-low'
        },
        ok: {
            badge:     '<span class="badge stock-badge-ok rounded-pill">OK</span>',
            cardClass: '',
            icon:      'bi-check-circle-fill stock-icon-ok'
        }
    };

    const cfg = statusConfig[status];

    return `
    <div class="card shadow-sm border-0 mb-3 stock-item-card ${cfg.cardClass}" data-id="${item.id}">
        <div class="card-body p-3">
            <div class="row align-items-center g-2">
                <div class="col-auto d-none d-sm-block">
                    <i class="bi ${cfg.icon} fs-4"></i>
                </div>
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="fw-bold">${escapeHtml(item.name)}</span>
                        ${cfg.badge}
                    </div>
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span>
                            <i class="bi bi-box-seam me-1"></i>
                            <strong class="text-dark">${item.qty} ${item.unit}</strong>
                            ${item.threshold > 0
                                ? `<span class="text-muted">/ seuil : ${item.threshold} ${item.unit}</span>`
                                : ''}
                        </span>
                        <span>
                            <i class="bi bi-calendar me-1"></i>
                            ${item.expiry
                                ? `<span class="${
                                    status === 'expired' ? 'text-danger fw-bold'
                                    : status === 'warning' ? 'stock-text-warning fw-bold'
                                    : ''
                                  }">${formatDate(item.expiry)}</span>`
                                : '<span class="text-muted fst-italic">Aucune date</span>'}
                        </span>
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm"
                        onclick="startEdit('${item.id}')"
                        aria-label="Modifier ${escapeHtml(item.name)}"
                        title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-primary btn-sm"
                        onclick="openDeleteModal('${item.id}')"
                        aria-label="Supprimer ${escapeHtml(item.name)}"
                        title="Supprimer">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}

/* ---------- Rendu de la liste ---------- */

function renderStock() {
    const search       = document.getElementById('searchInput').value.toLowerCase();
    const filterStatus = document.getElementById('filterStatus').value;
    const sortBy       = document.getElementById('sortBy').value;
    const list         = document.getElementById('stockList');

    let items = stockItems.filter(item => {
        if (search && !item.name.toLowerCase().includes(search)) return false;
        if (filterStatus !== 'all') {
            const status = getItemStatus(item);
            if (filterStatus === 'low'     && status !== 'low')     return false;
            if (filterStatus === 'expired' && status !== 'expired') return false;
            if (filterStatus === 'ok'      && status !== 'ok')      return false;
        }
        return true;
    });

    items.sort((a, b) => {
        if (sortBy === 'name')   return a.name.localeCompare(b.name);
        if (sortBy === 'qty')    return parseFloat(a.qty) - parseFloat(b.qty);
        if (sortBy === 'expiry') {
            if (!a.expiry && !b.expiry) return 0;
            if (!a.expiry) return 1;
            if (!b.expiry) return -1;
            return new Date(a.expiry) - new Date(b.expiry);
        }
        return 0;
    });

    /* Mise à jour des badges de comptage */
    const lowCount = stockItems.filter(i => ['low', 'expired'].includes(getItemStatus(i))).length;
    document.getElementById('totalCount').textContent    = stockItems.length;
    document.getElementById('lowStockCount').textContent = lowCount;
    document.getElementById('lowStockBadge').classList.toggle('d-none', lowCount === 0);

    /* Rendu */
    if (items.length === 0) {
        const isEmpty = !search && filterStatus === 'all';
        list.innerHTML = `
            <div id="emptyState" class="text-center py-5">
                <i class="bi bi-basket display-3 text-muted d-block mb-3"></i>
                <h3 class="h5 text-muted">${isEmpty
                    ? 'Votre stock est vide'
                    : 'Aucun ingrédient ne correspond à vos critères.'}</h3>
                <p class="text-muted small mb-0">${isEmpty
                    ? 'Ajoutez votre premier ingrédient via le formulaire.'
                    : 'Essayez de modifier votre recherche ou vos filtres.'}</p>
            </div>`;
        return;
    }

    list.innerHTML = items.map(item => buildItemCard(item)).join('');
}

/* ---------- Formulaire d'ajout / modification ---------- */

document.getElementById('stockForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!this.checkValidity()) { this.classList.add('was-validated'); return; }

    const editId  = document.getElementById('editId').value;
    const newItem = {
        id:        editId || generateId(),
        name:      document.getElementById('ingredientName').value.trim(),
        qty:       parseFloat(document.getElementById('ingredientQty').value),
        unit:      document.getElementById('ingredientUnit').value,
        threshold: parseFloat(document.getElementById('ingredientThreshold').value) || 0,
        expiry:    document.getElementById('ingredientExpiry').value || null,
    };

    if (editId) {
        const idx = stockItems.findIndex(i => i.id === editId);
        if (idx !== -1) stockItems[idx] = newItem;
    } else {
        stockItems.unshift(newItem);
    }

    saveStock();
    renderStock();
    resetForm();
});

function startEdit(id) {
    const item = stockItems.find(i => i.id === id);
    if (!item) return;

    document.getElementById('stockForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    document.getElementById('editId').value              = item.id;
    document.getElementById('ingredientName').value      = item.name;
    document.getElementById('ingredientQty').value       = item.qty;
    document.getElementById('ingredientUnit').value      = item.unit;
    document.getElementById('ingredientThreshold').value = item.threshold;
    document.getElementById('ingredientExpiry').value    = item.expiry || '';

    document.getElementById('formTitle').innerHTML = `
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white"
            style="width:32px; height:32px; font-size:1rem;">
            <i class="bi bi-pencil"></i>
        </span>
        Modifier l'ingrédient`;

    document.getElementById('submitBtn').innerHTML     = '<i class="bi bi-check-circle me-2"></i>Enregistrer les modifications';
    document.getElementById('cancelEditBtn').classList.remove('d-none');
}

function cancelEdit() { resetForm(); }

function resetForm() {
    const form = document.getElementById('stockForm');
    form.reset();
    form.classList.remove('was-validated');
    document.getElementById('editId').value = '';

    document.getElementById('formTitle').innerHTML = `
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
            style="width:32px; height:32px; font-size:1rem;">
            <i class="bi bi-plus-lg"></i>
        </span>
        Ajouter un ingrédient`;

    document.getElementById('submitBtn').innerHTML     = '<i class="bi bi-plus-circle me-2"></i>Ajouter au stock';
    document.getElementById('cancelEditBtn').classList.add('d-none');
}

/* ---------- Modale de suppression ---------- */

function openDeleteModal(id) {
    const item = stockItems.find(i => i.id === id);
    if (!item) return;
    deleteTargetId = id;
    document.getElementById('deleteIngredientName').textContent = item.name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!deleteTargetId) return;
    stockItems = stockItems.filter(i => i.id !== deleteTargetId);
    saveStock();
    renderStock();
    deleteTargetId = null;
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
});

/* ---------- Validation en temps réel ---------- */

document.querySelectorAll('#stockForm .form-control').forEach(input => {
    input.addEventListener('blur', function () {
        const form = input.closest('form');
        if (form && form.classList.contains('needs-validation')) {
            input.classList.toggle('is-valid',   input.validity.valid && input.value !== '');
            input.classList.toggle('is-invalid', !input.validity.valid);
        }
    });
});

/* ---------- Init ---------- */

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
renderStock();

/* ============================================================
   COURSES — logique JS de la page Liste de Courses
   ============================================================ */

const COURSES_KEY = 'fridge_courses';
const STOCK_KEY   = 'fridge_stock';

const CATEGORIES = {
    'Fruits & Légumes':         { emoji: '🥦', color: 'courses-cat-green'  },
    'Crémerie':                 { emoji: '🧀', color: 'courses-cat-yellow' },
    'Boucherie & Poissonnerie': { emoji: '🥩', color: 'courses-cat-red'    },
    'Épicerie sucrée':          { emoji: '🍫', color: 'courses-cat-brown'  },
    'Épicerie salée':           { emoji: '🥫', color: 'courses-cat-orange' },
    'Boissons':                 { emoji: '🧃', color: 'courses-cat-blue'   },
    'Surgelés':                 { emoji: '❄️', color: 'courses-cat-ice'    },
    'Hygiène & Beauté':         { emoji: '🧴', color: 'courses-cat-pink'   },
    'Entretien':                { emoji: '🧹', color: 'courses-cat-grey'   },
    'Autre':                    { emoji: '📦', color: 'courses-cat-grey'   },
};

const CATEGORY_ORDER = [
    'Fruits & Légumes', 'Crémerie', 'Boucherie & Poissonnerie',
    'Épicerie salée', 'Épicerie sucrée', 'Boissons', 'Surgelés',
    'Hygiène & Beauté', 'Entretien', 'Autre'
];

let courseItems = loadCourses();
let clearTarget = 'all';

/* ---------- Persistance ---------- */

function loadCourses() {
    try { return JSON.parse(localStorage.getItem(COURSES_KEY)) || []; }
    catch { return []; }
}

function saveCourses() {
    localStorage.setItem(COURSES_KEY, JSON.stringify(courseItems));
}

function loadStock() {
    try { return JSON.parse(localStorage.getItem(STOCK_KEY)) || []; }
    catch { return []; }
}

function saveStock(items) {
    localStorage.setItem(STOCK_KEY, JSON.stringify(items));
}

/* ---------- Helpers ---------- */

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).slice(2);
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* ---------- Toast de feedback ---------- */

function showToast(message, type = 'success') {
    const toast   = document.getElementById('toastFeedback');
    const body    = document.getElementById('toastBody');
    const icon    = type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill';
    const bgClass = type === 'success' ? 'bg-primary' : 'text-bg-secondary';
    toast.className = `toast align-items-center text-white border-0 ${bgClass}`;
    body.innerHTML  = `<i class="bi ${icon}"></i> ${escapeHtml(message)}`;
    new bootstrap.Toast(toast, { delay: 2800 }).show();
}

/* ---------- Suggestions depuis le stock ---------- */

function updateSuggestions() {
    const dl = document.getElementById('ingredientSuggestions');
    dl.innerHTML = loadStock()
        .map(i => `<option value="${escapeHtml(i.name)}">`)
        .join('');
}

/* ---------- Formulaire d'ajout ---------- */

document.getElementById('coursesForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!this.checkValidity()) { this.classList.add('was-validated'); return; }

    const name = document.getElementById('itemName').value.trim();
    if (!name) return;

    courseItems.push({
        id:       generateId(),
        name,
        qty:      parseFloat(document.getElementById('itemQty').value) || 1,
        unit:     document.getElementById('itemUnit').value,
        category: document.getElementById('itemCategory').value,
        checked:  false,
    });

    saveCourses();
    renderList();

    document.getElementById('itemName').value = '';
    document.getElementById('itemName').classList.remove('is-valid');
    this.classList.remove('was-validated');
    document.getElementById('itemName').focus();
});

/* Validation en temps réel sur le champ nom */
document.getElementById('itemName').addEventListener('blur', function () {
    const form = this.closest('form');
    if (form.classList.contains('needs-validation')) {
        this.classList.toggle('is-valid',   this.validity.valid && this.value !== '');
        this.classList.toggle('is-invalid', !this.validity.valid);
    }
});

/* ---------- Rendu de la liste ---------- */

function renderList() {
    const search      = document.getElementById('searchInput').value.toLowerCase();
    const filterState = document.getElementById('filterState').value;
    const container   = document.getElementById('coursesList');

    let items = courseItems.filter(item => {
        if (search && !item.name.toLowerCase().includes(search)) return false;
        if (filterState === 'todo' &&  item.checked) return false;
        if (filterState === 'done' && !item.checked) return false;
        return true;
    });

    /* Mise à jour des compteurs et de la barre de progression */
    const total   = courseItems.length;
    const checked = courseItems.filter(i => i.checked).length;
    const pct     = total > 0 ? Math.round((checked / total) * 100) : 0;

    document.getElementById('totalCount').textContent   = total;
    document.getElementById('checkedCount').textContent = checked;
    document.getElementById('progressBar').style.width  = pct + '%';
    document.getElementById('progressBar').setAttribute('aria-valuenow', pct);
    document.getElementById('progressLabel').textContent = pct + ' %';
    document.getElementById('checkedBadge').classList.toggle('d-none', checked === 0);
    document.getElementById('transferBtn').disabled     = checked === 0;
    document.getElementById('clearCheckedBtn').disabled = checked === 0;
    document.getElementById('clearAllBtn').disabled     = total === 0;

    /* État vide */
    if (items.length === 0) {
        const isEmpty = !search && filterState === 'all';
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-3 text-muted d-block mb-3"></i>
                <h3 class="h5 text-muted">${isEmpty
                    ? 'Votre liste est vide'
                    : 'Aucun article ne correspond.'}</h3>
                <p class="text-muted small mb-0">${isEmpty
                    ? 'Ajoutez votre premier article via le formulaire.'
                    : 'Modifiez votre recherche ou vos filtres.'}</p>
            </div>`;
        return;
    }

    /* Regroupement par catégorie */
    const groups = {};
    items.forEach(item => {
        const cat = item.category || 'Autre';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(item);
    });

    container.innerHTML = CATEGORY_ORDER
        .filter(cat => groups[cat])
        .map(cat => {
            const catItems = groups[cat];
            const cfg      = CATEGORIES[cat] || CATEGORIES['Autre'];
            const allDone  = catItems.every(i => i.checked);
            const donePct  = Math.round((catItems.filter(i => i.checked).length / catItems.length) * 100);

            return `
            <div class="courses-group mb-3 ${allDone ? 'courses-group-done' : ''}">
                <div class="courses-group-header d-flex align-items-center justify-content-between px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="courses-cat-badge ${cfg.color}">${cfg.emoji} ${escapeHtml(cat)}</span>
                        <span class="small text-muted">${catItems.filter(i => i.checked).length}/${catItems.length}</span>
                    </div>
                    <div class="progress courses-group-progress" role="progressbar"
                        aria-label="Avancement ${escapeHtml(cat)}"
                        aria-valuenow="${donePct}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-primary" style="width: ${donePct}%"></div>
                    </div>
                </div>
                <ul class="list-unstyled mb-0" role="list">
                    ${catItems.map(item => buildItemRow(item)).join('')}
                </ul>
            </div>`;
        })
        .join('');
}

function buildItemRow(item) {
    return `
    <li class="courses-item ${item.checked ? 'courses-item-checked' : ''}" role="listitem">
        <label class="courses-item-label" for="check-${item.id}">
            <input type="checkbox" class="courses-checkbox form-check-input" id="check-${item.id}"
                ${item.checked ? 'checked' : ''}
                onchange="toggleItem('${item.id}', this.checked)"
                aria-label="Cocher ${escapeHtml(item.name)}">
            <span class="courses-item-text">
                <span class="courses-item-name">${escapeHtml(item.name)}</span>
                <span class="courses-item-detail">${item.qty} ${escapeHtml(item.unit)}</span>
            </span>
        </label>
        <button class="courses-delete-btn" onclick="deleteItem('${item.id}')"
            aria-label="Supprimer ${escapeHtml(item.name)}" title="Supprimer">
            <i class="bi bi-x-lg"></i>
        </button>
    </li>`;
}

/* ---------- Actions sur les articles ---------- */

function toggleItem(id, isChecked) {
    const item = courseItems.find(i => i.id === id);
    if (!item) return;
    item.checked = isChecked;
    saveCourses();
    renderList();
}

function deleteItem(id) {
    courseItems = courseItems.filter(i => i.id !== id);
    saveCourses();
    renderList();
}

/* ---------- Modale : vider la liste ---------- */

function openClearModal(target) {
    clearTarget = target;
    const isAll = target === 'all';
    document.getElementById('clearModalTitle').textContent =
        isAll ? 'Vider la liste' : 'Supprimer les articles cochés';
    document.getElementById('clearModalBody').textContent =
        isAll
            ? 'Voulez-vous vraiment supprimer tous les articles de votre liste ?'
            : 'Voulez-vous supprimer les articles cochés ? Les articles non cochés seront conservés.';
    new bootstrap.Modal(document.getElementById('clearModal')).show();
}

document.getElementById('confirmClearBtn').addEventListener('click', function () {
    courseItems = clearTarget === 'all' ? [] : courseItems.filter(i => !i.checked);
    saveCourses();
    renderList();
    bootstrap.Modal.getInstance(document.getElementById('clearModal')).hide();
    showToast(clearTarget === 'all' ? 'Liste vidée.' : 'Articles cochés supprimés.');
});

/* ---------- Transfert vers le stock ---------- */

function transferToStock() {
    const toTransfer = courseItems.filter(i => i.checked);
    if (!toTransfer.length) return;
    document.getElementById('transferCount').textContent = toTransfer.length;
    new bootstrap.Modal(document.getElementById('transferModal')).show();
}

document.getElementById('confirmTransferBtn').addEventListener('click', function () {
    const toTransfer = courseItems.filter(i => i.checked);
    let stock = loadStock();

    toTransfer.forEach(item => {
        const existing = stock.find(s => s.name.toLowerCase() === item.name.toLowerCase());
        if (existing && existing.unit === item.unit) {
            existing.qty = parseFloat(existing.qty) + parseFloat(item.qty);
        } else {
            stock.unshift({
                id:        generateId(),
                name:      item.name,
                qty:       item.qty,
                unit:      item.unit,
                threshold: 0,
                expiry:    null
            });
        }
    });

    saveStock(stock);
    courseItems = courseItems.filter(i => !i.checked);
    saveCourses();
    renderList();
    bootstrap.Modal.getInstance(document.getElementById('transferModal')).hide();
    showToast(`${toTransfer.length} article(s) ajouté(s) au stock. ✓`);
});

/* ---------- Init ---------- */

updateSuggestions();
renderList();
