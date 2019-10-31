<template>
	<div class="device-colors-list">
		<section-title>What Color is your device?</section-title>
		<section-info>Amazing Service Affordable Prices</section-info>
		<div class="select-color-content-container">
			<template v-for="color in deviceColors">
				<div class="shapla-device-box is-active" @click="chooseDeviceColor(color)">
					<div class="shapla-device-box__content hoverable">
						<div class="shapla-device-box__circle" :style="{backgroundColor: color.color}"></div>
						<div class="shapla-device-box__title" v-text="color.title"></div>
						<div class="shapla-device-box__subtitle" v-text="color.subtitle"></div>
					</div>
				</div>
			</template>
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
        name: "deviceColor",
        components: {SectionInfo, SectionTitle, SectionHelp},
        data() {
            return {}
        },
        computed: {
            ...mapState(['deviceColors', 'deviceModel', 'checkoutAnalysisId']),
            hasDeviceModel() {
                return Object.keys(this.deviceModel).length;
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_CART', true);
            this.$store.commit('IS_THANK_YOU_PAGE', false);

            // If no models, redirect one step back
            if (!this.hasDeviceModel) {
                this.$router.push({name: 'device-model'});
            }

            this.$store.dispatch('updateCheckoutAnalysis', {
                step: 'device_color',
                step_data: {device_model: this.deviceModel.title}
            });
        },
        methods: {
            chooseDeviceColor(color) {
                this.$store.commit('SET_DEVICE_COLOR', color);
                this.$router.push('/zip-code');
            }
        }
    }
</script>

<style lang="scss">
	.select-color-content-container {
		display: flex;
		justify-content: center;
		flex-wrap: wrap;
		max-width: 800px;
		margin: 0 auto;
	}
</style>
