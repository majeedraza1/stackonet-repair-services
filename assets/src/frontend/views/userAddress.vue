<template>
	<div class="select-address-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper"><span class="step-nav-title">Where can we meet you?</span></div>
		</div>
		<div class="select-address-content-wrapper">
			<div class="animated-input" style="width: 100%;">
			<span class="blocking-span">
				<div>
				<input placeholder=""
					   v-model="addressTemp"
					   @focus="geolocate"
					   type="text"
					   id="address"
					   name="address"
					   class="inputText"
					   autocomplete="off"
					   role="combobox"
					   aria-autocomplete="list"
					   aria-expanded="false"
					   value=""
					   style="width: 100%;">
				</div>
			</span>
				<span class="floating-label floating-label-focused">Enter exact address</span>
			</div>

			<div class="animated-input" style="width: 100%;">
			<span class="blocking-span">
				<input type="text" id="additional"
					   name="additional"
					   class="data-hj-whitelist inputText"
					   placeholder=" " value=""
					   v-model="additionalAddressTemp"
					   style="width: 100%;">
			</span>
				<span class="floating-label">Apt / Suite / Floor No. (optional)</span>
			</div>

			<div class="animated-input" style="width: 100%;">
			<span class="blocking-span">
				<input type="text"
					   id="instructions"
					   name="instructions"
					   class="data-hj-whitelist inputText"
					   v-model="instructionsTemp"
					   placeholder=" " value=""
					   style="width: 100%;">
			</span>
				<span class="floating-label">Add instructions (optional)</span></div>
			<div>
				<div class="select-address-continue-button" @click="handleContinue">Continue</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "userAddress",
		data() {
			return {
				addressTemp: '',
				additionalAddressTemp: '',
				instructionsTemp: '',
				autocomplete: {},
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);

			let address = this.$el.querySelector('#address');
			// Create the autocomplete object, restricting the search predictions to
			// geographical location types.
			this.autocomplete = new google.maps.places.Autocomplete(address, {types: ['geocode']});

			// Avoid paying for data that you don't need by restricting the set of
			// place fields that are returned to just the address components.
			Autocomplete.setFields('address_components');

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
				// Get the place details from the autocomplete object.
				let place = this.autocomplete.getPlace();
				console.log(place);
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

		&.select-address-continue-button-active {
			color: #0161c7;
			background-color: #12ffcd;
		}
	}
</style>
