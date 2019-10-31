<template>
	<div class="enter-details-wrapper">

		<section-title>Enter your contact details</section-title>

		<div class="enter-details-content-wrapper">

			<animated-input
				v-model="firstName"
				label="First name"
				helptext="This field is required."
				:has-success="firstName.length > 2"
			></animated-input>

			<animated-input
				v-model="lastName"
				label="Last name"
				helptext="This field is required."
				:has-success="lastName.length > 2"
			></animated-input>

			<animated-input
				v-model="email"
				type="email"
				label="Email"
				helptext="Enter a valid email address."
				:has-error="hasEmailError"
				:has-success="isEmailValid"
				@blur="emailBlurHandler"
				@focus="emailFocusHandler"
			></animated-input>

			<animated-input
				v-model="phone"
				label="Phone"
				helptext="This field is required."
			></animated-input>

			<div class="enter-details-continue-button-wrapper">
				<big-button @click="confirmAppointment"
							:disabled="!enabledContinueButton">Confirm Appointment
				</big-button>
			</div>
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

    export default {
        name: "userDetails",
        components: {AnimatedInput, BigButton, SectionTitle, SectionInfo, SectionHelp},
        data() {
            return {
                firstName: '',
                lastName: '',
                email: '',
                phone: '',
                couponCode: '',
                hasEmailError: false,
            }
        },
        computed: {
            ...mapState(['checkoutAnalysisId']),
            isEmailValid() {
                return this.hasEmail && this.validateEmail(this.email);
            },
            hasEmail() {
                return !!this.email.length;
            },
            hasPhone() {
                return !!this.phone.length;
            },
            hasLastName() {
                return !!this.lastName.length;
            },
            enabledContinueButton() {
                return (this.isEmailValid && this.hasPhone && this.hasLastName);
            },
            address() {
                return this.$store.state.address;
            },
            hasAddress() {
                return !!(this.address && this.address.length);
            },
            canConfirmAppointment() {
                return !!(this.lastName && this.email && this.phone);
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_CART', true);
            this.$store.commit('IS_THANK_YOU_PAGE', false);
            this.firstName = this.$store.state.firstName;
            this.lastName = this.$store.state.lastName;
            this.phone = this.$store.state.phone;

            // If no models, redirect one step back
            if (!this.hasAddress) {
                this.$router.push('/user-address');
            }

            this.$store.dispatch('updateCheckoutAnalysis', {
                step: 'user_details',
                step_data: {user_address: this.address}
            });
        },
        methods: {
            validateEmail(email) {
                let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            },
            emailBlurHandler() {
                if (this.hasEmail && !this.isEmailValid) {
                    this.hasEmailError = true;
                }
            },
            emailFocusHandler() {
                this.hasEmailError = false;
            },
            confirmAppointment() {
                this.$store.commit('SET_LOADING_STATUS', true);
                this.$store.commit('SET_FIRST_NAME', this.firstName);
                this.$store.commit('SET_LAST_NAME', this.lastName);
                this.$store.commit('SET_EMAIL_ADDRESS', this.email);
                this.$store.commit('SET_PHONE', this.phone);
                this.$store.commit('SET_COUPON_CODE', this.couponCode);

                this.$router.push('/terms-and-conditions');
            }
        }
    }
</script>

<style lang="scss">
	.enter-details-wrapper {
		width: 100%;
		padding: 0 10px;
		overflow: auto;
		box-sizing: border-box;
	}

	.enter-details-content-wrapper {
		padding-top: 10px;
		width: 100%;
		max-width: 520px;
		margin: 0 auto;
	}

	.enter-details-continue-button {
		color: #a9aeb3;
		background-color: #e1e8ec;
		border-radius: 5px;
		line-height: 64px;
		text-align: center;
		transition: all .4s ease;
		font-size: 18px;
		margin: 20px auto 40px;

		&.is-active {
			color: #0161c7;
			background-color: #12ffcd;
		}
	}

	.userdetails-coupon-button {
		margin: 30px auto 50px;
		text-align: center;
		color: #0161c7;
		font-size: 18px;
		font-weight: 300;
		line-height: 23px;
	}

	.upsell-special-offer-wrapper {
		box-sizing: border-box;
		border-radius: 10px;
		border: 1px solid #0161c7;
		padding: 13px;
		position: relative;
		max-width: 500px;
		margin: auto;
	}

	.upsell-special-offer-title {
		position: absolute;
		top: -10px;
		background: #eff2f5;
		padding: 0 15px 10px;
		color: #0161c7;
		font-size: 13px;
	}

	.upsell-special-offer-item span {
		font-size: 14px;
		color: #0161c7;
	}

	.upsell-special-offer-item button {
		float: right;
		background: none;
		outline: none;
		border: .5px solid #0161c7;
	}
</style>
