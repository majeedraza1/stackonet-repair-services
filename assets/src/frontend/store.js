import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
	// Same as Vue data
	state: {
		loading: true,
		showCart: true,
		toggleCart: false,
		group: [],
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
		testimonials: [],
		windowWidth: 0,
		isThankYouPage: false,
	},

	// Commit + track state changes
	mutations: {
		SET_LOADING_STATUS(state, loading) {
			state.loading = loading;
		},
		SET_AREA_REQUEST_ID(state, areaRequestId) {
			state.areaRequestId = areaRequestId;
		},
		SET_GROUP(state, group) {
			state.group = group;
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
		SET_TESTIMONIALS(state, testimonials) {
			state.testimonials = testimonials;
		},
		SET_TOGGLE_CART(state, toggleCart) {
			state.toggleCart = toggleCart;
		},
		SET_WINDOW_WIDTH(state, windowWidth) {
			state.windowWidth = windowWidth;
		},
		IS_THANK_YOU_PAGE(state, isThankYouPage) {
			state.isThankYouPage = isThankYouPage;
		},
	},

	// Same as Vue methods
	actions: {
		fetchAcceptedTestimonial(context) {
			context.commit('SET_LOADING_STATUS', true);
			window.jQuery.ajax({
				method: 'GET',
				url: window.Stackonet.rest_root + '/testimonials',
				data: {status: 'accept', per_page: 50,},
				success: function (response) {
					if (response.data) {
						context.commit('SET_TESTIMONIALS', response.data.items);
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
	getters: {
		isLargeScreen(state, getters) {
			return !getters.isSmallScreen;
		},
		isLargeScreenActive(state, getters) {
			return (getters.isLargeScreen && state.showCart);
		},
		isSmallScreen(state, getters) {
			if (getters.is_iPhone) {
				return true;
			}
			return !!(state.windowWidth < 1025);
		},
		isSmallScreenActive(state, getters) {
			return (getters.isSmallScreen && state.toggleCart);
		},
		is_iPhone() {
			if (window.Stackonet.is_iphone) {
				return true;
			}
			return !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
		},
		containerClasses(state, getters) {
			return {
				'is-cart-active': getters.isLargeScreenActive || getters.isSmallScreenActive,
				'is-small-screen': getters.isSmallScreen,
				'is-small-screen-active': getters.isSmallScreenActive,
				'is-thank-you-page': state.isThankYouPage,
			}
		}
	},
});
