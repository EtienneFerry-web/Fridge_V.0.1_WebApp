import React from 'react';
import FilterSelect from '../molecules/FilterSelect';
import Icon from '../atoms/Icon';

export default function FilterBar({ filters }) {
    const { search, setSearch, regime, setRegime, tempsMax, setTempsMax, filteredRecipes } = filters;

    const regimes = [
        { value: 'Omnivore', label: 'Omnivore' },
        { value: 'Végétarien', label: 'Végétarien' },
        { value: 'Vegan', label: 'Vegan' },
        { value: 'Sans gluten', label: 'Sans gluten' },
        { value: 'Sans lactose', label: 'Sans lactose' }
    ];

    const temps = [
        { value: 15, label: '≤ 15 min' },
        { value: 30, label: '≤ 30 min' },
        { value: 60, label: '≤ 1 h' },
        { value: 120, label: '≤ 2 h' }
    ];

    return (
        <div className="planning-filters mb-3 p-2 bg-light rounded">
            <div className="row g-2 align-items-center">
                <div className="col-12 col-md-5">
                    <div className="input-group input-group-sm">
                        <span className="input-group-text bg-white border-end-0">
                            <Icon name="search" />
                        </span>
                        <input 
                            type="text"
                            className="form-control border-start-0"
                            placeholder="Rechercher une recette..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                        />
                    </div>
                </div>
                <div className="col-12 col-md-4">
                    <FilterSelect 
                        id="planningFilterRegime"
                        value={regime}
                        onChange={setRegime}
                        options={regimes}
                        defaultOption={{ value: 'all', label: 'Tous les régimes' }}
                    />
                </div>
                <div className="col-12 col-md-3">
                    <FilterSelect 
                        id="planningFilterTemps"
                        value={tempsMax}
                        onChange={setTempsMax}
                        options={temps}
                        defaultOption={{ value: 0, label: 'Tous les temps' }}
                    />
                </div>
            </div>
            <div className="mt-2 text-muted small">
                {filteredRecipes.length} recette{filteredRecipes.length > 1 ? 's' : ''} trouvée{filteredRecipes.length > 1 ? 's' : ''}
            </div>
        </div>
    );
}
