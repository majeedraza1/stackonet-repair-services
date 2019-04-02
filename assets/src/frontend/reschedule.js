import Vue from 'vue';
import Reschedule from './reschedule/Reschedule'

if (document.querySelector('#stackonet_reschedule_order')) {
	let body = document.querySelector('body');
	body.classList.add('has-reschedule-form');

	new Vue({
		el: '#stackonet_reschedule_order',
		render: h => h(Reschedule)
	});
}
