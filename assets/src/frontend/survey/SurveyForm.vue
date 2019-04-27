<template>
	<div class="stackonet-survey-form">

		<div class="form-field">
			<mdl-radio
				label="Product or service does not pertain to them."
				value="not-pertain"
				v-model="device_status"
			></mdl-radio>
		</div>
		<div class="form-field">
			<mdl-radio
				label="Device can be fixed at an affordable price."
				value="affordable"
				v-model="device_status"
			></mdl-radio>
		</div>
		<div class="form-field">
			<mdl-radio
				label="Device cannot be fixed at an affordable price."
				value="not-affordable"
				v-model="device_status"
			></mdl-radio>
		</div>

		<div class="form-field">
			<animated-input id="formatted_address" label="Address" v-model="formatted_address"></animated-input>
			<button class="button" @click="open_address_modal = true">Change Address</button>

			<modal :active="open_address_modal" @close="open_address_modal = false" title="Address">
				<div class="formatted-address-list">
					<div
						v-for="_address in addresses"
						class="formatted-address-list_item"
						:class="{'is-active':_address.formatted_address === formatted_address}"
						@click="changeAddress(_address)"
					>
						<div>{{_address.formatted_address}}</div>
					</div>
				</div>
			</modal>
		</div>

		<big-button @click="handleSubmit">Submit</big-button>

		<div class="loading-container" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import axios from 'axios'
	import AnimatedInput from '../../components/AnimatedInput';
	import BigButton from '../../components/BigButton';
	import modal from '../../shapla/modal/modal';
	import mdlRadio from '../../material-design-lite/radio/mdlRadio';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner';

	export default {
		name: "SurveyForm",
		components: {AnimatedInput, BigButton, mdlRadio, mdlSpinner, modal},
		data() {
			return {
				loading: true,
				open_address_modal: false,
				device_status: '',
				latitude: '',
				longitude: '',
				addresses: [],
				address_object: {},
				formatted_address: '',
			}
		},
		computed: {
			map_api_key() {
				return window.PhoneRepairs.map_api_key;
			},
			c_address_object() {
				let place = this.address_object;
				let placeData = {};
				let componentForm = {
					street_number: 'street_number',
					route: 'street_address', // Street address
					locality: 'city', // City
					administrative_area_level_1: 'state', // State
					country: 'country', // Country Code
					postal_code: 'postal_code' // Post code
				};

				if (place.address_components) {
					for (let i = 0; i < place.address_components.length; i++) {
						let addressComponent = place.address_components[i];
						let addressType = addressComponent.types[0];
						if (componentForm[addressType]) {
							let val = addressComponent;
							delete addressComponent.types;
							placeData[componentForm[addressType]] = val;
						}
					}
				}

				return placeData;
			},
		},
		mounted() {
			let self = this;
			this.loading = false;
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function (position) {
					self.latitude = position.coords.latitude;
					self.longitude = position.coords.longitude;

					let geocoder = new google.maps.Geocoder;
					geocoder.geocode({
						'location': {
							lat: self.latitude,
							lng: self.longitude
						}
					}, function (results, status) {
						if (status === 'OK') {
							if (results[0]) {
								self.addresses = results;
								self.address_object = results[0];
								self.formatted_address = results[0].formatted_address;
							}
						} else {
							console.log('Geocoder failed due to: ' + status);
						}
					});
				});

			}
		},
		methods: {
			changeAddress(address) {
				this.address_object = address;
				this.formatted_address = address.formatted_address;
			},
			handleSubmit() {
				let self = this;
				if (self.device_status.length < 1) {
					alert('Please choose an option.');
					return;
				}
				this.loading = true;
				axios
					.post(PhoneRepairs.rest_root + '/survey',
						{
							device_status: self.device_status,
							latitude: self.latitude,
							longitude: self.longitude,
							address: self.c_address_object,
							full_address: self.formatted_address,
						},
						{
							headers: {'X-WP-Nonce': window.PhoneRepairs.rest_nonce},
						})
					.then((response) => {
						self.loading = false;
						alert('Data has been submitted successfully.');
					})
					.catch((error) => {
						self.loading = false;
						alert('Some thing went wrong. Please try again.');
					});
			}
		}
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/ripple/ripple";

	.stackonet-survey-form {
		margin: 100px auto;
		max-width: 600px;
		position: relative;

		.form-field {
			margin-bottom: 1rem;
		}

		.loading-container {
			&.is-active {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				display: flex;
				justify-content: center;
				align-items: center;
				background: rgba(#000, 0.5);
				z-index: 10;
			}
		}
	}

	.stackonet-section-title {
		text-align: center;
		margin-bottom: 1em;
	}

	.formatted-address-list_item {
		border: 1px solid rgba(#000, 0.2);
		cursor: pointer;
		margin: 1em 0;
		padding: 0.5rem 1rem;

		&:not(.is-active):hover {
			border-color: #f58730;
		}

		&.is-active {
			border-color: #f58730;
		}
	}
</style>
