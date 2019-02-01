<template>
	<div>
		<router-view></router-view>
		<mdl-snackbar align-start></mdl-snackbar>
	</div>
</template>

<script>
	import mdlSnackbar from '../material-design-lite/snackbar/mdlSnackbar.vue';

	export default {
		name: 'App',
		components: {mdlSnackbar},
	}
</script>

<style>

</style>
