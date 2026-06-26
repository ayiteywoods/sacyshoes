import Alpine from 'alpinejs';
// Dark mode disabled for now
// import { createThemeStore } from './theme.js';
import './admin-confirm.js';
import './admin-table.js';
import './checkout-shipping.js';
import './load-more-products.js';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
  // Dark mode disabled for now
  // Alpine.store('theme', createThemeStore());
});

Alpine.start();
