<template>
	<div class="unsupported-zip-code-wrapper">
		<div class="unsupported-zip-code-image-wrapper">
			<img :src="icons.unsupportedIssue" alt="Unsupported Zip" width="65" height="125">
		</div>
		<h1>Sorry, we currently do not offer this service in {{zipCode}}</h1>
		<div class="unsupported-zip-code-subtext-wrapper">
			<p>We will let you know a soon as this service is available in your area!</p>
			<p>Enter your email below:</p>
		</div>
		<div class="unsupported-zip-code-input-wrapper">
			<animated-input type="email" label="Type in your email" v-model="email"
							autocomplete="email"></animated-input>
		</div>
		<div class="unsupported-zip-code-button-wrapper">
			<big-button :disabled="!isValidEmail" @click="handleNotifyMe">Notify Me</big-button>
		</div>

		<section-help></section-help>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';
	import AnimatedInput from '../../components/AnimatedInput.vue';
	import SectionHelp from '../components/SectionHelp'
	import {mapState} from 'vuex';

	export default {
		name: "unsupportedZipCode",
		components: {AnimatedInput, BigButton, SectionHelp},
		data() {
			return {
				email: '',
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', false);
			this.$store.commit('IS_THANK_YOU_PAGE', true);

			// If no models, redirect one step back
			if (!this.hasZipCode) {
				this.$router.push('/zip-code');
			} else {
				this.saveInitialData();
			}

			this.$store.dispatch('checkoutAnalysis', {
				id: this.checkoutAnalysisId,
				step: 'unsupported_zip_code',
				step_data: {zip_code: this.zipCode}
			});
		},
		computed: {
			...mapState(['zipCode', 'areaRequestId', 'device', 'deviceModel', 'deviceColor', 'checkoutAnalysisId']),
			icons() {
				return window.Stackonet.icons;
			},
			hasZipCode() {
				return !!(this.zipCode && this.zipCode.length);
			},
			isValidEmail() {
				return !!(this.email.length && this.validateEmail(this.email));
			}
		},
		methods: {
			validateEmail(email) {
				let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(String(email).toLowerCase());
			},
			saveInitialData() {
				let self = this, $ = window.jQuery;
				$.ajax({
					method: 'POST',
					url: window.Stackonet.ajaxurl,
					data: {
						action: 'create_request_areas',
						zip_code: self.zipCode,
						device_title: self.device.device_title,
						device_model: self.deviceModel.title,
						device_color: self.deviceColor.title,
					},
					success: function (response) {
						if (response.data.id) {
							self.$store.commit('SET_AREA_REQUEST_ID', response.data.id);
						}
					}
				});
			},
			handleNotifyMe() {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);

				$.ajax({
					method: 'POST',
					url: window.Stackonet.ajaxurl,
					data: {
						action: 'create_request_areas',
						id: self.areaRequestId,
						email: self.email,
						zip_code: self.zipCode,
						device_title: self.device.device_title,
						device_model: self.deviceModel.title,
						device_color: self.deviceColor.title,
					},
					success: function (response) {
						self.$store.commit('SET_LOADING_STATUS', false);


						self.$store.dispatch('updateCheckoutAnalysis', {
							step: 'unsupported_zip_thank_you',
							step_data: {unsupported_zip_code: {email: self.email}}
						});

						self.$router.push('/thankyou');
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			}
		}
	}
</script>

<style lang="scss">
	.unsupported-zip-code-wrapper {
		flex: 1 1;
		height: 100%;
		background: #eff2f5;
		display: flex;
		flex-direction: column;
		padding: 0;
		overflow: hidden;
	}

	.unsupported-zip-code-image-wrapper {
		margin: 50px auto 30px;
	}

	.unsupported-zip-code-wrapper h1 {
		color: #000;
		font-size: 24px;
		line-height: 27px;
		text-align: center;
		margin-bottom: 30px;
	}

	.unsupported-zip-code-input-wrapper {
		margin: 0 auto 30px;
	}

	.unsupported-zip-code-input-wrapper,
	.unsupported-zip-code-input-wrapper input {
		height: 64px;
		width: 100%;
		max-width: 540px;
	}

	.unsupported-zip-code-input-wrapper input {
		border-radius: 6px;
		background-color: #fff;
		border: 0;
		box-sizing: border-box;
		padding-left: 20px;
		color: #383e42;
		font-size: 20px;
		line-height: 19px;
	}

	.unsupported-zip-code-subtext-wrapper {
		margin-bottom: 20px;

		p {
			color: #9b9b9b;
			font-size: 16px;
			line-height: 24px;
			text-align: center;
		}
	}

	.unsupported-zip-code-button-wrapper {
		max-width: 540px;
		margin: 13px auto 0;
		width: 100%;
	}
</style>
