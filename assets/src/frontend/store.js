import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		showCart: true,
		devices: [],
		deviceModels: [],
		deviceColors: [],
		device: {},
		deviceModel: {},
		deviceColor: {},
		zipCode: '',
		areaRequestId: 0,
		screenCracked: '',
		issues: [],
		issueDescription: '',
		date: '',
		timeRange: '',
		addressObject: {},
		address: '',
		additionalAddress: '',
		instructions: '',
		firstName: '',
		lastName: '',
		emailAddress: '',
		phone: '',
		couponCode: '',
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_AREA_REQUEST_ID(state, areaRequestId) {
			state.areaRequestId = areaRequestId;
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
		},
		SET_SCREEN_CRACKED(state, screenCracked) {
			state.screenCracked = screenCracked;
		},
		SET_ISSUE(state, issues) {
			state.issues = issues;
		},
		SET_ISSUE_DESCRIPTION(state, issueDescription) {
			state.issueDescription = issueDescription;
		},
		SET_DATE(state, date) {
			state.date = date;
		},
		SET_TIME_RANGE(state, timeRange) {
			state.timeRange = timeRange;
		},
		SET_ADDRESS(state, address) {
			state.address = address;
		},
		SET_ADDRESS_OBJECT(state, addressObject) {
			state.addressObject = addressObject;
		},
		SET_ADDITIONAL_ADDRESS(state, additionalAddress) {
			state.additionalAddress = additionalAddress;
		},
		SET_INSTRUCTIONS(state, instructions) {
			state.instructions = instructions;
		},
		SET_FIRST_NAME(state, firstName) {
			state.firstName = firstName;
		},
		SET_LAST_NAME(state, lastName) {
			state.lastName = lastName;
		},
		SET_EMAIL_ADDRESS(state, emailAddress) {
			state.emailAddress = emailAddress;
		},
		SET_PHONE(state, phone) {
			state.phone = phone;
		},
		SET_COUPON_CODE(state, couponCode) {
			state.couponCode = couponCode;
		},
		SET_SHOW_CART(state, showCart) {
			state.showCart = showCart;
		},
	},

	// Same as Vue methods
	actions: {},

	// Save as Vue computed property
	getters: {},
});
