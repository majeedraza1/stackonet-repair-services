<template>
	<div class="select-address-wrapper">

		<section-title>Where can we meet you?</section-title>

		<div class="select-address-content-wrapper">
			<form action="#" autocomplete="off">

				<g-map-autocomplete geolocation @change="updateAddress"></g-map-autocomplete>

				<animated-input
					id="additional"
					v-model="additionalAddressTemp"
					label="Apt / Suite / Floor No. (optional)"
					:has-success="!!additionalAddressTemp.length"
				></animated-input>

				<animated-input
					type="textarea"
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
					<big-button fullwidth :disabled="!canContinue" @click="handleContinue">Continue</big-button>
				</div>

				<div class="address-extra-info">
					If the system doesn't generate the proper address. Please add your full address on Add Instructions.
				</div>
			</form>
		</div>
		<section-help></section-help>
	</div>
</template>

<script>
    import {mapState} from 'vuex';
    import AnimatedInput from '../../../components/AnimatedInput.vue';
    import BigButton from '../../../components/BigButton.vue';
    import SectionTitle from '../../components/SectionTitle'
    import SectionInfo from '../../components/SectionInfo'
    import SectionHelp from '../../components/SectionHelp'
    import GMapAutocomplete from "../../components/gMapAutocomplete";

    export default {
        name: "userAddress",
        components: {GMapAutocomplete, AnimatedInput, BigButton, SectionTitle, SectionInfo, SectionHelp},
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
            ...mapState(['zipCode', 'addressObject', 'formatted_address', 'geo_address', 'geo_address_object', 'date', 'timeRange', 'checkoutAnalysisId']),
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
            hasDate() {
                return !!(this.date && this.date.length);
            },
            canContinue() {
                return !!(this.newZipCode);
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_CART', true);
            this.$store.commit('IS_THANK_YOU_PAGE', false);

            // If no models, redirect one step back
            if (!this.hasDate) {
                this.$router.push({name: 'select-time'});
            }

            this.$store.dispatch('updateCheckoutAnalysis', {
                step: 'user_address',
                step_data: {requested_date_time: {date: this.date, time_range: this.timeRange}}
            });
        },
        methods: {
            updateAddress(data) {
                let placeData = this.format_address_components(data.address.address_components);
                this.addressObj = placeData;
                this.addressTemp = data.formatted_address;
                this.$store.commit('SET_ADDRESS_OBJECT', placeData);
            },
            handleContinue() {
                this.$store.commit('SET_ADDRESS', this.addressTemp);
                this.$store.commit('SET_ADDITIONAL_ADDRESS', this.additionalAddressTemp);
                this.$store.commit('SET_INSTRUCTIONS', this.instructionsTemp);
                this.$router.push('/user-details');
            },
            format_address_components(address_components) {
                let placeData = {};
                let componentForm = {
                    street_number: 'street_number',
                    route: 'street_address', // Street address
                    locality: 'city', // City
                    administrative_area_level_1: 'state', // State
                    country: 'country', // Country Code
                    postal_code: 'postal_code' // Post code
                };

                for (let i = 0; i < address_components.length; i++) {
                    let addressComponent = address_components[i];
                    let addressType = addressComponent.types[0];
                    if (componentForm[addressType]) {
                        let val = addressComponent;
                        delete addressComponent.types;
                        placeData[componentForm[addressType]] = val;
                    }
                }

                return placeData;
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

	.address-extra-info {
		margin-top: 2rem;
		text-align: center;
	}
</style>
