<template>
	<div class="select-issue-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper"><span class="step-nav-title">Is your screen broken?</span></div>
		</div>
		<div class="info-box-wrapper">
			<div class="info-box-inner-wrapper">
				<span>Parts and labor come with a </span><span><b>lifetime guarantee.</b></span>
			</div>
		</div>
		<div class="select-issue-content-container">

			<template v-if="broken_screen_price">
				<div class="scale-on-mount scale-on-mount-active" @click="setScreenCracked('yes')">
					<div class="screen-cracked-button-item hoverable">
						<div class="screen-cracked-button-item-price"><b>${{broken_screen_price}}</b></div>
						<div class="screen-cracked-button-image-wrapper">
							<svg xmlns="http://www.w3.org/2000/svg" width="30" height="53">
								<use xlink:href="#icon-screen-broken-yes"></use>
							</svg>
						</div>
						<p>Yes</p>
					</div>
				</div>
			</template>

			<div class="scale-on-mount scale-on-mount-active" @click="setScreenCracked('no')">
				<div class="screen-cracked-button-item hoverable">
					<div class="screen-cracked-button-image-wrapper">
						<svg xmlns="http://www.w3.org/2000/svg" width="30" height="53">
							<use xlink:href="#icon-screen-broken-no"></use>
						</svg>
					</div>
					<p>No</p>
				</div>
			</div>
			<div class="scale-on-mount scale-on-mount-active" @click="setScreenCracked('multiple')">
				<div class="screen-cracked-button-item hoverable">
					<div class="screen-cracked-button-image-wrapper">
						<svg xmlns="http://www.w3.org/2000/svg" width="66" height="47">
							<use xlink:href="#icon-screen-multi-issue"></use>
						</svg>
					</div>
					<p>Multiple</p>
					<p>Issues</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "screenCracked",
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);

			// If no models, redirect one step back
			if (!this.hasZipCode) {
				this.$router.push('/zip-code');
			}
		},
		computed: {
			zipCode() {
				return this.$store.state.zipCode;
			},
			hasZipCode() {
				return !!(this.zipCode && this.zipCode.length);
			},
			screenCracked() {
				return this.$store.state.screenCracked;
			},
			device() {
				return this.$store.state.device;
			},
			broken_screen_label() {
				if (this.device && this.device.broken_screen_label) {
					return this.device.broken_screen_label;
				}

				return '';
			},
			broken_screen_price() {
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
					this.$router.push('/select-time');
				} else {
					this.$router.push('/select-issue');
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

	.step-nav-wrapper {
		display: flex;
		justify-content: space-around;
		margin: 30px 0;
	}

	.step-nav-title {
		flex: 1 1;
		text-align: center;
		font-size: 22px;
		color: #3d4248;
	}

	.info-box-wrapper {
		text-align: center;
		position: relative;
		top: -10px;
		padding: 0 10px;
	}

	.info-box-inner-wrapper {
		display: inline-block;
		background: #fff;
		padding: 5px 15px;
		color: #0161c7;
		font-size: 16px;
		line-height: 20px;
		border-radius: 15px;
		box-shadow: 0 1px 0 0 #12ffcd;
		margin-bottom: 20px;
	}

	.select-issue-content-container {
		display: flex;
		justify-content: center;
		margin: 0 auto 3rem;
		flex-wrap: wrap;
		max-width: 700px;
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

	.screen-cracked-button-item {
		position: relative;
		box-sizing: border-box;
		width: 160px;
		height: 160px;
		background: #fff;
		text-align: center;
		display: inline-block;
		margin: 0 15px;
		border-radius: 6px;
		padding-top: 15px;
	}

	.screen-cracked-button-item-price {
		position: absolute;
		top: -15px;
		left: -15px;
		line-height: 40px;
		height: 40px;
		width: 40px;
		border-radius: 50%;
		background: #0161c7;
		font-size: 13px;
		text-align: center;
		color: #fff;
		border: 2px solid #12ffcd;
	}

	.screen-cracked-button-image-wrapper {
		min-height: 85px;
	}

	.screen-cracked-button-item p {
		line-height: 20px;
		margin: 0;
	}
</style>
