<template>
	<div class="device-model-list">
		<div class="select-device-content-container">
			<template v-for="device in models">
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
	export default {
		name: "deviceModel",
		data() {
			return {}
		},
		computed: {
			models() {
				return this.$store.state.deviceModels;
			},
			hasModels() {
				return !!(this.models && this.models.length);
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);

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
		height: 75px;
		width: 175px;
		margin: 20px;
		border-radius: 4px;
		background-color: #fff;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>
