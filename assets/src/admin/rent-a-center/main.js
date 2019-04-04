import Vue from 'vue';
import RentACenter from './RentACenter.vue'
import router from './routers.js';
import store from './store.js';
import menuFix from "../utils/admin-menu-fix.js";

jQuery.ajaxSetup({
	beforeSend: function (xhr) {
		xhr.setRequestHeader('X-WP-Nonce', window.stackonetSettings.nonce);
	}
});

let rentACenter = document.querySelector('#rent-a-center');
if (rentACenter) {
	new Vue({
		el: rentACenter,
		store: store,
		router: router,
		render: h => h(RentACenter)
	});
}

// fix the admin menu for the slug "vue-wp-starter"
menuFix('rent-a-center');
