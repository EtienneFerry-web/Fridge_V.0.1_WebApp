import React from 'react';
import { usePlanning } from '../../contexts/PlanningContext.js';
import MealCell from '../molecules/MealCell.js';
export default function PlanningTable() {
  const {
    state
  } = usePlanning();

  // Si on veut être dynamique, on peut les passer en props ou du contexte, 
  // mais gardons l'exemple avec les mêmes clés que Twig
  const days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
  const moments = {
    'petit_dejeuner': 'Petit-déjeuner',
    'dejeuner': 'Déjeuner',
    'diner': 'Dîner',
    'dessert': 'Dessert'
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "table-responsive"
  }, /*#__PURE__*/React.createElement("table", {
    className: "table table-bordered align-middle text-center table-planning"
  }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", {
    style: {
      width: '130px'
    }
  }), days.map(day => /*#__PURE__*/React.createElement("th", {
    key: day,
    className: "text-capitalize"
  }, day)))), /*#__PURE__*/React.createElement("tbody", null, Object.entries(moments).map(([momentKey, momentLabel]) => /*#__PURE__*/React.createElement("tr", {
    key: momentKey
  }, /*#__PURE__*/React.createElement("td", {
    className: "fw-bold text-start ps-3 category-row"
  }, momentLabel), days.map(day => /*#__PURE__*/React.createElement(MealCell, {
    key: `${day}-${momentKey}`,
    day: day,
    moment: momentKey,
    label: momentLabel,
    meal: state.grid[day]?.[momentKey]
  })))))));
}