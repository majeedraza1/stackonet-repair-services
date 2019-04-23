<template>
	<div class="select-issue-wrapper">

		<section-title>Is your screen broken?</section-title>

		<section-info>
			<span><b>Lifetime warranty</b></span><span> on all parts and labor</span>
		</section-info>

		<div class="select-issue-content-container">

			<template v-if="broken_screen_price">
				<div class="scale-on-mount scale-on-mount-active" @click="setScreenCracked('yes')">
					<div class="screen-cracked-button-item hoverable">
						<div class="screen-cracked-button-item-price"><b>${{broken_screen_price}}</b></div>
						<div class="screen-cracked-button-image-wrapper">
							<img :src="icons.screenBrokenYes" alt="Screen Broken" width="30" height="53">
						</div>
						<p>Yes</p>
					</div>
				</div>
			</template>

			<div class="scale-on-mount scale-on-mount-active" @click="setScreenCracked('no')">
				<div class="screen-cracked-button-item hoverable">
					<div class="screen-cracked-button-image-wrapper">
						<img :src="icons.screenBrokenNo" alt="Screen Not Broken" width="30" height="53">
					</div>
					<p>No</p>
				</div>
			</div>
			<div class="scale-on-mount scale-on-mount-active" @click="setScreenCracked('multiple')">
				<div class="screen-cracked-button-item hoverable">
					<div class="screen-cracked-button-image-wrapper">
						<img :src="icons.screenMultiIssue" alt="Screen Multiple Issues" width="30" height="53">
					</div>
					<p>Multiple</p>
					<p>Issues</p>
				</div>
			</div>
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
		name: "screenCracked",
		components: {SectionInfo, SectionTitle, SectionHelp},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);

			// If no models, redirect one step back
			if (!this.hasZipCode) {
				this.$router.push('/zip-code');
			}
		},
		computed: {
			...mapState(['zipCode', 'device', 'screenCracked', 'deviceModel']),
			icons() {
				return window.Stackonet.icons;
			},
			hasZipCode() {
				return !!(this.zipCode && this.zipCode.length);
			},
			broken_screen_label() {
				if (this.device && this.device.broken_screen_label) {
					return this.device.broken_screen_label;
				}

				return '';
			},
			broken_screen_price() {
				if (this.deviceModel && this.deviceModel.broken_screen_price) {
					return this.deviceModel.broken_screen_price;
				}
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
		cursor: pointer;
	}

	.screen-cracked-button-item {
		position: relative;
		box-sizing: border-box;
		width: 140px;
		height: 140px;
		background: #fff;
		text-align: center;
		display: inline-block;
		margin: 15px;
		border-radius: 6px;
		padding-top: 15px;
	}

	.screen-cracked-button-item-price {
		position: absolute;
		top: -15px;
		left: -15px;
		line-height: 40px;
		height: 40px;
		min-width: 40px;
		border-radius: 10px;
		background: #0161c7;
		font-size: 13px;
		text-align: center;
		color: #fff;
		border: 2px solid #12ffcd;

		b {
			display: block;
			padding-left: 3px;
			padding-right: 3px;
		}
	}

	.screen-cracked-button-image-wrapper {
		min-height: 85px;
	}

	.screen-cracked-button-item p {
		line-height: 20px;
		margin: 0;
	}
</style>
