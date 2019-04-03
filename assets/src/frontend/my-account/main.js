import Vue from 'vue';
import Phones from './Phones.vue';
import store from './store.js';
import TrackStatus from './TrackStatus.vue';

let myAccountPhones = document.querySelector('#my-account-phones');
let myAccountTrackStatus = document.querySelector('#my-account-track-status');

jQuery.ajaxSetup({
	beforeSend: function (xhr) {
		xhr.setRequestHeader('X-WP-Nonce', window.PhoneRepairs.rest_nonce);
	}
});

if (myAccountPhones) {
	new Vue({
		el: myAccountPhones,
		store: store,
		render: h => h(Phones)
	});
}
if (myAccountTrackStatus) {
	new Vue({
		el: myAccountTrackStatus,
		render: h => h(TrackStatus)
	});
}
