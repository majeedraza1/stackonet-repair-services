import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		snackbar: {},
		issues: [],
		devices: [],
		phones: [],
		pagination: {},
		counts: {},
		status: 'all',
		currentPage: 1,
		search: '',
		store_address: '',
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_SNACKBAR(state, snackbar) {
			state.snackbar = snackbar;
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
		SET_STATUS(state, status) {
			state.status = status;
		},
		SET_CURRENT_PAGE(state, currentPage) {
			state.currentPage = currentPage;
		},
		SET_SEARCH(state, search) {
			state.search = search;
		},
		SET_STORE_ADDRESS(state, store_address) {
			state.store_address = store_address;
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
		filterAddress({commit, dispatch}, address) {
			commit('SET_STORE_ADDRESS', address);
			dispatch('fetchPhones');
		},
		fetchPhones({commit, state}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: ajaxurl,
				data: {
					action: 'get_phones',
					status: state.status,
					paged: state.currentPage,
					search: state.search,
					store_address: state.store_address,
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
		},
		updatePhone({commit, state, dispatch}, data) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'PUT',
				url: window.stackonetSettings.root + '/phones/' + data.id,
				data: data,
				success: function (response) {
					if (response.data) {
						dispatch('fetchPhones');
						commit('SET_SNACKBAR', {message: 'Data has been updated.'});
					}
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		},
		trashPhone({commit, dispatch, state}, data) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'POST',
				url: ajaxurl,
				data: {action: 'delete_phone', id: data.item.id, _action: data.action},
				success: function () {
					dispatch('fetchPhones');
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		},
		batchTrashPhones({commit, dispatch, state}, {ids, action}) {
			commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'POST',
				url: ajaxurl,
				data: {action: 'batch_delete_phones', ids: ids, _action: action,},
				success: function () {
					dispatch('fetchPhones');
					commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					commit('SET_LOADING_STATUS', false);
				}
			});
		},
	},

	// Save as Vue computed property
	getters: {
		phone_statuses() {
			return StackonetRentCenter.phone_statuses;
		},
		store_addresses() {
			return StackonetRentCenter.unique_store_addresses;
		}
	},
});
