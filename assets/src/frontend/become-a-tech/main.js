import Vue from 'vue';
import BecomeTechnician from './BecomeTechnician';

let element = document.querySelector('#stackonet_become_technician');
if (element) {
	new Vue({
		el: element,
		render: h => h(BecomeTechnician)
	});
}
