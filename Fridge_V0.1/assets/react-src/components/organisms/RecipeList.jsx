import React from 'react';
import RecipeCard from '../molecules/RecipeCard.js';

export default function RecipeList({ recipes, emptyMessage, onRecipeClick }) {
    if (!recipes || recipes.length === 0) {
        return <p className="text-muted text-center py-3">{emptyMessage}</p>;
    }

    return (
        <div className="row row-cols-2 row-cols-md-3 g-3">
            {recipes.map(recipe => (
                <RecipeCard 
                    key={recipe.id} 
                    recipe={recipe} 
                    onClick={onRecipeClick} 
                />
            ))}
        </div>
    );
}
