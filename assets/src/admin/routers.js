import Vue from 'vue';
import VueRouter from 'vue-router';
import Devices from './views/Devices.vue';
import Device from './views/Device.vue';
import ServiceAreas from './views/ServiceAreas.vue';
import Settings from './views/Settings.vue';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'Devices', component: Devices},
	{path: '/device/new', name: 'Device', component: Device},
	{path: '/device/edit/:id', name: 'Device', component: Device},
	{path: '/areas', name: 'ServiceAreas', component: ServiceAreas},
	{path: '/settings', name: 'Settings', component: Settings}
];

export default new VueRouter({
	routes // short for `routes: routes`
});
