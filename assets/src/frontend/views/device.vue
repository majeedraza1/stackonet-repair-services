<template>
	<div class="device-list">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper">
				<span class="step-nav-title">Which device would you like repaired?</span>
			</div>
		</div>
		<div class="info-box-wrapper">
			<div class="info-box-inner-wrapper">
				Whether you’re at home, the office or even your favorite coffee shop. We come to you and repair your device on the spot!
			</div>
		</div>
		<div class="select-device-content-container">
			<template v-for="device in _devices">
				<div class="scale-on-mount scale-on-mount-active" @click="chooseDeviceModel(device)">
					<div class="phone-family-item-wrapper hoverable">
						<div class="phone-family-image-wrapper">
							<img :src="device.image.src" :alt="device.device_title">
						</div>
						<div class="phone-family-item-text-wrapper" v-text="device.device_title"></div>
					</div>
				</div>
			</template>
		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';

	export default {
		name: "device",
		data() {
			return {}
		},
		computed: {
			...mapState(['devices', 'group']),
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
		height: 140px;
		width: 140px;
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

			&:not(.time-content-box-active):not(:disabled):hover {
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
		font-weight: bold;
	}
</style>
