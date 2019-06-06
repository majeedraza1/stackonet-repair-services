import Vue from 'vue';
import CheckoutAnalysis from './CheckoutAnalysis'

let element = document.querySelector('#stackonet_checkout_analysis');
if (element) {
	new Vue({el: element, render: h => h(CheckoutAnalysis)});
}
