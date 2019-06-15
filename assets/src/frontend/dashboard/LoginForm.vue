<template>
	<div class="stackonet-login-form-container">
		<form @submit.prevent="submitForm" class="stackonet-login-form">
			<div class="stackonet-login-form-logo">
				<img :src="logo_url" alt="">
			</div>
			<animated-input
				type="text"
				v-model="user_login"
				label="Email or Username"
				autocomplete="username"
				:has-error="hasUserLoginError"
				:helptext="errors.user_login?errors.user_login[0]:''"
			></animated-input>
			<animated-input
				v-model="password"
				label="Password"
				type="password"
				autocomplete="current-password"
				:has-error="hasPasswordError"
				:helptext="errors.password?errors.password[0]:''"
			></animated-input>
			<big-button fullwidth :disabled="!canSubmit">Log In</big-button>
		</form>
		<div class="stackonet-loading-container" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import axios from 'axios';
	import AnimatedInput from "../../components/AnimatedInput";
	import BigButton from "../../components/BigButton";
	import MdlSpinner from "../../material-design-lite/spinner/mdlSpinner";

	export default {
		name: "LoginForm",
		components: {MdlSpinner, BigButton, AnimatedInput},
		data() {
			return {
				loading: false,
				user_login: '',
				password: '',
				errors: {
					user_login: [],
					password: [],
				}
			}
		},
		computed: {
			logo_url() {
				return SupportTickets.logo_url;
			},
			hasUserLogin() {
				return !!(this.user_login.length >= 4);
			},
			hasPassword() {
				return !!(this.password.length >= 4);
			},
			canSubmit() {
				return this.hasPassword && this.hasUserLogin;
			},
			hasUserLoginError() {
				return !!(this.errors.user_login && this.errors.user_login.length);
			},
			hasPasswordError() {
				return !!(this.errors.password && this.errors.password.length);
			}
		},
		methods: {
			submitForm() {
				this.loading = true;
				axios
					.post(PhoneRepairs.rest_root + '/login', {
						user_login: this.user_login,
						password: this.password
					})
					.then(response => {
						this.loading = false;
						window.location.reload();
					})
					.catch(error => {
						this.loading = false;
						if (error.response) {
							this.errors = error.response.data.errors;
						}
					})
			},
			validateEmail(email) {
				let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(String(email).toLowerCase());
			},
		}
	}
</script>

<style lang="scss">
	.stackonet-login-form-container {
		display: flex;
		justify-content: center;
		align-items: center;
		position: fixed;
		width: 100%;
		height: 100%;
	}

	.stackonet-loading-container {
		&.is-active {
			background: rgba(#fff, 0.75);
			display: flex;
			justify-content: center;
			align-items: center;
			position: fixed;
			width: 100%;
			height: 100%;
		}
	}

	form.stackonet-login-form {
		width: 320px;

		.stackonet-login-form-logo {
			display: flex;
			justify-content: center;
			align-items: center;
			margin-bottom: 50px;
		}
	}
</style>
