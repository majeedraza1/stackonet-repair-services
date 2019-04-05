<template>
	<div class="rent-a-center-container">
		<router-view></router-view>
		<mdl-snackbar :options="snackbar"></mdl-snackbar>
		<div class="rent-a-center-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import mdlSnackbar from '../../material-design-lite/snackbar/mdlSnackbar.vue';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner.vue';

	export default {
		name: "RentACenter",
		components: {mdlSnackbar, mdlSpinner},
		data() {
			return {}
		},
		computed: {
			...mapState(['snackbar']),
			loading() {
				return this.$store.state.loading;
			}
		}
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/ripple/ripple";

	.rent-a-center-container {
		position: relative;
		box-sizing: border-box;

		* {
			box-sizing: border-box;
		}
	}

	.rent-a-center-loader {
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
			position: fixed;
			z-index: 100;
		}
	}
</style>
