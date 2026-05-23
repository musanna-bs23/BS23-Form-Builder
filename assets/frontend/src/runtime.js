export function initAllForms(root = document) {
  root.querySelectorAll('.bs23-form[data-bs23-form-id]').forEach((form) => initFormRuntime(form));
}

export function initFormRuntime(form) {
  const schemaNode = form.querySelector('.bs23-form__schema');
  if (!schemaNode) {
    return;
  }

  const schema = JSON.parse(schemaNode.textContent || '{"fields":[]}');
  const fields = flattenFields(schema.fields || []);
  const steps = buildSteps(form, fields);
  let activeStep = 0;

  const refresh = () => {
    const values = formValues(form);
    fields.forEach((field) => applyVisibility(form, field, values));
    if (steps.length > 1) {
      applyStepState(form, steps, activeStep);
    }
  };

  if (steps.length > 1) {
    renderStepNavigation(form, steps, {
      getActiveStep: () => activeStep,
      setActiveStep: (nextStep) => {
        activeStep = Math.max(0, Math.min(nextStep, steps.length - 1));
        refresh();
      },
      validateCurrentStep: () => validateStep(form, steps[activeStep], fields),
    });
  }

  form.addEventListener('input', refresh);
  form.addEventListener('change', refresh);
  form.addEventListener('submit', (event) => {
    refresh();
    const values = formValues(form);
    const firstError = fields.map((field) => applyValidation(form, field, values)).find(Boolean);
    if (firstError) {
      event.preventDefault();
      firstError.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
  });

  fields.forEach((field) => {
    controlsForField(form, field).forEach((control) => {
      control.addEventListener('blur', () => applyValidation(form, field, formValues(form)));
      control.addEventListener('change', () => applyValidation(form, field, formValues(form)));
    });
  });

  refresh();
}

export function evaluateVisibility(field, values) {
  const logic = field?.settings?.conditionalLogic;
  if (!logic || !logic.enabled || !Array.isArray(logic.rules) || logic.rules.length === 0) {
    return true;
  }

  const matches = logic.rules.map((rule) => conditionMatches(rule, values));
  const matched = logic.match === 'any' ? matches.includes(true) : !matches.includes(false);
  return logic.action === 'hide' ? !matched : matched;
}

export function validateField(field, value, values = {}) {
  const label = field.label || field.name || 'Field';
  const validation = field.settings?.validation || {};
  const rules = [
    field.required ? 'required' : '',
    field.type === 'email' ? 'email' : '',
    field.type === 'url' ? 'url' : '',
    validation.minLength ? `min:${validation.minLength}` : '',
    validation.maxLength ? `max:${validation.maxLength}` : '',
    validation.pattern ? `regex:${validation.pattern}` : '',
    validation.rules || '',
  ].filter(Boolean).join('|');

  for (const rule of parseRules(rules)) {
    const error = validateRule(label, field.name || '', value, values, rule.name, rule.params, validation);
    if (error) {
      return error;
    }
  }
  return '';
}

function applyVisibility(form, field, values) {
  const wrapper = form.querySelector(`[data-bs23-field-id="${cssEscape(field.id)}"]`);
  if (!wrapper) {
    return;
  }

  const visible = evaluateVisibility(field, values);
  wrapper.hidden = !visible;
  wrapper.classList.toggle('is-hidden', !visible);
  controlsForField(form, field).forEach((control) => {
    control.disabled = !visible;
  });
  if (!visible) {
    clearError(wrapper);
  }
}

function applyValidation(form, field, values) {
  const wrapper = form.querySelector(`[data-bs23-field-id="${cssEscape(field.id)}"]`);
  if (!wrapper || wrapper.hidden) {
    return null;
  }

  const error = validateField(field, values[field.name] ?? '', values);
  clearError(wrapper);
  wrapper.classList.toggle('has-error', !!error);
  if (!error) {
    return null;
  }

  const message = document.createElement('div');
  message.className = 'bs23-form__error bs23-form__error--client';
  message.textContent = error;
  wrapper.appendChild(message);
  return wrapper;
}

function buildSteps(form, fields) {
  const markers = Array.from(form.querySelectorAll('[data-bs23-step-marker]'));
  if (markers.length === 0) {
    return [];
  }

  return markers.map((marker, index) => {
    const nextMarker = markers[index + 1] || null;
    const fieldIds = [];
    let node = marker.nextElementSibling;
    while (node && node !== nextMarker) {
      if (node.dataset?.bs23FieldId) {
        fieldIds.push(node.dataset.bs23FieldId);
      }
      node = node.nextElementSibling;
    }

    return {
      id: marker.dataset.bs23FieldId || `step_${index + 1}`,
      label: marker.textContent.trim() || `Step ${index + 1}`,
      marker,
      fieldIds,
      fields: fields.filter((field) => fieldIds.includes(field.id)),
    };
  });
}

function renderStepNavigation(form, steps, controller) {
  const progress = document.createElement('div');
  progress.className = 'bs23-form__steps';
  progress.innerHTML = `
    <div class="bs23-form__steps-status"></div>
    <div class="bs23-form__steps-list"></div>
  `;
  form.insertBefore(progress, form.querySelector('.bs23-form__schema')?.nextSibling || form.firstChild);

  const list = progress.querySelector('.bs23-form__steps-list');
  steps.forEach((step, index) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'bs23-form__step-indicator';
    button.textContent = step.label;
    button.addEventListener('click', () => {
      if (index <= controller.getActiveStep() || controller.validateCurrentStep()) {
        controller.setActiveStep(index);
      }
    });
    list.appendChild(button);
  });

  const nav = document.createElement('div');
  nav.className = 'bs23-form__step-nav';
  nav.innerHTML = `
    <button type="button" class="bs23-form__previous">Previous</button>
    <button type="button" class="bs23-form__next">Next</button>
  `;
  form.appendChild(nav);

  nav.querySelector('.bs23-form__previous').addEventListener('click', () => {
    controller.setActiveStep(controller.getActiveStep() - 1);
  });
  nav.querySelector('.bs23-form__next').addEventListener('click', () => {
    if (controller.validateCurrentStep()) {
      controller.setActiveStep(controller.getActiveStep() + 1);
    }
  });
}

