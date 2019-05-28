import Vue from 'vue';
import router from './routers.js';
import SupportTicket from './SupportTicket';
import axios from "axios";

let element = document.querySelector('#stackonet_support_ticket');
if (element) {
	axios.defaults.headers.common['X-WP-Nonce'] = window.PhoneRepairs.rest_nonce;
	new Vue({
		el: element,
		router,
		render: h => h(SupportTicket)
	});
}
