import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		currentPage: 1,
		phones: [],
		devices: [],
		issues: [],
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_DEVICES(state, devices) {
			state.devices = devices;
		},
		SET_PHONES(state, phones) {
			state.phones = phones;
		},
		SET_ISSUES(state, issues) {
			state.issues = issues;
		},
	},

	// Same as Vue methods
	actions: {
		fetchDevices({commit, state}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: window.PhoneRepairs.rest_root + '/devices',
				data: {paged: state.currentPage, per_page: 50,},
				success: function (response) {
					if (response.data) {
						commit('SET_DEVICES', response.data.items);
					}
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		},
		fetchIssues({commit, state}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: window.PhoneRepairs.rest_root + '/issues',
				data: {paged: state.currentPage, per_page: 50,},
				success: function (response) {
					if (response.data) {
						commit('SET_ISSUES', response.data.items);
					}
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		},
		getPhones({commit, state}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: window.PhoneRepairs.rest_root + '/phones',
				data: {paged: state.currentPage, per_page: 50,},
				success: function (response) {
					if (response.data) {
						commit('SET_PHONES', response.data.items);
					}
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		},
		createPhone({commit, state, dispatch}, data) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'POST',
				url: window.PhoneRepairs.rest_root + '/phones',
				data: data,
				success: function (response) {
					console.log(response);
					if (response.data) {
						dispatch('getPhones');
					}
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		}
	},

	// Save as Vue computed property
	getters: {},
});
