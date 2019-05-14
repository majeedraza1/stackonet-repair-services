import Vue from 'vue';
import SpotAppointment from './SpotAppointment';

let element = document.querySelector('#stackonet_spot_appointment');
if (element) {
	new Vue({el: element, render: h => h(SpotAppointment)});
}
