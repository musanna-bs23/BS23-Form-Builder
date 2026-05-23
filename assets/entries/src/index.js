import { createRoot } from '@wordpress/element';
import App from './app';
import './styles.scss';

const root = document.getElementById('bs23-form-entries-root');

if (root) {
  createRoot(root).render(<App />);
}
