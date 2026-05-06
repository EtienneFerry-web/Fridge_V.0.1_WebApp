import React, { useState } from 'react';
import { usePlanning } from '../../contexts/PlanningContext.js';
import Avatar from '../atoms/Avatar.js';
import Icon from '../atoms/Icon.js';
export default function MealCell({
  day,
  moment,
  meal,
  label
}) {
  const {
    dispatch,
    handleRemoveRecipe,
    handleMoveRecipe,
    handleDropFromModal,
    dnd
  } = usePlanning();
  const [isOver, setIsOver] = useState(false);
  const onDragStart = e => {
    dnd.handleDragStart(meal, {
      day,
      moment
    });
    e.dataTransfer.effectAllowed = 'move';
    setTimeout(() => e.target.classList.add('drag-chosen'), 0);
  };
  const onDragEnd = e => {
    e.target.classList.remove('drag-chosen');
    dnd.handleDragEnd();
  };
  const onDragOver = e => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };
  const onDragEnter = e => {
    e.preventDefault();
    if ((dnd.draggedItem || dnd.isDraggingModalItem) && (dnd.draggedSource?.day !== day || dnd.draggedSource?.moment !== moment)) {
      setIsOver(true);
    }
  };
  const onDragLeave = e => {
    if (!e.currentTarget.contains(e.relatedTarget)) {
      setIsOver(false);
    }
  };
  const onDrop = e => {
    e.preventDefault();
    setIsOver(false);
    if (dnd.isDraggingModalItem && dnd.draggedItem) {
      // Drop from modal
      handleDropFromModal(dnd.draggedItem.id, day, moment);
      return;
    }
    if (dnd.draggedItem && dnd.draggedSource) {
      if (dnd.draggedSource.day === day && dnd.draggedSource.moment === moment) return;
      handleMoveRecipe(dnd.draggedItem.id, dnd.draggedSource.day, dnd.draggedSource.moment, day, moment, dnd.draggedItem);
    }
  };
  const openAddModal = () => {
    dispatch({
      type: 'OPEN_MODAL',
      payload: {
        jour: day,
        moment: moment,
        label: label
      }
    });
  };
  return /*#__PURE__*/React.createElement("td", {
    className: `meal-cell p-1 ${isOver ? 'sortable-over' : ''}`,
    style: {
      minWidth: '110px',
      height: '90px'
    },
    onDragOver: onDragOver,
    onDragEnter: onDragEnter,
    onDragLeave: onDragLeave,
    onDrop: onDrop
  }, meal && meal.planningRecette ? React.createElement("div", {
    className: "d-flex flex-column align-items-center gap-1 h-100 justify-content-center position-relative planning-drag-item",
    draggable: "true",
    onDragStart: onDragStart,
    onDragEnd: onDragEnd
  }, React.createElement(Avatar, {
    src: meal.planningRecette.recettePhoto.startsWith('http') || meal.planningRecette.recettePhoto.startsWith('//') ? meal.planningRecette.recettePhoto : '/uploads/recettes/' + meal.planningRecette.recettePhoto,
    alt: meal.planningRecette.recetteLibelle,
    size: 50
  }), React.createElement("span", {
    className: "fw-bold text-dark text-center",
    style: {
      fontSize: '0.75rem',
      lineHeight: '1.1',
      maxWidth: '90px'
    }
  }, meal.planningRecette.recetteLibelle.length > 25 ? meal.planningRecette.recetteLibelle.substring(0, 25) + '…' : meal.planningRecette.recetteLibelle), React.createElement("button", {
    className: "btn btn-sm p-0 text-danger position-absolute top-0 end-0 mt-1 me-1",
    onClick: () => handleRemoveRecipe(meal.id, day, moment),
    title: "Retirer"
  }, React.createElement(Icon, {
    name: "x-circle-fill"
  }))) : React.createElement("button", {
    className: "btn-add w-100 h-100 border-0 bg-transparent d-flex align-items-center justify-content-center transition-normal",
    onClick: openAddModal,
    title: "Ajouter une recette"
  }, React.createElement("div", {
    className: "rounded-circle border border-2 border-dashed d-flex align-items-center justify-content-center",
    style: {
      width: '40px',
      height: '40px',
      borderColor: 'rgba(20, 21, 14, 0.15) !important'
    }
  }, React.createElement(Icon, {
    name: "plus-lg",
    className: "text-muted"
  }))));
}