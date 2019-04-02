import Vue from 'vue';
import Phones from './my-account/Phones.vue';
import TrackStatus from './my-account/TrackStatus.vue';

let myAccountPhones = document.querySelector('#my-account-phones');
let myAccountTrackStatus = document.querySelector('#my-account-track-status');

if (myAccountPhones) {
	new Vue({
		el: myAccountPhones,
		render: h => h(Phones)
	});
}
if (myAccountTrackStatus) {
	new Vue({
		el: myAccountTrackStatus,
		render: h => h(TrackStatus)
	});
}
