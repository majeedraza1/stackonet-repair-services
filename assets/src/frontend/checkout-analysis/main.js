import Vue from 'vue';
import CheckoutAnalysis from './CheckoutAnalysis'
import axios from "axios";

let element = document.querySelector('#stackonet_checkout_analysis');
if (element) {
	axios.defaults.headers.common['X-WP-Nonce'] = window.PhoneRepairs.rest_nonce;
	new Vue({el: element, render: h => h(CheckoutAnalysis)});
}