function applyStepState(form, steps, activeStep) {
  const submit = form.querySelector('.bs23-form__submit');
  const previous = form.querySelector('.bs23-form__previous');
  const next = form.querySelector('.bs23-form__next');
  const status = form.querySelector('.bs23-form__steps-status');

  steps.forEach((step, index) => {
    const active = index === activeStep;
    step.marker.hidden = !active;
    step.marker.classList.toggle('is-active', active);
    step.fieldIds.forEach((fieldId) => {
      const wrapper = form.querySelector(`[data-bs23-field-id="${cssEscape(fieldId)}"]`);
      if (wrapper) {
        const conditionHidden = wrapper.classList.contains('is-hidden');
        wrapper.hidden = !active || conditionHidden;
      }
    });
  });

  form.querySelectorAll('.bs23-form__step-indicator').forEach((button, index) => {
    button.classList.toggle('is-active', index === activeStep);
  });

  if (status) {
    status.textContent = `Step ${activeStep + 1} of ${steps.length}`;
  }
  if (previous) {
    previous.hidden = activeStep === 0;
  }
  if (next) {
    next.hidden = activeStep === steps.length - 1;
  }
  if (submit) {
    submit.hidden = activeStep !== steps.length - 1;
  }
}

function validateStep(form, step, fields) {
  const values = formValues(form);
  const firstError = step.fields
    .map((field) => applyValidation(form, field, values))
    .find(Boolean);
  if (firstError) {
    if (typeof firstError.scrollIntoView === 'function') {
      firstError.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
    return false;
  }
  return true;
}

function conditionMatches(rule, values) {
  const actual = normalize(values[rule.field] ?? '');
  const expected = normalize(rule.value ?? '');

  if (rule.operator === 'not_equals') {
    return actual !== expected;
  }
  if (rule.operator === 'contains') {
    return expected !== '' && actual.toLowerCase().includes(expected.toLowerCase());
  }
  if (rule.operator === 'is_empty') {
    return actual === '';
  }
  if (rule.operator === 'is_not_empty') {
    return actual !== '';
  }
  return actual === expected;
}

function validateRule(label, name, value, values, rule, params, validation) {
  const stringValue = normalize(value);
  const empty = stringValue === '';

  if (rule === 'nullable' && empty) {
    return '';
  }
  if (rule === 'required' && empty) {
    return `${label} is required.`;
  }
  if (empty && !['required', 'present', 'filled'].includes(rule)) {
    return '';
  }
  if (rule === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(stringValue)) {
    return `${label} must be a valid email address.`;
  }
  if (rule === 'url' && !isUrl(stringValue)) {
    return `${label} must be a valid URL.`;
  }
  if (rule === 'min' && sizeOf(value) < Number(params[0])) {
    return `${label} must be at least ${params[0]}.`;
  }
  if (rule === 'max' && sizeOf(value) > Number(params[0])) {
    return `${label} must be no more than ${params[0]}.`;
  }
  if (rule === 'between' && (sizeOf(value) < Number(params[0]) || sizeOf(value) > Number(params[1]))) {
    return `${label} must be between ${params[0]} and ${params[1]}.`;
  }
  if (rule === 'regex' && !matchesRegex(stringValue, params.join(':'))) {
    return validation.patternMessage || `${label} format is invalid.`;
  }
  if (rule === 'not_regex' && matchesRegex(stringValue, params.join(':'))) {
    return `${label} format is invalid.`;
  }
  if (rule === 'in' && !params.includes(stringValue)) {
    return `${label} value is invalid.`;
  }
  if (rule === 'not_in' && params.includes(stringValue)) {
    return `${label} value is invalid.`;
  }
  if (rule === 'alpha' && !/^[A-Za-z]+$/.test(stringValue)) {
    return `${label} format is invalid.`;
  }
  if (rule === 'alpha_num' && !/^[A-Za-z0-9]+$/.test(stringValue)) {
    return `${label} format is invalid.`;
  }
  if (rule === 'alpha_dash' && !/^[A-Za-z0-9_-]+$/.test(stringValue)) {
    return `${label} format is invalid.`;
  }
  if (rule === 'starts_with' && !params.some((prefix) => stringValue.startsWith(prefix))) {
    return `${label} start is invalid.`;
  }
  if (rule === 'ends_with' && !params.some((suffix) => stringValue.endsWith(suffix))) {
    return `${label} ending is invalid.`;
  }
  if (rule === 'confirmed' && stringValue !== normalize(values[`${name}_confirmation`] ?? '')) {
    return `${label} must match confirmation.`;
  }
  return '';
}

function formValues(form) {
  const data = new FormData(form);
  const values = {};
  data.forEach((value, key) => {
    const cleanKey = key.replace(/\[\]$/, '');
    if (values[cleanKey]) {
      values[cleanKey] = Array.isArray(values[cleanKey]) ? [...values[cleanKey], value] : [values[cleanKey], value];
      return;
    }
    values[cleanKey] = value;
  });
  return values;
}

function controlsForField(form, field) {
  if (!field.name) {
    return [];
  }
  return Array.from(form.querySelectorAll(`[name="${cssEscape(field.name)}"], [name="${cssEscape(field.name)}[]"]`));
}

function flattenFields(fields) {
  return fields.flatMap((field) => {
    if (field.type === 'container') {
      return (field.children || []).flatMap((column) => flattenFields(column || []));
    }
    return [field];
  });
}

function parseRules(rules) {
  return rules.split('|').filter(Boolean).map((rule) => {
    const [name, ...rest] = rule.split(':');
    return { name, params: rest.join(':').split(',').filter((param) => param !== '') };
  });
}

function normalize(value) {
  return Array.isArray(value) ? value.join(' ').trim() : String(value ?? '').trim();
}

function sizeOf(value) {
  const normalized = normalize(value);
  return Number.isFinite(Number(normalized)) && normalized !== '' ? Number(normalized) : normalized.length;
}

function matchesRegex(value, pattern) {
  try {
    const match = pattern.match(/^\/(.+)\/([gimsuy]*)$/);
    const regex = match ? new RegExp(match[1], match[2]) : new RegExp(pattern);
    return regex.test(value);
  } catch (error) {
    return false;
  }
}

function clearError(wrapper) {
  wrapper.classList.remove('has-error');
  wrapper.querySelectorAll('.bs23-form__error--client').forEach((node) => node.remove());
}

function isUrl(value) {
  try {
    return Boolean(new URL(value));
  } catch (error) {
    return false;
  }
}

function cssEscape(value) {
  if (window.CSS?.escape) {
    return window.CSS.escape(value);
  }
  return String(value).replace(/"/g, '\\"');
}
