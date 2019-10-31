<template>
	<div class="thank-you-content">
		<img class="thank-you-check" :src="icons.checkCircle" alt="Check Circle" width="55" height="55">
		<div class="title-wrapper theme-dark centered small">
			<h1 class="h1-headline">Thank you!</h1>
		</div>
		<div class="theme-dark centered">
			<p class="body-text-big">We've received your appointment request.</p>
		</div>
		<div class="thank-you-rows-wrapper">
			<div class="section-item-container">
				<div class="section-item-img">
					<img :src="icons.envelope" alt="envelope" width="25">
				</div>
				<div class="section-item-text">You will recieve a confirmation email and SMS with all the details.</div>
			</div>
			<div class="section-item-container">
				<div class="section-item-img">
					<img :src="icons.check" alt="check" width="25">
				</div>
				<div class="section-item-text">We’re selecting the best technician in your area and will confirm your
					appointment soon.
				</div>
			</div>
			<div class="section-item-container">
				<div class="section-item-img">
					<img :src="icons.map" alt="map" width="25">
				</div>
				<div class="section-item-text">Once we assign a technician, we will provide the technician's name and
					arrival time by email and SMS.
				</div>
			</div>
		</div>

		<section-help></section-help>
	</div>
</template>

<script>
    import {mapState} from 'vuex';
    import SectionHelp from '../../components/SectionHelp'

    export default {
        name: "thankyou",
        components: {SectionHelp},
        computed: {
            ...mapState(['checkoutAnalysisId', 'emailAddress']),
            icons() {
                return window.Stackonet.icons;
            },
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_CART', false);
            this.$store.commit('IS_THANK_YOU_PAGE', true);

            // If no models, redirect one step back
            if (!this.emailAddress.length) {
                this.$router.push({name: 'terms-and-conditions'});
            }

            this.$store.dispatch('updateCheckoutAnalysis', {
                step: 'thank_you',
                step_data: {}
            });
            this.$store.dispatch('removeCheckoutAnalysisIdFromLocalStorage');
        },
    }
</script>

<style lang="scss">
	.is-thank-you-page {
		.my-cart-wrapper {
			display: none;
		}
	}

	.thank-you-content {
		padding: 1rem;

		.h1-headline {
			text-align: center;
		}

		.thank-you-check {
			display: block;
			text-align: center;
			margin: 25px auto 15px;
		}

		.centered {
			text-align: center;
		}

		.thank-you-rows-wrapper {
			display: flex;
			flex-direction: column;
		}

		.section-item-container {
			max-width: 500px;
			width: 100%;
			display: flex;
			background-color: rgb(255, 255, 255);
			box-shadow: rgba(0, 0, 0, 0.04) 0 2px 4px 0;
			border-radius: 6px;
			margin: 10px auto;
			padding: 35px 0;

			.section-item-img {
				width: 125px;
				text-align: center;
				margin: auto;
			}

			.section-item-text {
				color: rgb(95, 95, 95);
				font-size: 16px;
				line-height: 20px;
				width: 90%;
				margin: 0 40px 0 0;
			}
		}
	}
</style>
