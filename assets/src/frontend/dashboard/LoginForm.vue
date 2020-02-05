<template>
	<div class="stackonet-login-form-container">
		<form @submit.prevent="submitForm" class="stackonet-login-form">
			<columns multiline>
				<column :tablet="12">
					<div class="stackonet-login-form-logo">
						<img :src="logo_url" alt="">
					</div>
				</column>
				<column :tablet="12">
					<text-field
							type="text"
							v-model="user_login"
							label="Email or Username"
							autocomplete="username"
							:has-error="hasUserLoginError"
							:help-text="errors.user_login?errors.user_login[0]:''"
					/>
				</column>
				<column :tablet="12">
					<text-field
							v-model="password"
							label="Password"
							type="password"
							autocomplete="current-password"
							:has-error="hasPasswordError"
							:help-text="errors.password?errors.password[0]:''"
					/>
				</column>
				<column :tablet="12">
					<shapla-checkbox label="Remember me" v-model="remember"/>
				</column>
				<column :tablet="12">
					<shapla-button fullwidth theme="primary" :disabled="!canSubmit">Log In</shapla-button>
				</column>
			</columns>
		</form>
		<spinner :active="loading"></spinner>
	</div>
</template>

<script>
	import axios from 'axios';
	import spinner from "shapla-spinner";
	import shaplaButton from 'shapla-button';
	import shaplaCheckbox from 'shapla-checkbox';
	import textField from 'shapla-text-field';
	import {columns, column} from 'shapla-columns';

	export default {
		name: "LoginForm",
		components: {spinner, textField, shaplaButton, shaplaCheckbox, columns, column},
		data() {
			return {
				loading: false,
				remember: false,
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
				return (this.user_login.length >= 4);
			},
			hasPassword() {
				return (this.password.length >= 4);
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
				axios.post(PhoneRepairs.rest_root + '/login', {
					user_login: this.user_login,
					password: this.password,
					remember: this.remember,
				}).then(response => {
					this.loading = false;
					window.location.reload();
				}).catch(error => {
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
			margin-bottom: 30px;
		}
	}
</style>
