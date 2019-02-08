import Vue from 'vue';
import VueRouter from 'vue-router';
import Devices from './views/Devices.vue';
import Device from './views/Device.vue';
import ServiceAreas from './views/ServiceAreas.vue';
import Settings from './views/Settings.vue';
import Issues from './views/Issues.vue';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'devices', component: Devices},
	{path: '/device/new', name: 'device', component: Device},
	{path: '/device/edit/:id', name: 'device-edit', component: Device},
	{path: '/areas', name: 'service-areas', component: ServiceAreas},
	{path: '/issues', name: 'issues', component: Issues},
	{path: '/settings', name: 'settings', component: Settings}
];

export default new VueRouter({
	routes // short for `routes: routes`
});
