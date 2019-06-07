import Vue from 'vue';
import VueRouter from 'vue-router';
import Dashboard from './pages/Dashboard';
import StoreAddress from './pages/StoreAddress';
import ChatApp from './pages/ChatApp'

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'Dashboard', component: Dashboard},
	{path: '/store-addresses', name: 'StoreAddress', component: StoreAddress},
	{path: '/chat-app', name: 'ChatApp', component: ChatApp}
];

export default new VueRouter({
	routes: routes
});
