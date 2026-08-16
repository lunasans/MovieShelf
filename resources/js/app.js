import './bootstrap';

// Selbst gehostete Fonts & Icons (kein CDN, DSGVO)
import '@fontsource/outfit/400.css';
import '@fontsource/outfit/600.css';
import '@fontsource/outfit/700.css';
import '@fontsource/outfit/800.css';
import '@fontsource/outfit/900.css';
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';
import '@fontsource/jetbrains-mono/400.css';
import '@fontsource/jetbrains-mono/500.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/900.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

// Chart.js, SortableJS & GridStack global bereitstellen (Inline-Skripte in Blades
// nutzen window.Chart / window.Sortable / window.GridStack)
import Chart from 'chart.js/auto';
import Sortable from 'sortablejs';
import { GridStack } from 'gridstack';
import 'gridstack/dist/gridstack.min.css';

window.Chart = Chart;
window.Sortable = Sortable;
window.GridStack = GridStack;

globalThis.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.start();
