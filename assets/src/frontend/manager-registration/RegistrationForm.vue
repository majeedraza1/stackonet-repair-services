<template>
	<div>
		<form action="#" method="post" accept-charset="UTF-8" @submit.prevent="handleSubmit">
			<animated-input
				id="first_name"
				v-model="first_name"
				label="First Name"
				:has-success="!!first_name.length"
				type="text"
				autocomplete="given-name"
			>

			</animated-input>
			<animated-input
				id="last_name"
				v-model="last_name"
				label="Last Name"
				:has-success="!!last_name.length"
				type="text"
				autocomplete="family-name"
				:has-error="!!(errors.last_name && errors.last_name.length)"
				helptext="Last name is required."
				required
			></animated-input>
			<animated-input
				id="email"
				v-model="email"
				label="Email"
				:has-success="!!email.length"
				type="email"
				autocomplete="email"
				:has-error="!!(errors.email && errors.email.length)"
				:helptext="!!(errors.email && errors.email.length)?errors.email[0]:''"
				required
			></animated-input>
			<animated-input
				id="phone"
				v-model="phone"
				label="Phone Number"
				:has-success="!!phone.length"
				type="tel"
				autocomplete="tel"
				:has-error="!!(errors.phone && errors.phone.length)"
				helptext="Phone number is required."
				required
			></animated-input>
			<animated-input
				id="password"
				v-model="password"
				label="Password"
				:has-success="password.length >= 8"
				type="password"
				:has-error="!!(errors.password && errors.password.length)"
				autocomplete="new-password"
				helptext="Password must be at least 8 characters."
				required
			></animated-input>
			<animated-input
				type="textarea"
				id="store_address"
				v-model="store_address"
				label="Store Address"
				:has-success="!!store_address.length"
				:has-error="!!(errors.store_address && errors.store_address.length)"
				helptext="Store Address is required."
				required
			></animated-input>
			<big-button fullwidth>Submit</big-button>
		</form>
		<modal :active="openModel" type="box" @close="closeModal">
			<div class="mdl-box mdl-shadow--2dp">
				<h3>Thank you for your registration.</h3>
				<mdl-button @click="closeModal">Close</mdl-button>
			</div>
		</modal>
	</div>
</template>

<script>
	import modal from 'shapla-modal';
	import AnimatedInput from '../../components/AnimatedInput.vue';
	import BigButton from '../../components/BigButton.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';

	export default {
		name: "RegistrationForm",
		components: {AnimatedInput, BigButton, modal, mdlButton},
		data() {
			return {
				openModel: false,
				first_name: '',
				last_name: '',
				phone: '',
				email: '',
				password: '',
				store_address: '',
				errors: {
					first_name: [],
					last_name: [],
					phone: [],
					email: [],
					password: [],
					store_address: [],
				},
			}
		},
		methods: {
			closeModal() {
				this.openModel = false;
				window.location.href = PhoneRepairs.myaccount;
			},
			handleSubmit() {
				let self = this, $ = window.jQuery;
				$.ajax({
					method: "POST",
					url: window.Stackonet.ajaxurl,
					data: {
						action: 'manager_registration',
						first_name: self.first_name,
						last_name: self.last_name,
						phone: self.phone,
						email: self.email,
						password: self.password,
						store_address: self.store_address,
					},
					success: function () {
						self.openModel = true;
						self.first_name = '';
						self.last_name = '';
						self.phone = '';
						self.email = '';
						self.password = '';
						self.store_address = '';
						self.errors = {
							first_name: [],
							last_name: [],
							phone: [],
							email: [],
							password: [],
							store_address: []
						}
					},
					error: function (data) {
						console.log(data.responseJSON.data);
						self.errors = data.responseJSON.data
					}
				});
			}
		}
	}
</script>

<style scoped>

</style>
