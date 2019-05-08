import Vue from 'vue';
import BecomeTech from './BecomeTech';

let element = document.querySelector('#stackonet_become_a_tech');
if (element) {
	new Vue({
		el: element,
		render: h => h(BecomeTech)
	});
}
