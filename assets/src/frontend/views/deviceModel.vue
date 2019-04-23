<template>
	<div class="device-model-list">
		<section-title>Which Model is your {{device.device_title}} device?</section-title>
		<section-info>Pay once the job is complete!</section-info>
		<div class="select-device-content-container">
			<template v-for="device in deviceModels">
				<div class="scale-on-mount scale-on-mount-active" @click="chooseDeviceModel(device)">
					<div class="phone-model-item-wrapper hoverable">
						<div v-text="device.title"></div>
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
		name: "deviceModel",
		components: {SectionInfo, SectionTitle, SectionHelp},
		data() {
			return {}
		},
		computed: {
			...mapState(['deviceModels', 'device']),
			hasModels() {
				return !!(this.deviceModels && this.deviceModels.length);
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);
			this.$store.commit('IS_THANK_YOU_PAGE', false);

			// If no models, redirect one step back
			if (!this.hasModels) {
				this.$router.push('/');
			}
		},
		methods: {
			chooseDeviceModel(device) {
				this.$store.commit('SET_DEVICE_MODEL', device);
				this.$store.commit('SET_DEVICES_COLORS', device.colors);
				this.$router.push('/device-color');
			}
		}
	}
</script>

<style lang="scss">
	.phone-model-item-wrapper {
		cursor: pointer;
		height: 75px;
		min-width: 140px;
		margin: 15px !important;
		border-radius: 4px;
		background-color: #fff;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>
