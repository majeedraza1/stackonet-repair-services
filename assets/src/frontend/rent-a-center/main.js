import Vue from 'vue';
import RentCenter from './RentCenter.vue'
import router from './routers.js';
import store from './store.js';

let element = document.querySelector('#stackonet_rent_a_center');
if (element) {
	document.querySelector('body').classList.add('page-rent-a-center');
	new Vue({el: element, store: store, router: router, render: h => h(RentCenter)});
}
