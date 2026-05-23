import apiFetch from '@wordpress/api-fetch';

export function configureApiFetch(config = window.bs23FormBuilder || {}) {
  if (config.nonce && typeof apiFetch.createNonceMiddleware === 'function') {
    apiFetch.use(apiFetch.createNonceMiddleware(config.nonce));
  }

  if (config.restUrl && typeof apiFetch.createRootURLMiddleware === 'function') {
    apiFetch.use(apiFetch.createRootURLMiddleware(config.restUrl.replace(/\/bs23-form-builder\/v1\/?$/, '')));
  }
}
