import {
  evaluateVisibility,
  validateField,
  initFormRuntime,
} from '../runtime';

test('evaluates show and hide conditional logic', () => {
  const field = {
    name: 'email',
    settings: {
      conditionalLogic: {
        enabled: true,
        action: 'show',
        match: 'all',
        rules: [{ field: 'department', operator: 'equals', value: 'Sales' }],
      },
    },
  };

  expect(evaluateVisibility(field, { department: 'Sales' })).toBe(true);
  expect(evaluateVisibility(field, { department: 'Support' })).toBe(false);
});

test('validates required email and length rules', () => {
  const field = {
    label: 'Work Email',
    name: 'email',
    type: 'email',
    required: true,
    settings: { validation: { minLength: '6', rules: 'email|max:30' } },
  };

  expect(validateField(field, '')).toBe('Work Email is required.');
  expect(validateField(field, 'bad')).toBe('Work Email must be a valid email address.');
  expect(validateField(field, 'person@example.com')).toBe('');
});

test('runtime hides conditional field and disables controls', () => {
  document.body.innerHTML = `
    <form class="bs23-form" data-bs23-form-id="12">
      <script type="application/json" class="bs23-form__schema">{"fields":[{"id":"field_1","type":"text","label":"Department","name":"department","settings":{}},{"id":"field_2","type":"email","label":"Email","name":"email","required":true,"settings":{"conditionalLogic":{"enabled":true,"action":"show","match":"all","rules":[{"field":"department","operator":"equals","value":"Sales"}]}}}]}</script>
      <div class="bs23-form__field" data-bs23-field-id="field_1"><input name="department" value="Support" /></div>
      <div class="bs23-form__field" data-bs23-field-id="field_2"><input name="email" required /></div>
    </form>
  `;

  initFormRuntime(document.querySelector('.bs23-form'));

  expect(document.querySelector('[data-bs23-field-id="field_2"]').hidden).toBe(true);
  expect(document.querySelector('[name="email"]').disabled).toBe(true);
});
