import { createRoot } from '@wordpress/element';
import App from './app';
import { configureApiFetch } from './configure-api-fetch';
import './styles.scss';

const root = document.getElementById('bs23-form-builder-root');

if (root) {
  configureApiFetch();
  createRoot(root).render(<App />);
}
