<template>
	<div class="rent-a-center-container">
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
		name: "RentACenter",
		components: {notification, spinner},
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
</style>
