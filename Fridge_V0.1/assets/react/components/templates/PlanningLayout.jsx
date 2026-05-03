import React from 'react';

export default function PlanningLayout({ children }) {
    return (
        <main className="container py-4">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 className="h2 fw-bold mb-1">
                        <i className="bi bi-calendar3 me-2 text-primary"></i>Mon Planning
                    </h1>
                    <p className="text-muted mb-0">Organisez vos repas de la semaine</p>
                </div>
                <div className="d-flex gap-2">
                    <form action="/liste-courses/generer" method="POST">
                        <button type="submit" className="btn btn-primary btn-sm">
                            <i className="bi bi-cart-plus me-1"></i>Générer ma liste
                        </button>
                    </form>
                    <button className="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearModal">
                        <i className="bi bi-trash me-1"></i>Vider le planning
                    </button>
                </div>
            </div>
            {children}
        </main>
    );
}
