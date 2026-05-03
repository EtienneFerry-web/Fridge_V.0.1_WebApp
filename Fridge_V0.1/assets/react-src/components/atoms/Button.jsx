import React from 'react';

export default function Button({ children, variant = 'primary', className = '', onClick, type = 'button', ...props }) {
    return (
        <button 
            type={type} 
            className={`btn btn-${variant} ${className}`} 
            onClick={onClick}
            {...props}
        >
            {children}
        </button>
    );
}
