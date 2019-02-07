<template>
	<div class="repair-services-device">
		<h1 class="wp-heading-inline">Device</h1>
	</div>
</template>

<script>
	export default {
		name: "Device",
		computed: {
			loading() {
				return this.$store.state.loading;
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false)
		}
	}
</script>

<style scoped>

</style>
