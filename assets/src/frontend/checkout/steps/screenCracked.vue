<template>
	<div class="select-issue-wrapper">

		<section-title>Is your screen broken?</section-title>

		<section-info>
			<span><b>Lifetime warranty</b></span><span> on all parts and labor</span>
		</section-info>

		<div class="select-issue-content-container">

			<template v-if="broken_screen_price">
				<div class="shapla-device-box shapla-device-box--broken-screen is-active"
					 @click="setScreenCracked('yes')">
					<div class="shapla-device-box__content hoverable">
						<div class="shapla-device-box__price">
							<span class="shapla-device-box__price-text">${{broken_screen_price}}</span>
						</div>
						<div class="shapla-device-box__icon">
							<img :src="icons.screenBrokenYes" alt="Screen Broken" width="30" height="53">
						</div>
						<div class="shapla-device-box__title">Yes</div>
					</div>
				</div>
			</template>

			<div class="shapla-device-box shapla-device-box--broken-screen is-active" @click="setScreenCracked('no')">
				<div class="shapla-device-box__content hoverable">
					<div class="shapla-device-box__icon">
						<img :src="icons.screenBrokenNo" alt="Screen Not Broken" width="30" height="53">
					</div>
					<div class="shapla-device-box__title">No</div>
				</div>
			</div>
			<div class="shapla-device-box shapla-device-box--broken-screen is-active"
				 @click="setScreenCracked('multiple')">
				<div class="shapla-device-box__content hoverable">
					<div class="shapla-device-box__icon">
						<img :src="icons.screenMultiIssue" alt="Screen Multiple Issues" width="30" height="53">
					</div>
					<div class="shapla-device-box__title">Multiple <br> Issues</div>
				</div>
			</div>
		</div>
		<section-help></section-help>
	</div>
</template>

<script>
    import {mapState} from 'vuex';
    import SectionInfo from '../../components/SectionInfo'
    import SectionTitle from '../../components/SectionTitle'
    import SectionHelp from '../../components/SectionHelp'

    export default {
        name: "screenCracked",
        components: {SectionInfo, SectionTitle, SectionHelp},
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_CART', true);

            // If no models, redirect one step back
            if (!this.hasZipCode) {
                this.$router.push({name: 'zip-code'});
            }

            this.$store.dispatch('updateCheckoutAnalysis', {
                step: 'screen_cracked',
                step_data: {zip_code: this.zipCode}
            });
        },
        computed: {
            ...mapState(['zipCode', 'device', 'screenCracked', 'deviceModel', 'checkoutAnalysisId']),
            icons() {
                return window.Stackonet.icons;
            },
            hasZipCode() {
                return !!(this.zipCode && this.zipCode.length);
            },
            broken_screen_label() {
                if (this.device && this.device.broken_screen_label) {
                    return this.device.broken_screen_label;
                }

                return '';
            },
            broken_screen_price() {
                if (this.deviceModel && this.deviceModel.broken_screen_price) {
                    return this.deviceModel.broken_screen_price;
                }
                if (this.device && this.device.broken_screen_price) {
                    return this.device.broken_screen_price;
                }

                return '';
            }
        },
        methods: {
            setScreenCracked(value) {
                this.$store.commit('SET_SCREEN_CRACKED', value);
                if ('yes' === value) {
                    this.$store.commit('SET_ISSUE', [
                        {
                            id: 'BrokenScreen',
                            title: this.broken_screen_label,
                            price: this.broken_screen_price
                        }
                    ]);
                    this.$router.push({name: 'promotion'});
                } else {
                    this.$router.push({name: 'select-issue'});
                }
            }
        }
    }
</script>

<style lang="scss">
	.select-issue-wrapper {
		display: flex;
		flex-direction: column;
	}

	.select-issue-content-container {
		display: flex;
		justify-content: center;
		margin: 0 auto 3rem;
		flex-wrap: wrap;
		max-width: 700px;

		.shapla-device-box--broken-screen {
			.shapla-device-box__content {
				justify-content: space-between;
				padding-bottom: 10px;
				padding-top: 10px;
			}
		}
	}
</style>
