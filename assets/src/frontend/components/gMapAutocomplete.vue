<template>
	<div class="g-map-autocomplete">
		<animated-input
			type="textarea"
			id="g-map-autocomplete-address"
			:label="label"
			:value="formatted_address"
			@input="handleInputEvent"
		></animated-input>
	</div>
</template>

<script>
	import AnimatedInput from '../../components/AnimatedInput.vue';

	export default {
		name: "gMapAutocomplete",
		components: {AnimatedInput},
		props: {
			label: {type: String, default: 'Address'},
			value: {type: String, default: ''},
			geolocation: {type: Boolean, default: false},
		},
		data() {
			return {
				latitude: '',
				longitude: '',
				postal_code: '',
				formatted_address: '',
				address: {},
				addresses: [],
			}
		},
		watch: {
			value(newValue) {
				this.formatted_address = newValue;
			}
		},
		mounted() {
			let self = this,
				inputField = self.$el.querySelector('#g-map-autocomplete-address');

			if (self.value) {
				self.formatted_address = self.value;
			}

			inputField.addEventListener('focus', function () {
				inputField.setAttribute('autocomplete', 'noop-' + Date.now());
			});

			// Create the autocomplete object, restricting the search predictions to
			// geographical location types.
			let autocomplete = new google.maps.places.Autocomplete(inputField, {types: ['geocode']});

			if (navigator.geolocation && self.geolocation) {
				let geocoder = new google.maps.Geocoder;

				navigator.geolocation.getCurrentPosition(function (position) {
					self.latitude = position.coords.latitude;
					self.longitude = position.coords.longitude;

					geocoder.geocode(
						{'location': {lat: self.latitude, lng: self.longitude}},
						function (results, status) {
							if (status === 'OK') {
								if (results[0]) {
									self.addresses = results;
									self.address = results[0];
									self.formatted_address = results[0].formatted_address;

									self.findPostalCode(results[0]);
								}
							} else {
								console.log('Geocoder failed due to: ' + status);
							}
						}
					);
				});
			}

			// Avoid paying for data that you don't need by restricting the set of
			// place fields that are returned to just the address components.
			autocomplete.setFields(['address_components', 'formatted_address', 'geometry', 'place_id', 'types']);

			// When the user selects an address from the drop-down, populate the
			// address fields in the form.
			google.maps.event.addListener(autocomplete, 'place_changed', () => {
				// Get the place details from the autocomplete object.
				let place = autocomplete.getPlace();

				self.address = place;
				self.formatted_address = place.formatted_address;
				self.latitude = place.geometry.location.lat();
				self.longitude = place.geometry.location.lng();

				self.findPostalCode(place);

				self.$emit('change', {
					formatted_address: self.formatted_address,
					postal_code: self.postal_code,
					latitude: self.latitude,
					longitude: self.longitude,
					address: self.address,
				});
			});

		},
		methods: {
			handleInputEvent(value) {
				this.formatted_address = value;
			},
			findPostalCode(address) {
				for (let i = 0; i < address.address_components.length; i++) {
					let addressComponent = address.address_components[i];
					let addressType = addressComponent.types[0];
					if ('postal_code' === addressType) {
						this.postal_code = addressComponent.short_name;
					}
				}
			}
		}
	}
</script>

<style scoped>

</style>
