import Vuex from 'vuex'
import Vue from 'vue'
import axios from 'axios'

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
		geo_address: false,
		geo_address_object: {},
		formatted_address: '',
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
		checkoutAnalysisId: 0,
		promotion_discount: false,
	},

	// Commit + track state changes
	mutations: {
		SET_PROMOTION_DISCOUNT(state, promotion_discount) {
			state.promotion_discount = promotion_discount;
		},
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
		SET_FORMATTED_ADDRESS(state, formatted_address) {
			state.formatted_address = formatted_address;
		},
		SET_GEO_ADDRESS(state, geo_address) {
			state.geo_address = geo_address;
		},
		SET_GEO_ADDRESS_OBJECT(state, geo_address_object) {
			state.geo_address_object = geo_address_object;
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
		SET_CHECKOUT_ANALYSIS_ID(state, id) {
			state.checkoutAnalysisId = id;
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
		},
		refreshCheckoutAnalysisIdFromLocalStorage({commit}) {
			if (typeof (Storage) !== "undefined") {
				let checkoutAnalysisId = localStorage.getItem('_checkout_initial_id');

				if (checkoutAnalysisId) {
					commit('SET_CHECKOUT_ANALYSIS_ID', parseInt(checkoutAnalysisId));
				}
			}
		},
		removeCheckoutAnalysisIdFromLocalStorage({commit}) {
			if (typeof (Storage) !== "undefined") {
				let checkoutAnalysisId = localStorage.getItem('_checkout_initial_id');

				if (checkoutAnalysisId) {
					localStorage.removeItem('_checkout_initial_id');
					commit('SET_CHECKOUT_ANALYSIS_ID', 0);
				}
			}
		},
		checkoutAnalysis({commit}, data = {}) {
			axios
				.post(window.Stackonet.rest_root + '/checkout-analysis', data)
				.then(response => {
					if (response.data.data) {
						commit('SET_CHECKOUT_ANALYSIS_ID', response.data.data.id);
						// Store in local storage
						if (typeof (Storage) !== "undefined") {
							localStorage.setItem('_checkout_initial_id', response.data.data.id);
						}
					}
				})
				.catch(error => {
					console.log(error);
				});
		},
		updateCheckoutAnalysis(context, data = {}) {
			context.dispatch('refreshCheckoutAnalysisIdFromLocalStorage');
			axios.put(window.Stackonet.rest_root + '/checkout-analysis/' + context.state.checkoutAnalysisId, data).then(() => {
			}).catch(error => {
				console.log(error);
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
