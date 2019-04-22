import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		snackbar: {},
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_SNACKBAR(state, snackbar) {
			state.snackbar = snackbar;
		},
	},

	// Same as Vue methods
	actions: {},

	// Save as Vue computed property
	getters: {},
});
