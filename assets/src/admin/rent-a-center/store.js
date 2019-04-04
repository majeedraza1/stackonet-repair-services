import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		services_areas: [],
		requested_areas: [],
		requested_areas_counts: {},
		products: [],
		issues: [],
		devices: [],
		testimonials: [],
		testimonialsCounts: {},
		phones: [],
		settings: {},
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_SERVICES_AREAS(state, services_areas) {
			state.services_areas = services_areas;
		},
		SET_TESTIMONIALS(state, testimonials) {
			state.testimonials = testimonials;
		},
		SET_TESTIMONIALS_COUNTS(state, testimonialsCounts) {
			state.testimonialsCounts = testimonialsCounts;
		},
		SET_REQUESTED_AREAS(state, requested_areas) {
			state.requested_areas = requested_areas;
		},
		SET_REQUESTED_AREAS_COUNTS(state, requested_areas_counts) {
			state.requested_areas_counts = requested_areas_counts;
		},
		SET_ISSUES(state, issues) {
			state.issues = issues;
		},
		SET_PRODUCTS(state, products) {
			state.products = products;
		},
		SET_DEVICES(state, devices) {
			state.devices = devices;
		},
		SET_SETTINGS(state, settings) {
			state.settings = settings;
		},
		SET_PHONES(state, phones) {
			state.phones = phones;
		},
	},

	// Same as Vue methods
	actions: {
		fetch_services_areas(context) {
			let $ = window.jQuery;
			context.commit('SET_LOADING_STATUS', true);
			$.ajax({
				method: 'GET',
				url: ajaxurl,
				data: {
					action: 'get_services_areas',
				},
				success: function (response) {
					if (response.data) {
						context.commit('SET_SERVICES_AREAS', response.data);
					}
					context.commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					context.commit('SET_LOADING_STATUS', false);
				}
			});
		},
		fetch_testimonials(context) {
			context.commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: ajaxurl,
				data: {
					action: 'get_client_testimonials',
					per_page: 1,
				},
				success: function (response) {
					if (response.data) {
						context.commit('SET_TESTIMONIALS', response.data);
					}
					context.commit('SET_LOADING_STATUS', false);
				},
				error: function () {
					context.commit('SET_LOADING_STATUS', false);
				}
			});
		},
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
