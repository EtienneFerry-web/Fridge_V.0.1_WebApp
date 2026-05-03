import React from 'react';
import { PlanningProvider } from '../contexts/PlanningContext.js';
import PlanningLayout from '../components/templates/PlanningLayout.js';
import PlanningTable from '../components/organisms/PlanningTable.js';
import AddRecipeModal from '../components/organisms/AddRecipeModal.js';
export default function PlanningPage({
  initialGrid,
  initialLikes,
  initialFavoris
}) {
  // Parser les JSON passés depuis Twig
  const grid = typeof initialGrid === 'string' ? JSON.parse(initialGrid) : initialGrid;
  const likes = typeof initialLikes === 'string' ? JSON.parse(initialLikes) : initialLikes;
  const favoris = typeof initialFavoris === 'string' ? JSON.parse(initialFavoris) : initialFavoris;
  return /*#__PURE__*/React.createElement(PlanningProvider, {
    initialGrid: grid
  }, /*#__PURE__*/React.createElement(PlanningLayout, null, /*#__PURE__*/React.createElement(PlanningTable, null), /*#__PURE__*/React.createElement(AddRecipeModal, {
    likes: likes,
    favoris: favoris
  })));
}