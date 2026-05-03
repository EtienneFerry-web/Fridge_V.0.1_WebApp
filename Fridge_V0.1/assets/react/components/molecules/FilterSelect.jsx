import React from 'react';

export default function FilterSelect({ id, value, onChange, options, defaultOption }) {
    return (
        <select id={id} className="form-select form-select-sm" value={value} onChange={e => onChange(e.target.value)}>
            {defaultOption && <option value={defaultOption.value}>{defaultOption.label}</option>}
            {options.map((opt, i) => (
                <option key={i} value={opt.value}>{opt.label}</option>
            ))}
        </select>
    );
}
