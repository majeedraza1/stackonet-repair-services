<template>
	<div>
		<form action="#" method="post" accept-charset="UTF-8" @submit.prevent="handleSubmit">
			<animated-input
				id="email"
				v-model="email"
				label="Email"
				:has-success="!!email.length"
				type="email"
				autocomplete="email"
				:has-error="!!(errors.email && errors.email.length)"
				:helptext="errors.email[0]"
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
				helptext="Password is required."
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
			<big-button>Submit</big-button>
		</form>
		<mdl-modal :active="openModel" type="box" @close="closeModal">
			<div class="mdl-box mdl-shadow--2dp">
				<h3>Thank you for your registration.</h3>
				<mdl-button @click="closeModal">Close</mdl-button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import AnimatedInput from '../../components/AnimatedInput.vue';
	import BigButton from '../../components/BigButton.vue';
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';

	export default {
		name: "RegistrationForm",
		components: {AnimatedInput, BigButton, mdlModal, mdlButton},
		data() {
			return {
				openModel: false,
				email: '',
				password: '',
				store_address: '',
				errors: {
					email: [],
					password: [],
					store_address: [],
				},
			}
		},
		methods: {
			closeModal() {
				this.openModel = false;
				window.location.href = PhoneRepairs.login_url;
			},
			handleSubmit() {
				let self = this, $ = window.jQuery;
				$.ajax({
					method: "POST",
					url: window.Stackonet.ajaxurl,
					data: {
						action: 'manager_registration',
						email: self.email,
						password: self.password,
						store_address: self.store_address,
					},
					success: function () {
						self.openModel = true;
						self.email = '';
						self.password = '';
						self.store_address = '';
						self.errors = {email: [], password: [], store_address: []}
					},
					error: function (data) {
						self.errors = data.responseJSON.data
					}
				});
			}
		}
	}
</script>

<style scoped>

</style>
