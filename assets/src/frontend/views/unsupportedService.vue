<template>
	<div class="unsupported-zip-code-wrapper">
		<div class="unsupported-zip-code-image-wrapper">
			<svg width="108px" height="85px" version="1.1" xmlns="http://www.w3.org/2000/svg"
				 xmlns:xlink="http://www.w3.org/1999/xlink">
				<use xlink:href="#unsupported-zip-code-phone"></use>
			</svg>
		</div>
		<h1>Sorry, we not supporting any more issues.</h1>
		<div class="unsupported-zip-code-input-wrapper">
			<input type="email" placeholder="Type in your email" value="">
		</div>
		<div class="unsupported-zip-code-subtext-wrapper">
			<p>We currently not supporting any more issues. Please try
				again, or sign<br>up to be first to know when we expand.</p>
		</div>
		<div class="unsupported-zip-code-button-wrapper">
			<button disabled="" class="">Notify Me</button>
		</div>

		<section-help></section-help>
	</div>
</template>

<script>
	import SectionHelp from '../components/SectionHelp'

	export default {
		name: "unsupportedService",
		components: {SectionHelp},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', false);
			this.$store.commit('IS_THANK_YOU_PAGE', true);
		},
	}
</script>

<style scoped>

</style>
