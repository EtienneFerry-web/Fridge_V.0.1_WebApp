import React from 'react';
import PlanningHero from '../molecules/PlanningHero.js';

export default function PlanningLayout({
  children
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(PlanningHero, {
    title: "Mon Planning Gourmand",
    subtitle: "Organisez vos repas de la semaine avec des ingrédients frais et locaux."
  }), /*#__PURE__*/React.createElement("main", {
    className: "container mb-5"
  }, children));
}