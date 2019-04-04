import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		issues: [],
		devices: [],
		phones: [],
		pagination: {},
		counts: {},
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_ISSUES(state, issues) {
			state.issues = issues;
		},
		SET_DEVICES(state, devices) {
			state.devices = devices;
		},
		SET_PHONES(state, phones) {
			state.phones = phones;
		},
		SET_PAGINATION(state, pagination) {
			state.pagination = pagination;
		},
		SET_COUNTS(state, counts) {
			state.counts = counts;
		},
	},

	// Same as Vue methods
	actions: {
		fetchDevices({commit, state}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: window.stackonetSettings.root + '/devices',
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
				url: window.stackonetSettings.root + '/issues',
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
		fetchPhones({commit}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: ajaxurl,
				data: {
					action: 'get_phones',
					per_page: 20,
				},
				success: function (response) {
					if (response.data) {
						commit('SET_PHONES', response.data.items);
						commit('SET_PAGINATION', response.data.pagination);
						commit('SET_COUNTS', response.data.counts);
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
				url: window.stackonetSettings.root + '/phones',
				data: data,
				success: function (response) {
					if (response.data) {
						dispatch('fetchPhones');
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
