import { createRoot } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import App from './app';
import './styles.scss';

const root = document.getElementById('bs23-form-entries-root');

if (root) {
  if (window.bs23Entries?.nonce && typeof apiFetch.createNonceMiddleware === 'function') {
    apiFetch.use(apiFetch.createNonceMiddleware(window.bs23Entries.nonce));
  }
  if (window.bs23Entries?.restUrl && typeof apiFetch.createRootURLMiddleware === 'function') {
    apiFetch.use(apiFetch.createRootURLMiddleware(window.bs23Entries.restUrl.replace(/\/bs23-form-builder\/v1\/?$/, '')));
  }
  createRoot(root).render(<App />);
}
