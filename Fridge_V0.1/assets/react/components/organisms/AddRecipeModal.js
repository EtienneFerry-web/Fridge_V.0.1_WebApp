import React, { useState, useEffect } from 'react';
import { usePlanning } from '../../contexts/PlanningContext.js';
import { useRecipeFilters } from '../../hooks/useRecipeFilters.js';
import FilterBar from './FilterBar.js';
import RecipeList from './RecipeList.js';
import Icon from '../atoms/Icon.js';
export default function AddRecipeModal({
  likes,
  favoris
}) {
  const {
    state,
    dispatch,
    handleAddRecipe
  } = usePlanning();
  const {
    isOpen,
    targetJour,
    targetLabel
  } = state.modalState;
  const [activeTab, setActiveTab] = useState('likes');

  // On initialise deux hooks de filtres, un pour chaque liste
  const likesFilters = useRecipeFilters(likes);
  const favorisFilters = useRecipeFilters(favoris);

  // Activer/Désactiver le scroll du body comme le fait Bootstrap Modal
  useEffect(() => {
    if (isOpen) {
      document.body.classList.add('modal-open');
      document.body.style.overflow = 'hidden';
      // Reset filters on open
      likesFilters.resetFilters();
      favorisFilters.resetFilters();
    } else {
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    }
    return () => {
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    };
  }, [isOpen]);
  if (!isOpen) return null;
  const closeModal = () => dispatch({
    type: 'CLOSE_MODAL'
  });
  const handleBackdropClick = e => {
    if (e.target.classList.contains('modal')) {
      closeModal();
    }
  };
  const currentFilters = activeTab === 'likes' ? likesFilters : favorisFilters;
  return /*#__PURE__*/React.createElement("div", {
    className: "modal fade show d-block",
    style: {
      backgroundColor: 'rgba(0,0,0,0.5)'
    },
    onClick: handleBackdropClick
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-dialog modal-dialog-centered modal-lg"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-header"
  }, /*#__PURE__*/React.createElement("h5", {
    className: "modal-title"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "plus-circle",
    className: "me-2 text-primary"
  }), "Ajouter une recette \u2014 ", /*#__PURE__*/React.createElement("span", null, targetLabel, " \u2014 ", targetJour)), /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "btn-close",
    onClick: closeModal
  })), /*#__PURE__*/React.createElement("div", {
    className: "modal-body"
  }, /*#__PURE__*/React.createElement("ul", {
    className: "nav nav-pills mb-3",
    id: "planningTabs"
  }, /*#__PURE__*/React.createElement("li", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement("button", {
    className: `nav-link ${activeTab === 'likes' ? 'active' : ''}`,
    onClick: () => setActiveTab('likes')
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "heart",
    className: "me-1"
  }), "Mes Likes")), /*#__PURE__*/React.createElement("li", {
    className: "nav-item"
  }, /*#__PURE__*/React.createElement("button", {
    className: `nav-link ${activeTab === 'favoris' ? 'active' : ''}`,
    onClick: () => setActiveTab('favoris')
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "bookmark",
    className: "me-1"
  }), "Mes Favoris"))), /*#__PURE__*/React.createElement(FilterBar, {
    filters: currentFilters
  }), activeTab === 'likes' && /*#__PURE__*/React.createElement(RecipeList, {
    recipes: likesFilters.filteredRecipes,
    emptyMessage: "Vous n'avez pas encore lik\xE9 de recettes.",
    onRecipeClick: handleAddRecipe
  }), activeTab === 'favoris' && /*#__PURE__*/React.createElement(RecipeList, {
    recipes: favorisFilters.filteredRecipes,
    emptyMessage: "Vous n'avez pas encore de recettes en favoris.",
    onRecipeClick: handleAddRecipe
  })))));
}