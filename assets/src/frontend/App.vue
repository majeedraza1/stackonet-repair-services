<template>
	<div class="repair-services-container">
		<router-view></router-view>
		<cart></cart>
		<mdl-snackbar align-start></mdl-snackbar>
		<div class="repair-services-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import mdlSnackbar from '../material-design-lite/snackbar/mdlSnackbar.vue';
	import mdlSpinner from '../material-design-lite/spinner/mdlSpinner.vue';
	import cart from './views/cart.vue';

	export default {
		name: 'App',
		components: {mdlSnackbar, mdlSpinner, cart},
		computed: {
			loading() {
				return this.$store.state.loading;
			}
		}
	}
</script>

<style lang="scss">
	.repair-services-container {
		background: #f5f5f5;
		position: relative;
	}

	.repair-services-loader {
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		display: none;
		align-items: center;
		justify-content: center;
		background: rgba(#fff, 0.5);

		&.is-active {
			display: flex;
		}
	}
</style>
