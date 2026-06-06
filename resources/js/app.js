import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import 'bootstrap/dist/js/bootstrap.bundle';

Alpine.plugin(intersect);
window.Alpine = Alpine;
Alpine.start();