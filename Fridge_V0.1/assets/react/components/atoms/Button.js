function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
import React from 'react';
export default function Button({
  children,
  variant = 'primary',
  className = '',
  onClick,
  type = 'button',
  ...props
}) {
  return /*#__PURE__*/React.createElement("button", _extends({
    type: type,
    className: `btn btn-${variant} ${className}`,
    onClick: onClick
  }, props), children);
}