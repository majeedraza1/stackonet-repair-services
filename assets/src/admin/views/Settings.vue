<template>
	<div>
		<h1 class="title">Settings page</h1>

		<div>
			<mdl-tabs :vertical="false">
				<mdl-tab name="General" selected>General Content</mdl-tab>
				<mdl-tab name="Service Times">Service Times Content</mdl-tab>
				<mdl-tab name="API Settings">API Settings</mdl-tab>
			</mdl-tabs>
		</div>

	</div>
</template>

<script>
	import mdlTabs from '../../material-design-lite/tabs/mdlTabs.vue';
	import mdlTab from '../../material-design-lite/tabs/mdlTab.vue';

	export default {
		name: "Settings",
		components: {mdlTabs, mdlTab},
		data() {
			return {}
		},
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

<style lang="scss">
	.mdl-tabs.is-upgraded .mdl-tabs__tab.is-active {
		box-shadow: none;
	}
</style>
