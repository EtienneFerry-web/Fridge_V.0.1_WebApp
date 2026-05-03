import React, { useState, useEffect } from 'react';
import { usePlanning } from '../../contexts/PlanningContext';
import { useRecipeFilters } from '../../hooks/useRecipeFilters';
import FilterBar from './FilterBar';
import RecipeList from './RecipeList';
import Icon from '../atoms/Icon';

export default function AddRecipeModal({ likes, favoris }) {
    const { state, dispatch, handleAddRecipe } = usePlanning();
    const { isOpen, targetJour, targetLabel } = state.modalState;
    
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

    const closeModal = () => dispatch({ type: 'CLOSE_MODAL' });

    const handleBackdropClick = (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal();
        }
    };

    const currentFilters = activeTab === 'likes' ? likesFilters : favorisFilters;

    return (
        <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }} onClick={handleBackdropClick}>
            <div className="modal-dialog modal-dialog-centered modal-lg">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">
                            <Icon name="plus-circle" className="me-2 text-primary" />
                            Ajouter une recette — <span>{targetLabel} — {targetJour}</span>
                        </h5>
                        <button type="button" className="btn-close" onClick={closeModal}></button>
                    </div>
                    <div className="modal-body">
                        <ul className="nav nav-pills mb-3" id="planningTabs">
                            <li className="nav-item">
                                <button 
                                    className={`nav-link ${activeTab === 'likes' ? 'active' : ''}`} 
                                    onClick={() => setActiveTab('likes')}
                                >
                                    <Icon name="heart" className="me-1" />Mes Likes
                                </button>
                            </li>
                            <li className="nav-item">
                                <button 
                                    className={`nav-link ${activeTab === 'favoris' ? 'active' : ''}`} 
                                    onClick={() => setActiveTab('favoris')}
                                >
                                    <Icon name="bookmark" className="me-1" />Mes Favoris
                                </button>
                            </li>
                        </ul>
                        
                        <FilterBar filters={currentFilters} />

                        {activeTab === 'likes' && (
                            <RecipeList 
                                recipes={likesFilters.filteredRecipes} 
                                emptyMessage="Vous n'avez pas encore liké de recettes." 
                                onRecipeClick={handleAddRecipe}
                            />
                        )}

                        {activeTab === 'favoris' && (
                            <RecipeList 
                                recipes={favorisFilters.filteredRecipes} 
                                emptyMessage="Vous n'avez pas encore de recettes en favoris." 
                                onRecipeClick={handleAddRecipe}
                            />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
