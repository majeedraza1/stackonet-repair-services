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
						<tr>
							<th scope="row">
								<label for="reschedule_page_id">Re-Schedule Page</label>
							</th>
							<td>
								<input type="text" id="reschedule_page_id" class="regular-text"
									   v-model="settings.reschedule_page_id"/>
								<p class="description">Reschedule page.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="payment_page_id">Payment form page</label>
							</th>
							<td>
								<input type="text" id="payment_page_id" class="regular-text"
									   v-model="settings.payment_page_id"/>
								<p class="description">Payment form page.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="payment_thank_you_page_id">After payment thank you page</label>
							</th>
							<td>
								<input type="text" id="payment_thank_you_page_id" class="regular-text"
									   v-model="settings.payment_thank_you_page_id"/>
								<p class="description">After payment thank you page.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="terms_and_conditions">Terms and conditions Page</label>
							</th>
							<td>
								<input type="text" id="terms_and_conditions" class="regular-text"
									   v-model="settings.terms_and_conditions_page_id"/>
								<p class="description">Choose terms and conditions page.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="order_reminder_minutes">Order Reminder Time</label>
							</th>
							<td>
								<input type="number" id="order_reminder_minutes" class="regular-text"
									   v-model="settings.order_reminder_minutes"/>
								<p class="description">Enter order reminder time in minutes. Example, enter 1440 for For
									24 hours.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="number_of_alternate_places">Timeline alternative places</label>
							</th>
							<td>
								<input type="number" id="number_of_alternate_places" class="regular-text"
									   v-model="settings.number_of_alternate_places"/>
								<p class="description">Set number of alternative places should show on timeline
									dropdown.</p>
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
					<h2 class="title">Time Settings</h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="minimum_time_difference">Minimum time difference</label>
							</th>
							<td>
								<input type="number" id="minimum_time_difference"
									   v-model="settings.minimum_time_difference">
								<p class="description">Set minimum time interval in minutes between order time and
									service request time.</p>
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
					<h2 class="title">Dropbox</h2>
					<p>
						To get dropbox client id and secret, go to
						<a href="https://www.dropbox.com/developers" target="_blank">Dropbox App Console</a> and choose
						an app that you want to use.
					</p>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="dropbox_client_id">App key</label>
							</th>
							<td>
								<input type="text" id="dropbox_client_id" class="regular-text"
									   v-model="settings.dropbox_client_id">
								<p class="description">Enter dropbox client id.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="dropbox_client_secret">App secret</label>
							</th>
							<td>
								<input type="text" id="dropbox_client_secret" class="regular-text"
									   v-model="settings.dropbox_client_secret">
								<p class="description">Enter dropbox client Secret.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="dropbox_access_token">Access Token</label>
							</th>
							<td>
								<input type="text" id="dropbox_access_token" class="regular-text"
									   v-model="settings.dropbox_access_token">
								<p class="description"> Enter dropbox Access Token. </p>
							</td>
						</tr>
					</table>


					<h2 class="title">IP Stack</h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="ipdata_api_key">API key</label>
							</th>
							<td>
								<input type="text" id="ipdata_api_key" class="regular-text"
									   v-model="settings.ipdata_api_key">
								<p class="description">Enter google Map API key</p>
							</td>
						</tr>
					</table>

					<h2 class="title">Square Payment</h2>
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="square_payment_application_id">Application ID</label>
							</th>
							<td>
								<input type="text" id="square_payment_application_id" class="regular-text"
									   v-model="settings.square_payment_application_id">
								<p class="description">Enter Square payment application id.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="square_payment_access_token">Access Token</label>
							</th>
							<td>
								<input type="text" id="square_payment_access_token" class="regular-text"
									   v-model="settings.square_payment_access_token">
								<p class="description">Enter Square payment access token.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="square_payment_location_id">Location Id</label>
							</th>
							<td>
								<input type="text" id="square_payment_location_id" class="regular-text"
									   v-model="settings.square_payment_location_id">
								<p class="description">Enter Square payment location id.</p>
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
                    reschedule_page_id: '',
                    payment_page_id: '',
                    terms_and_conditions_page_id: '',
                    order_reminder_minutes: '',
                    ipdata_api_key: '',
                    google_map_key: '',
                    dropbox_client_id: '',
                    dropbox_client_secret: '',
                    dropbox_access_token: '',
                    minimum_time_difference: '',
                    payment_thank_you_page_id: '',
                    square_payment_application_id: '',
                    square_payment_access_token: '',
                    square_payment_location_id: '',
                    number_of_alternate_places: '',
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
                        settings: this.settings,
                    },
                    success: (response) => {
                        if (response.data) {
                            this.$store.commit('SET_SETTINGS', response.data);
                        }
                        this.$store.commit('SET_LOADING_STATUS', false);
                        this.$store.commit('SET_NOTIFICATION', {
                            message: 'Settings has been saved successfully.',
                            type: 'success',
                        });
                    },
                    error: () => {
                        this.$store.commit('SET_LOADING_STATUS', false);
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
