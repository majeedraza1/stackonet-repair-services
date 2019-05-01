import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		devices: [],
		devices_models: [],
		device: {},
		device_model: {},
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
		SET_DEVICES_MODELS(state, devices_models) {
			state.devices_models = devices_models;
		},
		SET_DEVICE(state, device) {
			state.device = device;
		},
		SET_DEVICE_MODEL(state, device_model) {
			state.device_model = device_model;
		},
		SET_ISSUE(state, issues) {
			state.issues = issues;
		},
	},

	// Same as Vue methods
	actions: {},

	// Save as Vue computed property
	getters: {},
});
