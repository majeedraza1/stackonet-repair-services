<template>
	<div>
		<div class="zip-code-wrapper">
			<div class="step-nav-page-wrapper">
				<div class="step-nav-wrapper"><span class="step-nav-title">What's your zip code?</span></div>
			</div>
			<form action="#" @submit.prevent="handleSubmit">
				<div class="zip-code-input-wrapper">
					<input type="tel" placeholder="Please enter zip code" v-model="tempZipCode">
				</div>
				<div class="zip-code-continue-wrapper">
					<big-button @click="handleSubmit" :disabled="!isSubmitActive">Continue</big-button>
				</div>
			</form>
		</div>

		<div class="testimonial-carousel-wrapper">
			<testimonial-carousel :items="testimonials"></testimonial-carousel>
		</div>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';
	import TestimonialCarousel from '../TestimonialCarousel';
	import {mapState} from 'vuex';

	export default {
		name: "zipCode",
		components: {BigButton, TestimonialCarousel},
		data() {
			return {
				tempZipCode: '',
				serviceArea: [],
			}
		},
		mounted() {
			this.serviceArea = window.Stackonet.serviceArea;
			this.$store.commit('SET_LOADING_STATUS', false);

			// If no models, redirect one step back
			if (!this.hasDeviceColor) {
				this.$router.push('/device-color');
			}

			if (!this.isTestimonials) {
				this.$store.dispatch('fetchAcceptedTestimonial');
			}
		},
		computed: {
			...mapState(['deviceColor', 'zipCode', 'testimonials']),
			hasDeviceColor() {
				return !!(this.deviceColor && this.deviceColor.color);
			},
			isSubmitActive() {
				return this.tempZipCode && this.tempZipCode.length >= 3;
			},
			isTestimonials() {
				return this.testimonials && this.testimonials.length >= 1;
			},
			isValidArea() {
				let value = parseInt(this.tempZipCode);
				return this.serviceArea.indexOf(value) !== -1;
			}
		},
		methods: {
			updateZipCode(event) {
				this.tempZipCode = event.target.value ? parseInt(event.target.value) : '';
			},
			handleSubmit() {
				if (this.isValidArea) {
					this.$store.commit('SET_ZIP_CODE', this.tempZipCode);
					this.$router.push('/screen-cracked');
				} else {
					this.$store.commit('SET_ZIP_CODE', this.tempZipCode);
					this.$router.push('/unsupported-zip-code');
				}
			}
		}
	}
</script>

<style lang="scss">
	.zip-code-wrapper {
		width: 100%;
		padding: 0 10px 30px;
	}

	.testimonial-carousel-wrapper {
		margin: 3rem auto;
	}

	.step-nav-wrapper {
		display: flex;
		justify-content: space-around;
		margin: 30px 0;
	}

	.step-nav-title {
		flex: 1 1;
		text-align: center;
		font-size: 22px;
		color: #3d4248;
	}

	.zip-code-input-wrapper {
		margin: 0 auto 10px;
		border-radius: 6px;

		input[type=tel] {
			font-size: 20px;
			line-height: 1.5;
			background: none;
			border: none;
			outline: none;
			padding-left: 20px;
			color: #383e42;
			background: #fff;
			border-radius: 6px;
			box-sizing: border-box;
		}
	}

	.zip-code-continue-wrapper {
		margin: 13px auto 0;
	}
</style>
