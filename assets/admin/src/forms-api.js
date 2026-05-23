import apiFetch from '@wordpress/api-fetch';

export function listForms() {
  return apiFetch({ path: '/bs23-form-builder/v1/forms' });
}

export function loadForm(formId) {
  return apiFetch({ path: `/bs23-form-builder/v1/forms/${formId}` });
}
