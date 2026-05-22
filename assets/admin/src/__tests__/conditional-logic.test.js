import {
  conditionNeedsValue,
  conditionSourceFields,
  defaultConditionalLogic,
} from '../conditional-logic';

test('creates a default show rule payload', () => {
  expect(defaultConditionalLogic('email')).toEqual({
    enabled: true,
    action: 'show',
    match: 'all',
    rules: [
      {
        field: 'email',
        operator: 'equals',
        value: '',
      },
    ],
  });
});

test('lists eligible source fields from root and container children', () => {
  const fields = [
    { id: 'field_1', type: 'email', label: 'Email Address', name: 'email', settings: {} },
    { id: 'field_2', type: 'section_break', label: 'Section', name: 'section', settings: {} },
    {
      id: 'container_1',
      type: 'container',
      columns: 2,
      children: [
        [{ id: 'field_3', type: 'dropdown', label: 'Department', name: 'department', settings: {} }],
        [{ id: 'field_4', type: 'html', label: 'Intro', name: 'intro', settings: {} }],
      ],
    },
  ];

  expect(conditionSourceFields(fields, 'field_3')).toEqual([
    { id: 'field_1', label: 'Email Address', name: 'email' },
  ]);
});

test('detects operators that do not need a value', () => {
  expect(conditionNeedsValue('equals')).toBe(true);
  expect(conditionNeedsValue('is_empty')).toBe(false);
  expect(conditionNeedsValue('is_not_empty')).toBe(false);
});
