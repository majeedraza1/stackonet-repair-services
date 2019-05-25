import Vue from 'vue';
import SupportTicket from './SupportTicket';

let element = document.querySelector('#stackonet_support_ticket');
if (element) {
	new Vue({
		el: element,
		render: h => h(SupportTicket)
	});
}
