import React from 'react';
import { usePlanning } from '../../contexts/PlanningContext.js';
import Icon from '../atoms/Icon.js';
export default function RecipeCard({
  recipe,
  onClick
}) {
  const {
    dnd
  } = usePlanning();
  const temps = (recipe.recetteTempsPrepa || 0) + (recipe.recetteTempsCuisson || 0);
  const photoUrl = recipe.recettePhoto?.startsWith('http') || recipe.recettePhoto?.startsWith('//') ? recipe.recettePhoto : '/uploads/recettes/' + recipe.recettePhoto;
  const onDragStart = e => {
    dnd.handleDragStart(recipe, null, true);
    e.dataTransfer.effectAllowed = 'copy';
  };
  const onDragEnd = e => {
    dnd.handleDragEnd();
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "col planning-recette-item"
  }, /*#__PURE__*/React.createElement("div", {
    className: "card h-100 shadow-sm border-0 planning-recette-card",
    onClick: () => onClick(recipe.id, recipe.recetteLibelle),
    style: {
      cursor: 'pointer'
    },
    draggable: "true",
    onDragStart: onDragStart,
    onDragEnd: onDragEnd
  }, /*#__PURE__*/React.createElement("img", {
    src: photoUrl,
    className: "card-img-top",
    style: {
      height: '80px',
      objectFit: 'cover'
    },
    alt: recipe.recetteLibelle
  }), /*#__PURE__*/React.createElement("div", {
    className: "card-body p-2 text-center"
  }, /*#__PURE__*/React.createElement("p", {
    className: "small fw-bold mb-1"
  }, recipe.recetteLibelle), /*#__PURE__*/React.createElement("span", {
    className: "small text-muted"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "clock",
    className: "me-1"
  }), temps, " min"))));
}