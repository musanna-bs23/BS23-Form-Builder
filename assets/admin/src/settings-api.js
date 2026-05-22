import apiFetch from '@wordpress/api-fetch';

export function loadSettings(formId) {
  if (!formId) {
    return Promise.resolve(defaultSettings());
  }
  return apiFetch({ path: `/bs23-form-builder/v1/forms/${formId}/settings` });
}

export function saveSettings(formId, settings) {
  return apiFetch({
    path: `/bs23-form-builder/v1/forms/${formId}/settings`,
    method: 'PUT',
    data: { settings },
  });
}

export function sendTestEmail(formId, settings) {
  return apiFetch({
    path: `/bs23-form-builder/v1/forms/${formId}/settings/test-email`,
    method: 'POST',
    data: { settings },
  });
}

export function defaultSettings() {
  return {
    notification: {
      enabled: true,
      to: '{admin_email}',
      subject: 'New submission from {form_title}',
      message: '{all_fields}',
      reply_to: '',
    },
    confirmation: {
      message: 'Thanks, your submission has been received.',
      redirect_url: '',
    },
  };
}
