import Vue from 'vue';
import Dashboard from './Dashboard'
import MdlDashboard from './MdlDashboard'
import router from './routers.js';
import store from './store.js';
import axios from "axios";

let el = document.querySelector('#stackonet_repair_services_dashboard');
if (el) {
	axios.defaults.headers.common['X-WP-Nonce'] = window.PhoneRepairs.rest_nonce;
	new Vue({el, store, router, render: h => h(MdlDashboard)});
}
