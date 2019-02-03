import Vue from 'vue';
import App from './App.vue'
import router from './routers.js';
import store from './store.js';

if (document.querySelector('#stackonet_repair_services')) {
	let body = document.querySelector('body');
	body.classList.add('page-repair-services');

	new Vue({
		el: '#stackonet_repair_services',
		store: store,
		router: router,
		render: h => h(App)
	});
}
