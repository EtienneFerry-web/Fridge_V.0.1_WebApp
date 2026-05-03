import React from 'react';
import FilterSelect from '../molecules/FilterSelect.js';
import Icon from '../atoms/Icon.js';
export default function FilterBar({
  filters
}) {
  const {
    search,
    setSearch,
    regime,
    setRegime,
    tempsMax,
    setTempsMax,
    filteredRecipes
  } = filters;
  const regimes = [{
    value: 'Omnivore',
    label: 'Omnivore'
  }, {
    value: 'Végétarien',
    label: 'Végétarien'
  }, {
    value: 'Vegan',
    label: 'Vegan'
  }, {
    value: 'Sans gluten',
    label: 'Sans gluten'
  }, {
    value: 'Sans lactose',
    label: 'Sans lactose'
  }];
  const temps = [{
    value: 15,
    label: '≤ 15 min'
  }, {
    value: 30,
    label: '≤ 30 min'
  }, {
    value: 60,
    label: '≤ 1 h'
  }, {
    value: 120,
    label: '≤ 2 h'
  }];
  return /*#__PURE__*/React.createElement("div", {
    className: "planning-filters mb-3 p-2 bg-light rounded"
  }, /*#__PURE__*/React.createElement("div", {
    className: "row g-2 align-items-center"
  }, /*#__PURE__*/React.createElement("div", {
    className: "col-12 col-md-5"
  }, /*#__PURE__*/React.createElement("div", {
    className: "input-group input-group-sm"
  }, /*#__PURE__*/React.createElement("span", {
    className: "input-group-text bg-white border-end-0"
  }, /*#__PURE__*/React.createElement(Icon, {
    name: "search"
  })), /*#__PURE__*/React.createElement("input", {
    type: "text",
    className: "form-control border-start-0",
    placeholder: "Rechercher une recette...",
    value: search,
    onChange: e => setSearch(e.target.value)
  }))), /*#__PURE__*/React.createElement("div", {
    className: "col-12 col-md-4"
  }, /*#__PURE__*/React.createElement(FilterSelect, {
    id: "planningFilterRegime",
    value: regime,
    onChange: setRegime,
    options: regimes,
    defaultOption: {
      value: 'all',
      label: 'Tous les régimes'
    }
  })), /*#__PURE__*/React.createElement("div", {
    className: "col-12 col-md-3"
  }, /*#__PURE__*/React.createElement(FilterSelect, {
    id: "planningFilterTemps",
    value: tempsMax,
    onChange: setTempsMax,
    options: temps,
    defaultOption: {
      value: 0,
      label: 'Tous les temps'
    }
  }))), /*#__PURE__*/React.createElement("div", {
    className: "mt-2 text-muted small"
  }, filteredRecipes.length, " recette", filteredRecipes.length > 1 ? 's' : '', " trouv\xE9e", filteredRecipes.length > 1 ? 's' : ''));
}