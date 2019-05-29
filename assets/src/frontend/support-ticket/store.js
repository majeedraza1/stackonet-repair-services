import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
	},

	// Same as Vue methods
	actions: {},

	// Save as Vue computed property
	getters: {
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
	},
});
