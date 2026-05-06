import React from 'react';

export default function PlanningHero({ title, subtitle }) {
  return React.createElement("div", {
    className: "search-hero mb-5"
  }, React.createElement("div", {
    className: "container"
  }, React.createElement("div", {
    className: "row align-items-center"
  }, React.createElement("div", {
    className: "col-lg-7"
  }, React.createElement("h1", {
    className: "display-4 fw-bold mb-3",
    style: {
      fontFamily: 'var(--font-display)'
    }
  }, title), React.createElement("p", {
    className: "lead text-muted mb-4",
    style: {
      fontSize: '1.25rem'
    }
  }, subtitle), React.createElement("div", {
    className: "d-flex gap-3"
  }, React.createElement("form", {
    action: "/liste-courses/generer",
    method: "POST"
  }, React.createElement("button", {
    type: "submit",
    className: "btn btn-primary px-4 py-2"
  }, React.createElement("i", {
    className: "bi bi-cart-plus me-2"
  }), "G\xE9n\xE9rer ma liste")), React.createElement("button", {
    className: "btn btn-outline-secondary px-4 py-2",
    "data-bs-toggle": "modal",
    "data-bs-target": "#clearModal"
  }, React.createElement("i", {
    className: "bi bi-trash me-2"
  }), "R\xE9initialiser"))), React.createElement("div", {
    className: "col-lg-5 d-none d-lg-block"
  }, React.createElement("div", {
    className: "position-relative"
  }, React.createElement("div", {
    className: "hero-blob",
    style: {
      position: 'absolute',
      top: '-50px',
      right: '-50px',
      width: '400px',
      height: '400px',
      background: 'var(--custard-light)',
      borderRadius: 'var(--radius-xl)',
      zIndex: -1,
      opacity: 0.5
    }
  }), React.createElement("img", {
    src: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&q=80&w=800",
    alt: "Organic Vegetables",
    className: "img-fluid",
    style: {
      borderRadius: 'var(--radius-xl)',
      boxShadow: 'var(--shadow-lg)',
      transform: 'rotate(-2deg)'
    }
  }))))));
}
