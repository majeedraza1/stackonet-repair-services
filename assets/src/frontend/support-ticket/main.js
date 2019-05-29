import Vue from 'vue';
import router from './routers.js';
import store from './store.js';
import SupportTicket from './SupportTicket';
import axios from "axios";

let el = document.querySelector('#stackonet_support_ticket');
if (el) {
	axios.defaults.headers.common['X-WP-Nonce'] = window.PhoneRepairs.rest_nonce;
	new Vue({el, store, router, render: h => h(SupportTicket)});
}
