import Vue from 'vue';
import Testimonial from './Testimonial.vue'

if (document.querySelector('#stackonet_testimonial_form')) {
	let body = document.querySelector('body');
	body.classList.add('has-testimonial-form');

	new Vue({
		el: '#stackonet_testimonial_form',
		render: h => h(Testimonial)
	});
}
