import { useState, useMemo } from 'react';
export function useRecipeFilters(initialRecipes) {
  const [search, setSearch] = useState('');
  const [regime, setRegime] = useState('all');
  const [tempsMax, setTempsMax] = useState(0);
  const filteredRecipes = useMemo(() => {
    return initialRecipes.filter(recipe => {
      const temps = (recipe.recetteTempsPrepa || 0) + (recipe.recetteTempsCuisson || 0);
      const regimes = recipe.regimes ? recipe.regimes.map(r => r.regimeLibelle) : [];
      const libelle = recipe.recetteLibelle ? recipe.recetteLibelle.toLowerCase() : '';
      let match = true;
      if (search && !libelle.includes(search.toLowerCase().trim())) {
        match = false;
      }
      if (regime !== 'all' && !regimes.includes(regime)) {
        match = false;
      }
      if (tempsMax > 0 && temps > tempsMax) {
        match = false;
      }
      return match;
    });
  }, [initialRecipes, search, regime, tempsMax]);
  const resetFilters = () => {
    setSearch('');
    setRegime('all');
    setTempsMax(0);
  };
  return {
    search,
    setSearch,
    regime,
    setRegime,
    tempsMax,
    setTempsMax,
    filteredRecipes,
    resetFilters
  };
}