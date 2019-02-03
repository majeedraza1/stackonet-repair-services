import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		devices: [],
		deviceModels: [],
		deviceColors: [],
		device: {},
		deviceModel: {},
		deviceColor: {},
		zipCode: '',
	},

	mutations: { // Commit + track state changes
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_DEVICE(state, device) {
			state.device = device;
		},
		SET_DEVICE_MODEL(state, deviceModel) {
			state.deviceModel = deviceModel;
		},
		SET_DEVICE_COLOR(state, deviceColor) {
			state.deviceColor = deviceColor;
		},
		SET_ZIP_CODE(state, zipCode) {
			state.zipCode = zipCode;
		},
		SET_DEVICES(state, devices) {
			state.devices = devices;
		},
		SET_DEVICES_MODELS(state, deviceModels) {
			state.deviceModels = deviceModels;
		},
		SET_DEVICES_COLORS(state, deviceColors) {
			state.deviceColors = deviceColors;
		}
	},

	// Same as Vue methods
	actions: {},

	// Save as Vue computed property
	getters: {},
});
