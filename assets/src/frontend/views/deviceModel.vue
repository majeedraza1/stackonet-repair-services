<template>
	<div class="device-model-list">
		<div class="select-device-content-container">
			<template v-for="device in models">
				<div class="scale-on-mount scale-on-mount-active" @click="chooseDeviceModel">
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
			return {
				models: [],
			}
		},
		mounted() {
			this.models = window.Stackonet.deviceModel;
		},
		methods: {
			chooseDeviceModel() {
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
