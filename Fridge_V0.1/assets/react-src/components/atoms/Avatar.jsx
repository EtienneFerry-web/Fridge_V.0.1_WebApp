import React from 'react';

export default function Avatar({ src, alt, size = 42 }) {
    return (
        <img 
            src={src} 
            className="rounded-circle" 
            style={{ width: `${size}px`, height: `${size}px`, objectFit: 'cover' }} 
            alt={alt} 
        />
    );
}
