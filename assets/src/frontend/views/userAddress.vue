<template>
	<div class="select-address-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper"><span class="step-nav-title">Where can we meet you?</span></div>
		</div>
		<div class="select-address-content-wrapper">

			<animated-input
					id="address"
					v-model="addressTemp"
					@focus="geolocate"
					label="Enter exact address"
					helptext="Please enter valid input"
					:has-error="hasExactAddressError"
					:has-success="zipCode === newZipCode"
			></animated-input>

			<animated-input
					id="additional"
					v-model="additionalAddressTemp"
					label="Apt / Suite / Floor No. (optional)"
					:has-success="!!additionalAddressTemp.length"
			></animated-input>

			<animated-input
					id="instructions"
					v-model="instructionsTemp"
					label="Add instructions (optional)"
					:has-success="!!instructionsTemp.length"
			></animated-input>

			<div class="select-address-market-no-aligned" v-if="showZipCodeError">
				*The address you entered is not corresponding<br>
				with zipcode {{zipCode}} entered in an early step.<br>
				Please edit zipcode or change address.
			</div>
			<div>
				<div class="select-address-continue-button"
					 :class="{'select-address-continue-button-active': zipCode && !showZipCodeError}"
					 @click="handleContinue">
					Continue
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import AnimatedInput from '../../components/AnimatedInput.vue';

	export default {
		name: "userAddress",
		components: {AnimatedInput},
		data() {
			return {
				addressTemp: '',
				additionalAddressTemp: '',
				instructionsTemp: '',
				autocomplete: {},
				addressObj: {}
			}
		},
		computed: {
			zipCode() {
				return this.$store.state.zipCode;
			},
			addressObject() {
				return this.$store.state.addressObject;
			},
			newZipCode() {
				if (typeof this.addressObject.postal_code == "undefined") {
					return false;
				}
				if (typeof this.addressObject.postal_code.short_name == "undefined") {
					return false
				}

				return this.addressObject.postal_code.short_name;
			},
			showZipCodeError() {
				return (this.newZipCode && this.zipCode && this.newZipCode !== this.zipCode);
			},
			hasExactAddressError() {
				return (!!this.addressTemp.length && !this.zipCode.length);
			},
			date() {
				return this.$store.state.date;
			},
			hasDate() {
				return !!(this.date && this.date.length);
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);

			// If no models, redirect one step back
			if (!this.hasDate) {
				this.$router.push('/select-time');
			}

			let address = this.$el.querySelector('#address');
			// Create the autocomplete object, restricting the search predictions to
			// geographical location types.
			this.autocomplete = new google.maps.places.Autocomplete(address, {types: ['geocode']});

			// Avoid paying for data that you don't need by restricting the set of
			// place fields that are returned to just the address components.
			this.autocomplete.setFields(['address_components']);

			// When the user selects an address from the drop-down, populate the
			// address fields in the form.
			this.autocomplete.addListener('place_changed', this.fillInAddress);
		},
		methods: {
			handleContinue() {
				this.$store.commit('SET_ADDRESS', this.addressTemp);
				this.$store.commit('SET_ADDITIONAL_ADDRESS', this.additionalAddressTemp);
				this.$store.commit('SET_INSTRUCTIONS', this.instructionsTemp);
				this.$router.push('/user-details');
			},
			geolocate() {
				if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(function (position) {
						let geolocation = {
							lat: position.coords.latitude,
							lng: position.coords.longitude
						};
						let circle = new google.maps.Circle(
								{center: geolocation, radius: position.coords.accuracy}
						);
						this.autocomplete.setBounds(circle.getBounds());
					});
				}
			},
			fillInAddress() {
				let placeData = {};
				let componentForm = {
					street_number: 'street_number',
					route: 'street_address', // Street address
					locality: 'city', // City
					administrative_area_level_1: 'state', // State
					country: 'country', // Country Code
					postal_code: 'postal_code' // Post code
				};

				// Get the place details from the autocomplete object.
				let place = this.autocomplete.getPlace();
				// Get each component of the address from the place details,
				// and then fill-in the corresponding field on the form.
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

					this.addressObj = placeData;
					this.addressTemp = this.calculateFullAddress(placeData);
					this.$store.commit('SET_ADDRESS_OBJECT', placeData);
				}
			},
			calculateFullAddress(addressObj) {
				let address = '';
				address += addressObj.street_number.long_name + ' ' + addressObj.street_address.long_name;
				address += ', ' + addressObj.city.long_name;
				address += ', ' + addressObj.state.short_name + ' ' + addressObj.postal_code.short_name;
				address += ', ' + addressObj.country.short_name;

				return address;
			}
		}
	}
</script>

<style lang="scss">
	.step-nav-wrapper {
		display: flex;
		justify-content: space-around;
		margin: 30px 0;
	}

	.step-nav-title {
		flex: 1 1;
		text-align: center;
		font-size: 22px;
		color: #3d4248;
	}

	.select-address-content-wrapper {
		padding-top: 10px;
		width: 100%;
		max-width: 520px;
		margin: 0 auto;
	}

	.select-address-continue-button {
		width: 100%;
		color: #a9aeb3;
		background-color: #e1e8ec;
		border-radius: 5px;
		line-height: 64px;
		text-align: center;
		transition: all .4s ease;
		font-size: 18px;
		margin: 13px auto 40px;
		cursor: not-allowed;

		&.select-address-continue-button-active {
			color: #0161c7;
			background-color: #12ffcd;
			cursor: pointer;
		}
	}

	.select-address-market-no-aligned {
		text-align: center;
		font-size: 13px;
		color: red;
	}
</style>
