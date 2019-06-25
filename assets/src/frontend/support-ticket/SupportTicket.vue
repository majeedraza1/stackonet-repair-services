<template>
	<div class="support-ticket-container">
		<div class="support-ticket-content">
			<router-view></router-view>
		</div>
		<mdl-snackbar></mdl-snackbar>
		<div class="support-ticket-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner';
	import MdlSnackbar from "../../material-design-lite/snackbar/mdlSnackbar";

	export default {
		name: "SupportTicket",
		components: {MdlSnackbar, mdlSpinner},
		computed: {
			...mapState(['loading']),
		}
	}
</script>

<style lang="scss">
	.support-ticket-container {
		position: relative;

		.support-ticket-loader.is-active {
			display: flex;
			z-index: 100000;
			width: 100%;
			height: 100%;
			position: fixed;
			background: rgba(#fff, 0.6);
			top: 0;
			left: 0;
			justify-content: center;
			align-items: center;
		}
	}
</style>
