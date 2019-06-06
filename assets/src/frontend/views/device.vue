<template>
	<div class="device-list">
		<section-title>
			Which device would you like repaired?
		</section-title>
		<section-info>
			Whether you’re at home, the office or even your favorite coffee shop. We come to you and repair your
			device on the spot!
		</section-info>
		<div class="select-device-content-container">
			<template v-for="device in _devices">
				<div class="shapla-device-box is-active" @click="chooseDeviceModel(device)">
					<div class="shapla-device-box__content hoverable">
						<div class="shapla-device-box__image">
							<img :src="device.image.src" :alt="device.device_title">
						</div>
						<div class="shapla-device-box__label" v-text="device.device_title"></div>
					</div>
				</div>
			</template>
		</div>
		<section-help></section-help>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import SectionInfo from '../components/SectionInfo'
	import SectionTitle from '../components/SectionTitle'
	import SectionHelp from '../components/SectionHelp'

	export default {
		name: "device",
		data() {
			return {}
		},
		components: {SectionInfo, SectionTitle, SectionHelp},
		computed: {
			...mapState(['devices', 'group', 'checkoutAnalysisId']),
			_devices() {
				let self = this;
				if (self.group.length < 1) {
					return self.devices;
				}
				return self.devices.filter(device => {
					return -1 !== self.group.indexOf(device.device_group);
				});
			}
		},
		mounted() {
			let devices = window.Stackonet.devices;
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_DEVICES', devices);
			this.$store.commit('SET_SHOW_CART', false);
			this.$store.commit('IS_THANK_YOU_PAGE', false);

			this.$store.dispatch('refreshCheckoutAnalysisIdFromLocalStorage');
			if (!this.checkoutAnalysisId) {
				this.$store.dispatch('checkoutAnalysis', {
					id: 0, step: 'device', step_data: {}
				});
			}
		},
		methods: {
			chooseDeviceModel(device) {
				this.$store.commit('SET_DEVICE', device);
				this.$store.commit('SET_DEVICES_MODELS', device.device_models);
				this.$router.push('/device-model');
			}
		}
	}
</script>

<style lang="scss">
	.select-device-content-container {
		max-width: 800px;
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		align-items: center;
		margin: 0 auto;
	}
</style>
