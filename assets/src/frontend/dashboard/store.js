import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		title: 'Dashboard',
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_TITLE(state, title) {
			state.title = title;
		},
	},

	// Same as Vue methods
	actions: {},

	// Save as Vue computed property
	getters: {
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
