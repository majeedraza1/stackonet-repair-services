<template>
	<div>
		<h1 class="title">Settings page</h1>

		<div>
			<mdl-tabs :vertical="false">
				<mdl-tab name="General Settings" selected>
					<h2 class="title">General Settings</h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="support_email">Support Email</label>
							</th>
							<td>
								<input id="support_email" type="email" class="regular-text"
									   v-model="settings.support_email">
								<p class="description">Support email will use in email.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="support_phone">Support Phone</label>
							</th>
							<td>
								<input id="support_phone" type="tel" class="regular-text"
									   v-model="settings.support_phone">
								<p class="description">Support phone will use in email.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="business_address">Business Address</label>
							</th>
							<td>
								<textarea id="business_address" cols="2" class="regular-text"
										  v-model="settings.business_address"></textarea>
								<p class="description">Business address will use in email.</p>
							</td>
						</tr>
					</table>
				</mdl-tab>
				<mdl-tab name="Service Times">
					<h2 class="title">Service Times</h2>
					<table class="form-table">
						<tr v-for="day in days_in_week">
							<th scope="row">
								<label :for="day" v-text="day"></label>
							</th>
							<td>
								<span>Start time</span>
								<input type="time" class="time-picker" v-model="settings.service_times[day].start_time">
								<span>End time</span>
								<input type="time" class="time-picker" v-model="settings.service_times[day].end_time">
							</td>
						</tr>
					</table>
					<h2 class="title">Public Holidays</h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label>Public holidays list</label>
							</th>
							<td>
								<template v-for="holiday in settings.holidays_list">
									<delete @click="deleteHoliday(holiday)"></delete>
									<input type="date" v-model="holiday.date" placeholder="yyyy-mm-dd">
									<label>Optional Note</label>
									<input type="text" class="regular-text" v-model="holiday.note">
									<br>
								</template>
								<p>
									<button class="button" @click="addNewHoliday">Add New Holiday</button>
								</p>
								<p class="description">Public holidays excluding past days.</p>
							</td>
						</tr>
					</table>
				</mdl-tab>
				<mdl-tab name="Integrations">
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
	import Delete from '../../components/Delete.vue';

	export default {
		name: "Settings",
		components: {mdlTabs, mdlTab, Delete},
		data() {
			return {
				settings: {
					support_phone: '',
					support_email: '',
					business_address: '',
					google_map_key: '',
					service_times: {
						Monday: {start_time: '', end_time: '',},
						Tuesday: {start_time: '', end_time: '',},
						Wednesday: {start_time: '', end_time: '',},
						Thursday: {start_time: '', end_time: '',},
						Friday: {start_time: '', end_time: '',},
						Saturday: {start_time: '', end_time: '',},
						Sunday: {start_time: '', end_time: '',},
					},
					holidays_list: [
						{date: '', note: ''}
					],
				},
				days_in_week: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
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
			addNewHoliday() {
				this.settings.holidays_list.push({date: ''});
			},
			deleteHoliday(holiday) {
				if (window.confirm('Are you sure?')) {
					this.settings.holidays_list.splice(this.settings.holidays_list.indexOf(holiday), 1);
				}
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
