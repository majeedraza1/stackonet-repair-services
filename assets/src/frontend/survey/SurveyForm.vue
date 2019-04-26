<template>
	<div class="stackonet-survey-form">
		<h2 class="stackonet-section-title">Survey Form</h2>

		<div class="form-field">
			<mdl-radio
				label="Product or service does not pertain to them."
				value="not-pertain"
				v-model="device_status"
			></mdl-radio>
		</div>
		<div class="form-field">
			<mdl-radio
				label="Device can be fixed at an affordable price."
				value="affordable"
				v-model="device_status"
			></mdl-radio>
		</div>
		<div class="form-field">
			<mdl-radio
				label="Device cannot be fixed at an affordable price."
				value="not-affordable"
				v-model="device_status"
			></mdl-radio>
		</div>

		<big-button @click="handleSubmit">Submit</big-button>

		<div class="loading-container" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import AnimatedInput from '../../components/AnimatedInput';
	import BigButton from '../../components/BigButton';
	import mdlRadio from '../../material-design-lite/radio/mdlRadio';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner';

	export default {
		name: "SurveyForm",
		components: {AnimatedInput, BigButton, mdlRadio, mdlSpinner},
		data() {
			return {
				loading: true,
				device_status: '',
			}
		},
		mounted() {
			this.loading = false;
		},
		methods: {
			handleSubmit() {
				this.loading = true;
				setTimeout((self) => {
					self.loading = false;
				}, 1000, this)
			}
		}
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/ripple/ripple";

	.stackonet-survey-form {
		margin: 100px auto;
		max-width: 600px;
		position: relative;

		.form-field {
			margin-bottom: 1rem;
		}

		.loading-container {
			&.is-active {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				display: flex;
				justify-content: center;
				align-items: center;
				background: rgba(#000, 0.5);
				z-index: 10;
			}
		}
	}

	.stackonet-section-title {
		text-align: center;
		margin-bottom: 1em;
	}
</style>
