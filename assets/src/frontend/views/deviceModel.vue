<template>
	<div class="device-model-list">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper">
				<span class="step-nav-title">Which Model is your {{device.device_title}} device?</span>
			</div>
		</div>
		<div class="info-box-wrapper">
			<div class="info-box-inner-wrapper">Pay once the job is complete!</div>
		</div>
		<div class="select-device-content-container">
			<template v-for="device in deviceModels">
				<div class="scale-on-mount scale-on-mount-active" @click="chooseDeviceModel(device)">
					<div class="phone-model-item-wrapper hoverable">
						<div v-text="device.title"></div>
					</div>
				</div>
			</template>
		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';

	export default {
		name: "deviceModel",
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
		margin: 15px;
		border-radius: 4px;
		background-color: #fff;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>
