import Vue from 'vue';
import VueRouter from 'vue-router';
import device from './views/device.vue';
import deviceModel from './views/deviceModel.vue';
import deviceColor from './views/deviceColor.vue';
import zipCode from './views/zipCode.vue';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'device', component: device},
	{path: '/device-model', name: 'device-model', component: deviceModel},
	{path: '/device-color', name: 'device-color', component: deviceColor},
	{path: '/zip-code', name: 'zip-code', component: zipCode},
];

export default new VueRouter({routes});
