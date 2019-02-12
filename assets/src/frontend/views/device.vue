<template>
	<div class="device-list">
		<div class="select-device-content-container">
			<template v-for="device in devices">
				<div class="scale-on-mount scale-on-mount-active" @click="chooseDeviceModel(device)">
					<div class="phone-family-item-wrapper hoverable">
						<div class="phone-family-image-wrapper">
							<img :src="device.image.src" :alt="device.device_title">
						</div>
						<div class="phone-family-item-text-wrapper" v-text="device.title"></div>
					</div>
				</div>
			</template>
		</div>
	</div>
</template>

<script>
	export default {
		name: "device",
		data() {
			return {}
		},
		computed: {
			devices() {
				return this.$store.state.devices;
			}
		},
		mounted() {
			let devices = window.Stackonet.devices;
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_DEVICES', devices);
			this.$store.commit('SET_SHOW_CART', false);
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

	.scale-on-mount {
		transform: scale(0.5);
		opacity: 0;
		display: inline-block;
		transition: all 0.4s ease-in-out 0s;

		&-active {
			opacity: 1;
			transform: scale(1);
		}
	}

	.phone-family-item-wrapper {
		height: 150px;
		width: 150px;
		background-color: rgb(255, 255, 255);
		display: flex;
		flex-direction: column;
		border-radius: 6px;
		margin: 15px;
	}

	.phone-family-item-wrapper:hover {
		cursor: pointer;
	}

	@media (min-width: 1250px) {
		.hoverable {
			box-sizing: border-box;
			transition: all 0.4s ease-in-out 0s;
			border-width: 2px;
			border-style: solid;
			border-color: transparent;
			border-image: initial;

			&:hover {
				color: rgb(1, 97, 199);
				border-width: 2px;
				border-style: solid;
				border-color: rgba(18, 255, 205, 0.5);
				border-image: initial;
				background: rgba(1, 97, 199, 0.05);
			}
		}
	}

	.phone-family-image-wrapper {
		display: flex;
		justify-content: center;
		align-items: center;
		flex: 1 1 0%;
	}

	.phone-family-image-wrapper img {
		max-width: 80px;
		max-height: 60px;
		position: relative;
		top: 10px;
	}

	.phone-family-item-text-wrapper {
		line-height: 60px;
		text-align: center;
	}
</style>
