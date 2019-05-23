import Vue from 'vue';
import RegistrationForm from './RegistrationForm.vue';

let rForm = document.querySelector('#stackonet_manager_registration_form');
if (rForm) {
	let body = document.querySelector('body');
	body.classList.add('has-testimonial-form');

	new Vue({
		el: rForm,
		render: h => h(RegistrationForm)
	});
}
