import Vue from 'vue';
import BuyVan from './BuyVan'

if (document.querySelector('#stackonet_buy_van')) {
	new Vue({el: '#stackonet_buy_van', render: h => h(BuyVan)});
}
