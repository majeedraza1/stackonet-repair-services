<template>
	<div class="support-ticket-container">
		<div class="support-ticket-content">
			<router-view></router-view>
		</div>
		<div class="support-ticket-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner';

	export default {
		name: "SupportTicket",
		components: {mdlSpinner},
		data() {
			return {
				loading: false,
			}
		}
	}
</script>

<style scoped>

</style>
