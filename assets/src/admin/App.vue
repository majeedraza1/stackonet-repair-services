<template>
	<div class="repair-services-container">
		<router-view></router-view>
		<spinner :active="loading"></spinner>
		<notification ref="notify"></notification>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import notification from 'shapla-notifications';
	import spinner from "shapla-spinner";

	export default {
		name: 'App',
		components: {notification, spinner},
		data() {
			return {}
		},
		computed: {
			...mapState(['loading']),
		}
	}
</script>

<style lang="scss">
	@import "../material-design-lite/ripple/ripple";

	.repair-services-container {
		position: relative;
		box-sizing: border-box;

		* {
			box-sizing: border-box;
		}
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
			position: fixed;
			z-index: 100;
		}
	}
</style>
