import React from 'react';
import { PlanningProvider } from '../contexts/PlanningContext';
import PlanningLayout from '../components/templates/PlanningLayout';
import PlanningTable from '../components/organisms/PlanningTable';
import AddRecipeModal from '../components/organisms/AddRecipeModal';

export default function PlanningPage({ initialGrid, initialLikes, initialFavoris }) {
    // Parser les JSON passés depuis Twig
    const grid = typeof initialGrid === 'string' ? JSON.parse(initialGrid) : initialGrid;
    const likes = typeof initialLikes === 'string' ? JSON.parse(initialLikes) : initialLikes;
    const favoris = typeof initialFavoris === 'string' ? JSON.parse(initialFavoris) : initialFavoris;

    return (
        <PlanningProvider initialGrid={grid}>
            <PlanningLayout>
                <PlanningTable />
                <AddRecipeModal likes={likes} favoris={favoris} />
            </PlanningLayout>
        </PlanningProvider>
    );
}
