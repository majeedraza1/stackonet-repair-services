<template>
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
</template>

<script>
	import BigButton from '../../components/BigButton.vue';

	export default {
		name: "zipCode",
		components: {BigButton},
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
		},
		computed: {
			deviceColor() {
				return this.$store.state.deviceColor;
			},
			hasDeviceColor() {
				return !!(this.deviceColor && this.deviceColor.color);
			},
			zip_code() {
				return this.$store.state.zipCode;
			},
			isSubmitActive() {
				return this.tempZipCode && this.tempZipCode.length >= 3;
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
		width: 520px;
		margin: 0 auto 10px;
		border-radius: 6px;

		input {
			font-size: 20px;
			width: 520px;
			height: 64px;
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
		width: 520px;
		margin: 13px auto 0;
	}
</style>
