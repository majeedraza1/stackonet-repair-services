<template>
	<div>
		<h1 class="title">Settings page</h1>

		<div>
			<mdl-tabs :vertical="false">
				<mdl-tab name="General">General Content</mdl-tab>
				<mdl-tab name="Service Times">Service Times Content</mdl-tab>
				<mdl-tab name="Integrations" selected>
					<h2 class="title">Google Map</h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="google_map_key">API key</label>
							</th>
							<td>
								<input type="text" id="google_map_key" class="regular-text"
									   v-model="settings.google_map_key">
								<p class="description">Enter google Map API key</p>
							</td>
						</tr>
					</table>
				</mdl-tab>
			</mdl-tabs>
		</div>
		<p class="submit">
			<input type="submit" value="Save Changes" class="button button-primary" @click="saveSettings">
		</p>
	</div>
</template>

<script>
	import mdlTabs from '../../material-design-lite/tabs/mdlTabs.vue';
	import mdlTab from '../../material-design-lite/tabs/mdlTab.vue';

	export default {
		name: "Settings",
		components: {mdlTabs, mdlTab},
		data() {
			return {
				settings: {
					google_map_key: ''
				},
			}
		},
		computed: {
			loading() {
				return this.$store.state.loading;
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.settings = this.$store.state.settings;
			this.fetchSettings();
		},
		methods: {
			fetchSettings() {
				this.settings = window.stackonetSettings.settings;
			},
			saveSettings() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', false);
				$.ajax({
					method: 'POST',
					url: ajaxurl,
					data: {
						action: 'update_repair_services_settings',
						settings: self.settings,
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_SETTINGS', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
						self.$root.$emit('show-snackbar', {
							message: 'Settings has been saved successfully.',
						});
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			}
		}
	}
</script>

<style lang="scss">
	.mdl-tabs.is-upgraded .mdl-tabs__tab.is-active {
		box-shadow: none;
	}
</style>
