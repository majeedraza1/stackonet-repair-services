import Vue from 'vue';
import VueRouter from 'vue-router';
import device from './views/device.vue';
import deviceModel from './views/deviceModel.vue';
import deviceColor from './views/deviceColor.vue';
import zipCode from './views/zipCode.vue';
import screenCracked from './views/screenCracked.vue';
import deviceIssue from './views/deviceIssue.vue';
import timing from './views/timing.vue';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'device', component: device},
	{path: '/device-model', name: 'device-model', component: deviceModel},
	{path: '/device-color', name: 'device-color', component: deviceColor},
	{path: '/zip-code', name: 'zip-code', component: zipCode},
	{path: '/screen-cracked', name: 'screen-cracked', component: screenCracked},
	{path: '/select-issue', name: 'select-issue', component: deviceIssue},
	{path: '/select-time', name: 'select-time', component: timing},
];

export default new VueRouter({
	routes: routes
});
