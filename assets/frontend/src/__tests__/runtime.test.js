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

test('validates confirmed rule against field name confirmation', () => {
  const field = {
    label: 'Password',
    name: 'password',
    type: 'text',
    settings: { validation: { rules: 'confirmed' } },
  };

  expect(validateField(field, 'secret', { password_confirmation: 'other' })).toBe('Password must match confirmation.');
  expect(validateField(field, 'secret', { password_confirmation: 'secret' })).toBe('');
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

test('runtime creates multi-step navigation and shows submit on final step', () => {
  document.body.innerHTML = `
    <form class="bs23-form" data-bs23-form-id="12">
      <script type="application/json" class="bs23-form__schema">{"fields":[{"id":"step_1","type":"form_step","label":"Contact","name":"form_step","settings":{}},{"id":"field_1","type":"text","label":"Name","name":"name","settings":{}},{"id":"step_2","type":"form_step","label":"Details","name":"form_step","settings":{}},{"id":"field_2","type":"email","label":"Email","name":"email","settings":{}}]}</script>
      <div class="bs23-form__step-marker" data-bs23-field-id="step_1" data-bs23-step-marker>Contact</div>
      <div class="bs23-form__field" data-bs23-field-id="field_1"><input name="name" value="Hasan" /></div>
      <div class="bs23-form__step-marker" data-bs23-field-id="step_2" data-bs23-step-marker>Details</div>
      <div class="bs23-form__field" data-bs23-field-id="field_2"><input name="email" value="person@example.com" /></div>
      <button class="bs23-form__submit" type="submit">Submit</button>
    </form>
  `;

  initFormRuntime(document.querySelector('.bs23-form'));

  expect(document.querySelector('.bs23-form__steps-status').textContent).toBe('Step 1 of 2');
  expect(document.querySelector('[data-bs23-field-id="field_1"]').hidden).toBe(false);
  expect(document.querySelector('[data-bs23-field-id="field_2"]').hidden).toBe(true);
  expect(document.querySelector('.bs23-form__submit').hidden).toBe(true);

  document.querySelector('.bs23-form__next').click();

  expect(document.querySelector('.bs23-form__steps-status').textContent).toBe('Step 2 of 2');
  expect(document.querySelector('[data-bs23-field-id="field_1"]').hidden).toBe(true);
  expect(document.querySelector('[data-bs23-field-id="field_2"]').hidden).toBe(false);
  expect(document.querySelector('.bs23-form__submit').hidden).toBe(false);
});

test('runtime blocks next step when current step has validation errors', () => {
  document.body.innerHTML = `
    <form class="bs23-form" data-bs23-form-id="12">
      <script type="application/json" class="bs23-form__schema">{"fields":[{"id":"step_1","type":"form_step","label":"Contact","name":"form_step","settings":{}},{"id":"field_1","type":"text","label":"Name","name":"name","required":true,"settings":{}},{"id":"step_2","type":"form_step","label":"Details","name":"form_step","settings":{}},{"id":"field_2","type":"email","label":"Email","name":"email","settings":{}}]}</script>
      <div class="bs23-form__step-marker" data-bs23-field-id="step_1" data-bs23-step-marker>Contact</div>
      <div class="bs23-form__field" data-bs23-field-id="field_1"><input name="name" value="" /></div>
      <div class="bs23-form__step-marker" data-bs23-field-id="step_2" data-bs23-step-marker>Details</div>
      <div class="bs23-form__field" data-bs23-field-id="field_2"><input name="email" /></div>
      <button class="bs23-form__submit" type="submit">Submit</button>
    </form>
  `;

  initFormRuntime(document.querySelector('.bs23-form'));
  document.querySelector('.bs23-form__next').click();

  expect(document.querySelector('.bs23-form__steps-status').textContent).toBe('Step 1 of 2');
  expect(document.querySelector('.bs23-form__error--client').textContent).toBe('Name is required.');
});
