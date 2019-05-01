import Vue from 'vue';
import Pricing from './Pricing.vue'
import store from "./store";

if (document.querySelector('#stackonet_repair_services_pricing')) {
	new Vue({
		el: '#stackonet_repair_services_pricing',
		store: store,
		render: h => h(Pricing)
	});
}
