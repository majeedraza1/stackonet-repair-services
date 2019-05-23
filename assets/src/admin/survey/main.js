import Vue from 'vue';
import axios from 'axios';
import SurveyListTable from './SurveyListTable.vue'
import menuFix from "../utils/admin-menu-fix.js";

jQuery.ajaxSetup({
	beforeSend: function (xhr) {
		xhr.setRequestHeader('X-WP-Nonce', window.stackonetSettings.nonce);
	}
});
axios.defaults.headers.common['X-WP-Nonce'] = window.stackonetSettings.nonce;

let element = document.querySelector('#admin-stackonet-survey');
if (element) {
	new Vue({el: element, render: h => h(SurveyListTable)});
}

// fix the admin menu for the slug "vue-wp-starter"
menuFix('survey');
