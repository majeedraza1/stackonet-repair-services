import Vue from 'vue';
import VueRouter from 'vue-router';
import device from './views/device';
import deviceModel from './views/deviceModel';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'device', component: device},
	{path: '/device-model', name: 'device-model', component: deviceModel},
];

export default new VueRouter({routes});
