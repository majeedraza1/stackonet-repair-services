<template>
	<div class="admin-support-tickets-container">
		<router-view></router-view>
		<mdl-snackbar :options="snackbar"></mdl-snackbar>
		<div class="admin-support-tickets-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import MdlSnackbar from "../../material-design-lite/snackbar/mdlSnackbar";
	import MdlSpinner from "../../material-design-lite/spinner/mdlSpinner";

	export default {
		name: "SupportTicket",
		components: {MdlSpinner, MdlSnackbar},
		computed: {
			...mapState(['snackbar', 'loading']),
		}
	}
</script>

<style lang="scss">
	.admin-support-tickets-container {
		position: relative;
		box-sizing: border-box;

		* {
			box-sizing: border-box;
		}
	}

	.admin-support-tickets-loader {
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
