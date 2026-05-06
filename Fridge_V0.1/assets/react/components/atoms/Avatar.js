import React from 'react';
export default function Avatar({
  src,
  alt,
  size = 42
}) {
  return /*#__PURE__*/React.createElement("img", {
    src: src,
    style: {
      width: `${size}px`,
      height: `${size}px`,
      objectFit: 'cover',
      borderRadius: 'var(--radius-md)'
    },
    alt: alt
  });
}