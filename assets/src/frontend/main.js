import Vue from 'vue';
import App from './App.vue'
import router from './routers.js';

if (document.querySelector('#stackonet_repair_services')) {
	let body = document.querySelector('body');
	body.classList.add('page-repair-services');

	new Vue({
		el: '#stackonet_repair_services',
		router: router,
		render: h => h(App)
	});
}
