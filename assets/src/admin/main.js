import Vue from 'vue';
import App from './App.vue'
import router from './routers.js';
import store from './store.js';
import menuFix from "./utils/admin-menu-fix.js";

jQuery.ajaxSetup({
	beforeSend: function (xhr) {
		xhr.setRequestHeader('X-WP-Nonce', window.stackonetSettings.nonce);
	}
});

let element = document.querySelector('#stackonet-repair-services-admin');
if (element) {
	new Vue({el: element, store: store, router: router, render: h => h(App)});
}

// fix the admin menu for the slug "vue-wp-starter"
menuFix('phone-repairs');
