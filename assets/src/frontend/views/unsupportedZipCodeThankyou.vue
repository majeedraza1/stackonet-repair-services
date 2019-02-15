<template>
	<div class="thank-you-content">
		<svg class="thank-you-check" width="55" height="55" version="1.1" xmlns="http://www.w3.org/2000/svg"
			 xmlns:xlink="http://www.w3.org/1999/xlink">
			<use xlink:href="#icon-success"></use>
		</svg>
		<div class="title-wrapper theme-dark centered small">
			<h1 class="h1-headline">Thank you!</h1>
		</div>
		<div class="theme-dark centered">
			<p class="body-text-big">
				We've received your request.<br>
				When we extend our service to this area, we will inform you.
			</p>
		</div>
		<div class="thank-you-button-wrapper">
			<big-button @click="BackToHome">Back to home</big-button>
		</div>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';

	export default {
		name: "unsupportedZipCodeThankyou",
		components: {BigButton},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', false);
		},
		methods: {
			BackToHome() {
				window.location.href = window.Stackonet.home_url;
			}
		}
	}
</script>

<style lang="scss">
	.thank-you-button-wrapper {
		max-width: 500px;
		margin: 50px auto;
	}
</style>
