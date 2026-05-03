import React, { createContext, useContext, useReducer, useCallback } from 'react';
import { usePlanningAPI } from '../hooks/usePlanningAPI';
import { useNativeDragAndDrop } from '../hooks/useNativeDragAndDrop';

const PlanningContext = createContext();

const planningReducer = (state, action) => {
    switch (action.type) {
        case 'SET_GRID':
            return { ...state, grid: action.payload };
        case 'ADD_RECIPE':
            const { jour: addJour, moment: addMoment, planningItem } = action.payload;
            return {
                ...state,
                grid: {
                    ...state.grid,
                    [addJour]: {
                        ...state.grid[addJour],
                        [addMoment]: planningItem
                    }
                }
            };
        case 'REMOVE_RECIPE':
            const { jour: rmJour, moment: rmMoment } = action.payload;
            return {
                ...state,
                grid: {
                    ...state.grid,
                    [rmJour]: {
                        ...state.grid[rmJour],
                        [rmMoment]: null
                    }
                }
            };
        case 'MOVE_RECIPE':
            const { sourceJour, sourceMoment, destJour, destMoment, item } = action.payload;
            return {
                ...state,
                grid: {
                    ...state.grid,
                    [sourceJour]: {
                        ...state.grid[sourceJour],
                        [sourceMoment]: null
                    },
                    [destJour]: {
                        ...state.grid[destJour],
                        [destMoment]: item
                    }
                }
            };
        case 'OPEN_MODAL':
            return {
                ...state,
                modalState: {
                    isOpen: true,
                    targetJour: action.payload.jour,
                    targetMoment: action.payload.moment,
                    targetLabel: action.payload.label
                }
            };
        case 'CLOSE_MODAL':
            return {
                ...state,
                modalState: { ...state.modalState, isOpen: false }
            };
        default:
            return state;
    }
};

export const PlanningProvider = ({ children, initialGrid }) => {
    const [state, dispatch] = useReducer(planningReducer, { 
        grid: initialGrid || {},
        modalState: { isOpen: false, targetJour: null, targetMoment: null, targetLabel: null }
    });
    
    const api = usePlanningAPI();
    const dnd = useNativeDragAndDrop();

    const handleAddRecipe = useCallback(async (recetteId, recetteObj) => {
        const { targetJour, targetMoment } = state.modalState;
        if (!targetJour || !targetMoment) return;

        try {
            const data = await api.addRecipe(targetJour, targetMoment, recetteId);
            if (data.success) {
                // Créer l'objet planningItem à partir des data retournées
                const newItem = {
                    id: data.id,
                    planningRecette: {
                        id: recetteId,
                        recetteLibelle: data.titre,
                        recettePhoto: data.photo
                    }
                };
                dispatch({ 
                    type: 'ADD_RECIPE', 
                    payload: { jour: targetJour, moment: targetMoment, planningItem: newItem } 
                });
                dispatch({ type: 'CLOSE_MODAL' });
            }
        } catch (e) {
            console.error('Add Error', e);
        }
    }, [api, state.modalState]);

    const handleRemoveRecipe = useCallback(async (planningId, jour, moment) => {
        try {
            const data = await api.deleteRecipe(planningId);
            if (data.success) {
                dispatch({ type: 'REMOVE_RECIPE', payload: { jour, moment } });
            }
        } catch (e) {
            console.error('Delete Error', e);
        }
    }, [api]);

    const handleMoveRecipe = useCallback(async (planningId, sourceJour, sourceMoment, destJour, destMoment, item) => {
        // Optimistic update
        dispatch({ 
            type: 'MOVE_RECIPE', 
            payload: { sourceJour, sourceMoment, destJour, destMoment, item } 
        });

        try {
            const data = await api.moveRecipe(planningId, destJour, destMoment);
            if (!data.success) {
                // Revert
                dispatch({ 
                    type: 'MOVE_RECIPE', 
                    payload: { 
                        sourceJour: destJour, sourceMoment: destMoment, 
                        destJour: sourceJour, destMoment: sourceMoment, item 
                    } 
                });
            }
        } catch (e) {
            console.error('Move Error', e);
            // Revert
            dispatch({ 
                type: 'MOVE_RECIPE', 
                payload: { 
                    sourceJour: destJour, sourceMoment: destMoment, 
                    destJour: sourceJour, destMoment: sourceMoment, item 
                } 
            });
        }
    }, [api]);

    const handleDropFromModal = useCallback(async (recetteId, destJour, destMoment) => {
        try {
            const data = await api.addRecipe(destJour, destMoment, recetteId);
            if (data.success) {
                const newItem = {
                    id: data.id,
                    planningRecette: {
                        id: recetteId,
                        recetteLibelle: data.titre,
                        recettePhoto: data.photo
                    }
                };
                dispatch({ 
                    type: 'ADD_RECIPE', 
                    payload: { jour: destJour, moment: destMoment, planningItem: newItem } 
                });
                dispatch({ type: 'CLOSE_MODAL' });
            }
        } catch (e) {
            console.error('Drop from modal error', e);
        }
    }, [api]);

    return (
        <PlanningContext.Provider value={{ 
            state, 
            dispatch, 
            handleAddRecipe, 
            handleRemoveRecipe, 
            handleMoveRecipe,
            handleDropFromModal,
            dnd 
        }}>
            {children}
        </PlanningContext.Provider>
    );
};

export const usePlanning = () => useContext(PlanningContext);
