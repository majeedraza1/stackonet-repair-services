import Vue from 'vue';
import VueRouter from 'vue-router';
import Phones from './Phones.vue';

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'Phones', component: Phones},
];

export default new VueRouter({routes});
