import Vue from 'vue';
import LoginForm from './LoginForm'
import App from './App'
import router from './routers.js';
import store from './store.js';
import axios from "axios";
import Dialog from "shapla-confirm-dialog";

Vue.use(Dialog);

let el = document.querySelector('#stackonet_repair_services_dashboard');
if (el) {
	axios.defaults.headers.common['X-WP-Nonce'] = window.PhoneRepairs.rest_nonce;
	new Vue({el, store, router, render: h => h(App)});
}

let el2 = document.querySelector('#stackonet_repair_services_dashboard_login');
if (el2) {
	new Vue({el: el2, store, router, render: h => h(LoginForm)});
}
