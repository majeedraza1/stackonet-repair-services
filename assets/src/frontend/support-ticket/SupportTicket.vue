<template>
	<div class="support-ticket-container">
		<div class="support-ticket-content">
			<router-view></router-view>
		</div>
		<spinner :active="loading"></spinner>
		<notification ref="notify"></notification>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import notification from 'shapla-notifications';
	import spinner from "shapla-spinner";

	export default {
		name: "SupportTicket",
		components: {notification, spinner},
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
