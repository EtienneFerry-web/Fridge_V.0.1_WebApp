export function usePlanningAPI() {
  const urls = window.FRIDGE_URLS || {};
  const addRecipe = async (jour, moment, recetteId) => {
    const response = await fetch(urls.planningAdd || '/planning/add', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({
        jour,
        moment,
        recette_id: recetteId
      })
    });
    return response.json();
  };
  const moveRecipe = async (planningId, nouveauJour, nouveauMoment) => {
    const response = await fetch(urls.planningMove || '/planning/move', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({
        planning_id: planningId,
        nouveau_jour: nouveauJour,
        nouveau_moment: nouveauMoment
      })
    });
    return response.json();
  };
  const deleteRecipe = async planningId => {
    let strUrl = urls.planningDelete ? urls.planningDelete.replace('__ID__', planningId) : `/planning/delete/${planningId}`;
    const response = await fetch(strUrl, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    return response.json();
  };
  return {
    addRecipe,
    moveRecipe,
    deleteRecipe
  };
}