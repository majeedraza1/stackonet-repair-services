import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		services_areas: [],
		products: [],
		issues: [],
		devices: [],
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
		}
	},

	// Save as Vue computed property
	getters: {},
});
