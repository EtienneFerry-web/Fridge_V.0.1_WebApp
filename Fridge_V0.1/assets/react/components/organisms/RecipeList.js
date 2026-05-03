import React from 'react';
import RecipeCard from '../molecules/RecipeCard.js';
export default function RecipeList({
  recipes,
  emptyMessage,
  onRecipeClick
}) {
  if (!recipes || recipes.length === 0) {
    return /*#__PURE__*/React.createElement("p", {
      className: "text-muted text-center py-3"
    }, emptyMessage);
  }
  return /*#__PURE__*/React.createElement("div", {
    className: "row row-cols-2 row-cols-md-3 g-3"
  }, recipes.map(recipe => /*#__PURE__*/React.createElement(RecipeCard, {
    key: recipe.id,
    recipe: recipe,
    onClick: onRecipeClick
  })));
}