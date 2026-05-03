import React from 'react';
export default function FilterSelect({
  id,
  value,
  onChange,
  options,
  defaultOption
}) {
  return /*#__PURE__*/React.createElement("select", {
    id: id,
    className: "form-select form-select-sm",
    value: value,
    onChange: e => onChange(e.target.value)
  }, defaultOption && /*#__PURE__*/React.createElement("option", {
    value: defaultOption.value
  }, defaultOption.label), options.map((opt, i) => /*#__PURE__*/React.createElement("option", {
    key: i,
    value: opt.value
  }, opt.label)));
}