import Vue from 'vue';
import axios from 'axios';
import BecomeTechListTable from './BecomeTechListTable.vue'
import menuFix from "../utils/admin-menu-fix.js";

jQuery.ajaxSetup({
	beforeSend: function (xhr) {
		xhr.setRequestHeader('X-WP-Nonce', window.stackonetSettings.nonce);
	}
});
axios.defaults.headers.common['X-WP-Nonce'] = window.stackonetSettings.nonce;

let element = document.querySelector('#admin-stackonet-become-tech');
if (element) {
	new Vue({el: element, render: h => h(BecomeTechListTable)});
}

// fix the admin menu for the slug "become-tech"
menuFix('become-tech');
