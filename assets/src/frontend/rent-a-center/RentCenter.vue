<template>
	<div class="rent-a-center-container">
		<div class="rent-a-center-content">
			<router-view></router-view>
		</div>
		<div class="rent-a-center-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
		<mdl-snackbar :options="snackbar"></mdl-snackbar>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner.vue';
	import mdlSnackbar from '../../material-design-lite/snackbar/mdlSnackbar';

	export default {
		name: "RentCenter",
		components: {mdlSpinner, mdlSnackbar},
		computed: {
			...mapState(['loading', 'snackbar']),
		}
	}
</script>

<style scoped>

</style>
