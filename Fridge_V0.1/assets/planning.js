/* ============================================================
   PLANNING — logique JS de la page Mon Planning
   ============================================================ */

/* ---------- Données mock ---------- */

window.mockRecipes = [
    { id: 1, title: "Bowl de Quinoa Rose",     img: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150",  type: "likes",   category: "Déjeuner"       },
    { id: 2, title: "Salade Méditerranéenne",  img: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=150", type: "likes",   category: "Déjeuner"       },
    { id: 3, title: "Pancakes Banane",         img: "https://images.unsplash.com/photo-1567620905732-2d1ec7bb7445?w=150", type: "favoris", category: "Petit-Déjeuner" },
    { id: 4, title: "Pizza Veggie",            img: "https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=150", type: "favoris", category: "Dîner"          },
    { id: 5, title: "Porridge aux fruits",     img: "https://images.unsplash.com/photo-1517673132405-a56a62b18caf?w=150", type: "likes",   category: "Petit-Déjeuner" },
    { id: 6, title: "Curry de Pois Chiches",   img: "https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=150", type: "favoris", category: "Dîner"          },
    { id: 7, title: "Muffins Myrtilles",       img: "https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=150", type: "likes",   category: "Collation"      },
];

window.currentCellTarget = null;
window.currentTab        = 'likes';

/* ---------- Modale ---------- */

window.openModal = function (cellElement) {
    const modal   = document.getElementById('recipeModal');
    const overlay = document.getElementById('modalOverlay');
    if (!modal || !overlay) return;
    window.currentCellTarget = cellElement.closest('.meal-cell');
    modal.classList.add('show');
    overlay.classList.add('show');
    applyFilters();
};

window.closeModal = function () {
    const modal   = document.getElementById('recipeModal');
    const overlay = document.getElementById('modalOverlay');
    if (modal)   modal.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    window.currentCellTarget = null;
};

window.switchTab = function (tabName) {
    window.currentTab = tabName;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    applyFilters();
};

window.selectRecipe = function (recipe) {
    if (!window.currentCellTarget) return;
    window.currentCellTarget.innerHTML = `
        <div class="selected-recipe">
            <img src="${recipe.img}" alt="${recipe.title}">
            <span class="text-truncate w-100 px-1" title="${recipe.title}">${recipe.title}</span>
        </div>`;
    window.currentCellTarget.onclick = function () { openModal(this); };
    closeModal();
};

/* ---------- Filtres de la modale ---------- */

/*
 * Les ids ont été renommés planningSearchInput / planningSortSelect
 * dans le template pour éviter tout conflit avec les pages stock et courses
 * qui utilisaient elles aussi searchInput / sortSelect.
 */
window.applyFilters = function () {
    const searchInput = document.getElementById('planningSearchInput');
    const sortSelect  = document.getElementById('planningSortSelect');
    const grid        = document.getElementById('recipeGrid');
    if (!grid) return;

    const searchQuery = searchInput ? searchInput.value.toLowerCase() : '';
    const sortOption  = sortSelect  ? sortSelect.value                : 'az';

    let filtered = window.mockRecipes.filter(recipe =>
        recipe.type === window.currentTab &&
        recipe.title.toLowerCase().includes(searchQuery)
    );

    filtered.sort((a, b) => {
        if (sortOption === 'az')  return a.title.localeCompare(b.title);
        if (sortOption === 'za')  return b.title.localeCompare(a.title);
        if (sortOption === 'cat') return a.category.localeCompare(b.category);
        return 0;
    });

    grid.innerHTML = '';

    if (filtered.length === 0) {
        grid.innerHTML = `<p class="text-muted text-center w-100 mt-3" style="grid-column: 1 / -1;">Aucune recette trouvée.</p>`;
        return;
    }

    filtered.forEach(recipe => {
        const card = document.createElement('div');
        card.className = 'modal-recipe-card shadow-sm';
        card.onclick   = () => selectRecipe(recipe);
        card.innerHTML = `
            <img src="${recipe.img}" alt="${recipe.title}">
            <span class="text-truncate" title="${recipe.title}">${recipe.title}</span>
            <small>${recipe.category}</small>`;
        grid.appendChild(card);
    });
};

/* ---------- Génération du tableau ---------- */

window.clearPlanning = function () {
    if (confirm('Voulez-vous vraiment vider tout le planning ?')) {
        document.querySelectorAll('.meal-cell').forEach(cell => {
            cell.innerHTML = `<div class="btn-add" onclick="openModal(this)"><i class="bi bi-plus-lg"></i></div>`;
            cell.onclick = null;
        });
    }
};

function initPlanningTable() {
    const tbody = document.getElementById('planningBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    const categories = ['Petit-Déjeuner', 'Déjeuner', 'Dîner', 'Dessert / Collation'];

    categories.forEach(cat => {
        /* Ligne titre de catégorie */
        const trCat = document.createElement('tr');
        trCat.className = 'category-row';
        trCat.innerHTML = `<td colspan="7">${cat}</td>`;
        tbody.appendChild(trCat);

        /* Ligne des 7 cellules repas */
        const trDays = document.createElement('tr');
        for (let i = 0; i < 7; i++) {
            const td = document.createElement('td');
            td.className = 'meal-cell';
            td.innerHTML = `<div class="btn-add" onclick="openModal(this)"><i class="bi bi-plus-lg"></i></div>`;
            trDays.appendChild(td);
        }
        tbody.appendChild(trDays);
    });
}

/* ---------- Init ---------- */

document.addEventListener('DOMContentLoaded', initPlanningTable);
