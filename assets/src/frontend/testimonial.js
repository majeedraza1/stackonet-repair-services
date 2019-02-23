import Vue from 'vue';
import Testimonial from './Testimonial.vue'
import {tns} from 'tiny-slider/src/tiny-slider';

if (document.querySelector('#stackonet_testimonial_form')) {
	let body = document.querySelector('body');
	body.classList.add('has-testimonial-form');

	new Vue({
		el: '#stackonet_testimonial_form',
		render: h => h(Testimonial)
	});
}


let sliderOuter = document.querySelector('.fp-tns-slider-outer');
if (sliderOuter) {
	let controls = sliderOuter.querySelector('.fp-tns-slider-controls'),
		slider = sliderOuter.querySelector('.client-testimonial');

	tns({
		container: slider,
		controls: true,
		controlsContainer: controls,
		slideBy: 1,
		gutter: 10,
		nav: false,
		autoplay: true,
		edgePadding: 0,
		items: 1,
		responsive: {
			600: {items: 2},
			900: {items: 3},
		}
	});
}
