import React from 'react';
import { usePlanning } from '../../contexts/PlanningContext';
import Icon from '../atoms/Icon';

export default function RecipeCard({ recipe, onClick }) {
    const { dnd } = usePlanning();
    const temps = (recipe.recetteTempsPrepa || 0) + (recipe.recetteTempsCuisson || 0);
    const photoUrl = recipe.recettePhoto?.startsWith('http') || recipe.recettePhoto?.startsWith('//') ? recipe.recettePhoto : '/uploads/recettes/' + recipe.recettePhoto;

    const onDragStart = (e) => {
        dnd.handleDragStart(recipe, null, true);
        e.dataTransfer.effectAllowed = 'copy';
    };

    const onDragEnd = (e) => {
        dnd.handleDragEnd();
    };

    return (
        <div className="col planning-recette-item">
            <div 
                className="card h-100 shadow-sm border-0 planning-recette-card"
                onClick={() => onClick(recipe.id, recipe.recetteLibelle)}
                style={{ cursor: 'pointer' }}
                draggable="true"
                onDragStart={onDragStart}
                onDragEnd={onDragEnd}
            >
                <img 
                    src={photoUrl} 
                    className="card-img-top" 
                    style={{ height: '80px', objectFit: 'cover' }} 
                    alt={recipe.recetteLibelle} 
                />
                <div className="card-body p-2 text-center">
                    <p className="small fw-bold mb-1">{recipe.recetteLibelle}</p>
                    <span className="small text-muted">
                        <Icon name="clock" className="me-1" />
                        {temps} min
                    </span>
                </div>
            </div>
        </div>
    );
}
