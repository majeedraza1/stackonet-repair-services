<template>
	<div>
		<section-title>Enter your phone number.</section-title>

		<div class="enter-details-content-wrapper">

			<div class="pro-wrapper" v-if="gotDiscount">
				<div>You've</div>
				<div class="shapla-date-time-box">
					<div class="shapla-date-time-box__content is-active">
						<div>
							<div class="shapla-date-time-box__date">{{minutes}}:{{seconds}}</div>
						</div>
					</div>
				</div>
				<div>to get 10% off</div>
			</div>

			<animated-input v-model="phone" label="Phone Number" helptext="This field is required."></animated-input>

			<big-button fullwidth @click="confirmAppointment" :disabled="!hasPhone">Continue</big-button>
		</div>
	</div>
</template>

<script>
    import {mapState} from 'vuex';
    import SectionTitle from '../../components/SectionTitle'
    import AnimatedInput from '../../../components/AnimatedInput.vue';
    import BigButton from '../../../components/BigButton.vue';

    export default {
        name: "promotion",
        components: {AnimatedInput, SectionTitle, BigButton},
        data() {
            return {
                phone: '',
                gotDiscount: false,
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
            }
        },
        computed: {
            ...mapState(['issues']),
            hasPhone() {
                return !!this.phone.length;
            },
            checkout_banner_time() {
                return parseInt(PhoneRepairs.checkout_banner_time) * 1000;
            },
            hasIssues() {
                return !!(this.issues && this.issues.length);
            },
        },
        methods: {
            confirmAppointment() {
                this.$store.commit('SET_LOADING_STATUS', true);
                this.$store.commit('SET_PHONE', this.phone);
                this.$store.commit('SET_PROMOTION_DISCOUNT', this.gotDiscount > 0);

                this.$router.push({name: 'select-time'});
            }
        },
        mounted() {
            if (!this.hasIssues) {
                this.$router.push({name: 'select-issue'});
            }

            this.$store.dispatch('updateCheckoutAnalysis', {
                step: 'phone_number',
                step_data: {device_issue: this.issues}
            });

            let timeDif = parseInt(PhoneRepairs.checkout_banner_time) * 1000;
            let countDownDate = Date.now() + timeDif;
            let now = new Date().getTime();
            this.gotDiscount = true;

            let x = setInterval(() => {
                // Find the distance between now an the count down date
                let distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
                this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                this.seconds = Math.floor((distance % (1000 * 60)) / 1000);

                now += 1000;

                if (distance < 0) {
                    clearInterval(x);
                    this.gotDiscount = false;
                }
            }, 1000);
        }
    }
</script>

<style scoped lang="scss">
	.pro-wrapper {
		align-items: center;
		display: flex;
		justify-content: center;
	}
</style>
