import Vue from 'vue';
import VueRouter from 'vue-router';
import Dashboard from './pages/Dashboard';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'Dashboard', component: Dashboard},
];

export default new VueRouter({
	routes: routes
});
