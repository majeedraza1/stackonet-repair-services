<template>
	<div class="device-colors-list">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper">
				<span class="step-nav-title">What Color is your device?</span>
			</div>
		</div>
		<div class="info-box-wrapper">
			<div class="info-box-inner-wrapper">Amazing Service Affordable Prices</div>
		</div>
		<div class="select-color-content-container">
			<template v-for="color in deviceColors">
				<div class="scale-on-mount scale-on-mount-active" @click="chooseDeviceColor(color)">
					<div class="phone-color-item-wrapper hoverable">
						<div class="phone-color-item-color-circle" :style="{backgroundColor: color.color}"></div>
						<div class="phone-color-item-color-title" v-text="color.title"></div>
						<div class="phone-color-item-color-subtitle" v-text="color.subtitle"></div>
					</div>
				</div>
			</template>
		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';

	export default {
		name: "deviceColor",
		data() {
			return {}
		},
		computed: {
			...mapState(['deviceColors']),
			hasColors() {
				return !!(this.deviceColors && this.deviceColors.length);
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);

			// If no models, redirect one step back
			if (!this.hasColors) {
				this.$router.push('/device-model');
			}
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

	.scale-on-mount {
		transform: scale(.5);
		opacity: 0;
		display: inline-block;
		transition: all .4s ease-in-out;
	}

	.scale-on-mount-active {
		opacity: 1;
		transform: scale(1);
	}

	.phone-color-item-wrapper {
		cursor: pointer;
		min-width: 140px;
		height: 120px;
		background: #fff;
		color: #333333;
		margin: 15px;
		border-radius: 5px;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}

	.phone-color-item-color-circle {
		height: 25px;
		width: 25px;
		border-radius: 50%;
		margin-bottom: 10px;
	}

	.phone-color-item-color-subtitle {
		margin-top: 5px;
		font-size: 12px;
	}
</style>
