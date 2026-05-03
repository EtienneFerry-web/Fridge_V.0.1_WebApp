import React from 'react';
export default function PlanningLayout({
  children
}) {
  return /*#__PURE__*/React.createElement("main", {
    className: "container py-4"
  }, /*#__PURE__*/React.createElement("div", {
    className: "d-flex justify-content-between align-items-center mb-4"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h1", {
    className: "h2 fw-bold mb-1"
  }, /*#__PURE__*/React.createElement("i", {
    className: "bi bi-calendar3 me-2 text-primary"
  }), "Mon Planning"), /*#__PURE__*/React.createElement("p", {
    className: "text-muted mb-0"
  }, "Organisez vos repas de la semaine")), /*#__PURE__*/React.createElement("div", {
    className: "d-flex gap-2"
  }, /*#__PURE__*/React.createElement("form", {
    action: "/liste-courses/generer",
    method: "POST"
  }, /*#__PURE__*/React.createElement("button", {
    type: "submit",
    className: "btn btn-primary btn-sm"
  }, /*#__PURE__*/React.createElement("i", {
    className: "bi bi-cart-plus me-1"
  }), "G\xE9n\xE9rer ma liste")), /*#__PURE__*/React.createElement("button", {
    className: "btn btn-outline-danger btn-sm",
    "data-bs-toggle": "modal",
    "data-bs-target": "#clearModal"
  }, /*#__PURE__*/React.createElement("i", {
    className: "bi bi-trash me-1"
  }), "Vider le planning"))), children);
}