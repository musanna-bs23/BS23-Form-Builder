import { initAllForms } from './runtime';

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initAllForms());
} else {
  initAllForms();
}
