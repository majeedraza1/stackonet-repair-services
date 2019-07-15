import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		title: 'Dashboard',
		addresses: [],
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_TITLE(state, title) {
			state.title = title;
		},
		SET_ADDRESSES(state, addresses) {
			state.addresses = addresses;
		},
	},

	// Same as Vue methods
	actions: {
		interval_seconds(hours, minute) {
			return (hours * 60 * 60 * 1000) + (minute * 60 * 1000);
		},
	},

	// Save as Vue computed property
	getters: {
		filtered_addresses(state) {
			let addresses = [], _addresses = state.addresses;

			for (let i = 0; i < _addresses.length; i++) {
				let _place = _addresses[i];

				if (_place.interval_hour && _place.interval_hour.length) {
					_place.interval_hour = parseInt(_place.interval_hour);
				} else {
					_place.interval_hour = 0;
				}

				if (_place.interval_minute && _place.interval_minute.length) {
					_place.interval_minute = parseInt(_place.interval_minute);
				} else {
					_place.interval_minute = 0;
				}

				addresses.push(_place);
			}

			return addresses;
		},
		home_url() {
			return PhoneRepairs.home_url;
		},
		logout_url() {
			return PhoneRepairs.logout_url;
		},
		categories() {
			return SupportTickets.ticket_categories;
		},
		priorities() {
			return SupportTickets.ticket_priorities;
		},
		statuses() {
			return SupportTickets.ticket_statuses;
		},
		support_agents() {
			return SupportTickets.support_agents;
		},
		display_name() {
			return SupportTickets.user.display_name;
		},
		user_email() {
			return SupportTickets.user.user_email;
		},
		avatar_url() {
			return SupportTickets.user.avatar_url;
		},
	},
});
