import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

globalThis.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.start();
