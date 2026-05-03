import React, { useState } from 'react';
import { usePlanning } from '../../contexts/PlanningContext';
import Avatar from '../atoms/Avatar';
import Icon from '../atoms/Icon';

export default function MealCell({ day, moment, meal, label }) {
    const { 
        dispatch, 
        handleRemoveRecipe, 
        handleMoveRecipe, 
        handleDropFromModal, 
        dnd 
    } = usePlanning();
    
    const [isOver, setIsOver] = useState(false);

    const onDragStart = (e) => {
        dnd.handleDragStart(meal, { day, moment });
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => e.target.classList.add('drag-chosen'), 0);
    };

    const onDragEnd = (e) => {
        e.target.classList.remove('drag-chosen');
        dnd.handleDragEnd();
    };

    const onDragOver = (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    const onDragEnter = (e) => {
        e.preventDefault();
        if ((dnd.draggedItem || dnd.isDraggingModalItem) && 
            (dnd.draggedSource?.day !== day || dnd.draggedSource?.moment !== moment)) {
            setIsOver(true);
        }
    };

    const onDragLeave = (e) => {
        if (!e.currentTarget.contains(e.relatedTarget)) {
            setIsOver(false);
        }
    };

    const onDrop = (e) => {
        e.preventDefault();
        setIsOver(false);

        if (dnd.isDraggingModalItem && dnd.draggedItem) {
            // Drop from modal
            handleDropFromModal(dnd.draggedItem.id, day, moment);
            return;
        }

        if (dnd.draggedItem && dnd.draggedSource) {
            if (dnd.draggedSource.day === day && dnd.draggedSource.moment === moment) return;
            handleMoveRecipe(
                dnd.draggedItem.id, 
                dnd.draggedSource.day, 
                dnd.draggedSource.moment, 
                day, 
                moment, 
                dnd.draggedItem
            );
        }
    };

    const openAddModal = () => {
        dispatch({ type: 'OPEN_MODAL', payload: { jour: day, moment: moment, label: label } });
    };

    return (
        <td 
            className={`meal-cell p-1 ${isOver ? 'sortable-over' : ''}`}
            style={{ minWidth: '110px', height: '90px' }}
            onDragOver={onDragOver}
            onDragEnter={onDragEnter}
            onDragLeave={onDragLeave}
            onDrop={onDrop}
        >
            {meal && meal.planningRecette ? (
                <div 
                    className="d-flex flex-column align-items-center gap-1 h-100 justify-content-center position-relative planning-drag-item"
                    draggable="true"
                    onDragStart={onDragStart}
                    onDragEnd={onDragEnd}
                >
                    <Avatar 
                        src={meal.planningRecette.recettePhoto.startsWith('http') || meal.planningRecette.recettePhoto.startsWith('//') ? meal.planningRecette.recettePhoto : '/uploads/recettes/' + meal.planningRecette.recettePhoto} 
                        alt={meal.planningRecette.recetteLibelle} 
                    />
                    <span className="small fw-bold text-dark" style={{ fontSize: '0.72rem', lineHeight: '1.2' }}>
                        {meal.planningRecette.recetteLibelle.length > 20 ? meal.planningRecette.recetteLibelle.substring(0, 20) + '…' : meal.planningRecette.recetteLibelle}
                    </span>
                    <button 
                        className="btn btn-sm p-0 text-danger" 
                        style={{ fontSize: '0.75rem' }} 
                        onClick={() => handleRemoveRecipe(meal.id, day, moment)}
                        title="Retirer"
                    >
                        <Icon name="x-circle" />
                    </button>
                </div>
            ) : (
                <button 
                    className="btn-add w-100 h-100 border-0 bg-transparent"
                    onClick={openAddModal}
                    title="Ajouter une recette"
                >
                    <Icon name="plus-lg" className="text-muted" />
                </button>
            )}
        </td>
    );
}
