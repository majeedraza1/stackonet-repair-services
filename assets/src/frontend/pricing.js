import Vue from 'vue';
import Pricing from './pricing/Pricing.vue'

if (document.querySelector('#stackonet_repair_services_pricing')) {
	new Vue({
		el: '#stackonet_repair_services_pricing',
		render: h => h(Pricing)
	});
}
