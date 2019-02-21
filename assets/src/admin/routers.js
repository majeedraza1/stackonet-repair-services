import Vue from 'vue';
import VueRouter from 'vue-router';
import Devices from './views/Devices.vue';
import Device from './views/Device.vue';
import ServiceAreas from './views/ServiceAreas.vue';
import Settings from './views/Settings.vue';
import Issues from './views/Issues.vue';
import RequestAreas from './views/RequestAreas.vue';
import Testimonial from './views/Testimonial.vue';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'devices', component: Devices},
	{path: '/device/new', name: 'device', component: Device},
	{path: '/device/edit/:id', name: 'device-edit', component: Device},
	{path: '/areas', name: 'service-areas', component: ServiceAreas},
	{path: '/requested-areas', name: 'requested-areas', component: RequestAreas},
	{path: '/issues', name: 'issues', component: Issues},
	{path: '/settings', name: 'settings', component: Settings},
	{path: '/testimonial', name: 'Testimonial', component: Testimonial},
];

export default new VueRouter({
	routes // short for `routes: routes`
});
