<template>
	<div class="terms-and-conditions-page">

		<section-title>Terms and Conditions</section-title>

		<div class="terms-and-conditions-content">
			<div class="terms-and-conditions-content-inner" v-html="terms"></div>
		</div>

		<div class="terms-and-conditions-signature-content">
			<div class="signature-cart mdl-shadow--2dp">
				<h4 class="signature-cart__title">Draw your signature on screen</h4>

				<signature-canvas @save="saveCanvas"></signature-canvas>
			</div>
		</div>

		<div class="terms-and-conditions-sign-preview" v-if="imageData.length">
			<img class="terms-and-conditions-sign-preview__image" :src="imageData" alt="">
		</div>

		<div class="terms-and-conditions-buttons">
			<mdl-button type="raised" @click="handleSubmit">Skip</mdl-button>
			<mdl-button type="raised" color="primary" @click="handleSubmit">Accept</mdl-button>
		</div>

		<section-help></section-help>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import SectionInfo from '../components/SectionInfo'
	import SectionTitle from '../components/SectionTitle'
	import SectionHelp from '../components/SectionHelp'
	import SignatureCanvas from '../components/SignatureCanvas'
	import columns from '../../shapla/columns/columns'
	import column from '../../shapla/columns/column'
	import mdlButton from '../../material-design-lite/button/mdlButton'

	export default {
		name: "termsAndConditions",
		components: {SectionInfo, SectionTitle, SectionHelp, columns, column, SignatureCanvas, mdlButton},
		data() {
			return {
				imageData: '',
				terms_and_conditions: '',
			}
		},
		computed: {
			...mapState([
				'device', 'deviceModel', 'deviceColor', 'issues', 'issueDescription', 'date', 'timeRange',
				'firstName', 'lastName', 'phone', 'emailAddress', 'addressObject', 'instructions', 'additionalAddress'
			]),
			terms() {
				let content = this.terms_and_conditions;
				content = content.replace('{{first_name}}', this.firstName);
				content = content.replace('{{last_name}}', this.lastName);

				return content;
			},
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);
			this.$store.commit('IS_THANK_YOU_PAGE', false);

			// If no models, redirect one step back
			if (!this.emailAddress.length) {
				// this.$router.push('/user-details');
			}

			this.getTermsAndConditions();
		},
		methods: {
			getTermsAndConditions() {
				let self = this;
				jQuery.ajax({
					method: 'GET',
					url: PhoneRepairs.ajaxurl,
					data: {
						action: 'terms_and_conditions',
					},
					success: function (response) {
						self.terms_and_conditions = response;
					}
				});
			},
			saveCanvas(imageData) {
				this.imageData = imageData;
			},
			handleSubmit() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);

				window.jQuery.ajax({
					method: 'POST',
					url: window.Stackonet.ajaxurl,
					data: {
						action: 'confirm_appointment',
						product_id: self.device.product_id,
						device_id: self.device.id,
						device_title: self.device.device_title,
						device_model: self.deviceModel.title,
						device_color: self.deviceColor.title,
						issues: self.issues,
						issue_description: self.issueDescription,
						date: self.date,
						time_range: self.timeRange,
						first_name: self.firstName,
						last_name: self.lastName,
						phone: self.phone,
						email: self.emailAddress,
						address: self.addressObject,
						instructions: self.instructions,
						additional_address: self.additionalAddress,
						signature: self.imageData,
					},
					success: function (response) {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.$router.push('/thank-you');
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
	.terms-and-conditions-page {
		max-width: 700px;
		margin-left: auto;
		margin-right: auto;

		.signature-cart {
			background: white;
			border-radius: 6px;
			margin-left: auto;
			margin-right: auto;
			max-width: 500px;
			padding: 1rem;

			&__title {
				font-size: 16px;
				margin-bottom: 1.5em;
				text-align: center;
			}
		}

		.terms-and-conditions-signature-content {
			margin-top: 3rem;
			margin-bottom: 3rem;
		}
	}

	.terms-and-conditions-content {
		background: white;
		padding: 1rem;
		border-radius: 6px;

		&-inner {
			max-height: 200px;
			display: block;
			overflow-y: auto;
			overflow-x: hidden;
		}
	}

	.terms-and-conditions-buttons {
		display: flex;
		justify-content: flex-end;

		.mdl-button:not(:last-child) {
			margin-right: 1rem;
		}
	}

	.terms-and-conditions-sign-preview {
		display: flex;
		justify-content: flex-end;
		padding: 1rem 0;

		&__image {
			border: 1px solid rgba(#000, 0.2);
			max-height: 75px;
			width: auto;
		}
	}
</style>
