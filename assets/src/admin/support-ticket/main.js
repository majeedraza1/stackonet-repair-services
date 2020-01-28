import Vue from 'vue';
import SupportTicket from './SupportTicket.vue'
import router from './routers.js';
import store from './store.js';
import menuFix from "../utils/admin-menu-fix.js";

jQuery.ajaxSetup({
	beforeSend: function (xhr) {
		xhr.setRequestHeader('X-WP-Nonce', window.stackonetSettings.nonce);
	}
});

let el = document.querySelector('#admin-stackonet-support-tickets');
if (el) {
	new Vue({el: el, store: store, router: router, render: h => h(SupportTicket)});
}

// fix the admin menu for the slug "wpsc-tickets"
menuFix('wpsc-tickets');
