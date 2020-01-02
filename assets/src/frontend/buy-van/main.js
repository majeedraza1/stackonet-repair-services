import Vue from 'vue';
import BuyVan from './BuyVan'

let el = document.querySelector('#stackonet_buy_van');
if (el) {
	document.querySelector('body').classList.add('stackonet-fullscreen');
	new Vue({el, render: h => h(BuyVan)});
}
