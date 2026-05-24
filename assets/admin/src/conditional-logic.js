export const conditionalOperators = [
  { value: 'equals', label: 'Equals' },
  { value: 'not_equals', label: 'Does not equal' },
  { value: 'contains', label: 'Contains' },
  { value: 'is_empty', label: 'Is empty' },
  { value: 'is_not_empty', label: 'Is not empty' },
];

const sourceTypes = new Set([
  'name',
  'email',
  'text',
  'textarea',
  'number',
  'dropdown',
  'radio',
  'checkbox',
  'multiple_choice',
  'url',
  'phone',
  'hidden',
]);

export function defaultConditionalLogic(sourceField = '') {
  return {
    enabled: true,
    action: 'show',
    match: 'all',
    rules: [
      {
        field: sourceField,
        operator: 'equals',
        value: '',
      },
    ],
  };
}

export function conditionNeedsValue(operator) {
  return !['is_empty', 'is_not_empty'].includes(operator);
}

export function conditionSourceFields(fields, selectedFieldId) {
  return flattenFields(fields)
    .filter((field) => sourceTypes.has(field.type))
    .sort((first, second) => {
      if (first.id === selectedFieldId) {
        return 1;
      }
      if (second.id === selectedFieldId) {
        return -1;
      }
      return 0;
    })
    .map((field) => ({
      id: field.id,
      label: field.label || field.name || field.id,
      name: field.name || field.id,
    }));
}

function flattenFields(fields) {
  return fields.flatMap((field) => {
    if (!field || typeof field !== 'object') {
      return [];
    }
    if (field.type === 'container') {
      return (field.children || []).flatMap((column) => flattenFields(column || []));
    }
    return [field];
  });
}
