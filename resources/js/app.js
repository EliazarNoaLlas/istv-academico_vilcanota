import './bootstrap';
import { initSidebar } from './layout/sidebar';
import { initDropdowns } from './layout/dropdowns';

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initDropdowns();
});
