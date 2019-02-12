<template>
	<div class="unsupported-zip-code-wrapper">
		<div class="unsupported-zip-code-image-wrapper">
			<svg width="108px" height="85px" version="1.1" xmlns="http://www.w3.org/2000/svg"
				 xmlns:xlink="http://www.w3.org/1999/xlink">
				<use xlink:href="#unsupported-zip-code-phone"></use>
			</svg>
		</div>
		<h1>Sorry, your location is not within our service area</h1>
		<div class="unsupported-zip-code-input-wrapper">
			<input type="email" placeholder="Type in your email" value="">
		</div>
		<div class="unsupported-zip-code-subtext-wrapper">
			<p>We currently not supporting {{zipCode}}. Please try again, or
				sign<br>up to be first to know when we expand.</p>
		</div>
		<div class="unsupported-zip-code-button-wrapper">
			<big-button :disabled="true">Notify Me</big-button>
		</div>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';

	export default {
		name: "unsupportedZipCode",
		components: {BigButton},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', false);

			// If no models, redirect one step back
			if (!this.hasZipCode) {
				this.$router.push('/zip-code');
			}
		},
		computed: {
			zipCode() {
				return this.$store.state.zipCode;
			},
			hasZipCode() {
				return !!(this.zipCode && this.zipCode.length);
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
		padding: 0 50px;
		overflow: scroll;
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
		margin: 0 auto;
		margin-bottom: 30px;
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
		-webkit-box-sizing: border-box;
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
		width: 520px;
		margin: 13px auto 0;
	}
</style>
