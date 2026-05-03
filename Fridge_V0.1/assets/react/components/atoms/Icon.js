import React from 'react';
export default function Icon({
  name,
  className = ''
}) {
  return /*#__PURE__*/React.createElement("i", {
    className: `bi bi-${name} ${className}`
  });
}