import React from 'react';
import { usePlanning } from '../../contexts/PlanningContext';
import MealCell from '../molecules/MealCell';

export default function PlanningTable() {
    const { state } = usePlanning();
    
    // Si on veut être dynamique, on peut les passer en props ou du contexte, 
    // mais gardons l'exemple avec les mêmes clés que Twig
    const days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    const moments = { 
        'petit_dejeuner': 'Petit-déjeuner',
        'dejeuner': 'Déjeuner', 
        'diner': 'Dîner',
        'dessert': 'Dessert'
    };

    return (
        <div className="table-responsive">
            <table className="table table-bordered align-middle text-center table-planning">
                <thead>
                    <tr>
                        <th style={{ width: '130px' }}></th>
                        {days.map(day => (
                            <th key={day} className="text-capitalize">{day}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {Object.entries(moments).map(([momentKey, momentLabel]) => (
                        <tr key={momentKey}>
                            <td className="fw-bold text-start ps-3 category-row">{momentLabel}</td>
                            {days.map(day => (
                                <MealCell 
                                    key={`${day}-${momentKey}`} 
                                    day={day} 
                                    moment={momentKey} 
                                    label={momentLabel}
                                    meal={state.grid[day]?.[momentKey]} 
                                />
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
