<template>
	<div class="my-cart-wrapper" :class="{'is-active':showCart}">
		<div class="my-cart-toggle-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 576 512">
				<path fill="currentColor"
					  d="M551.991 64H129.28l-8.329-44.423C118.822 8.226 108.911 0 97.362 0H12C5.373 0 0 5.373 0 12v8c0 6.627 5.373 12 12 12h78.72l69.927 372.946C150.305 416.314 144 431.42 144 448c0 35.346 28.654 64 64 64s64-28.654 64-64a63.681 63.681 0 0 0-8.583-32h145.167a63.681 63.681 0 0 0-8.583 32c0 35.346 28.654 64 64 64 35.346 0 64-28.654 64-64 0-17.993-7.435-34.24-19.388-45.868C506.022 391.891 496.76 384 485.328 384H189.28l-12-64h331.381c11.368 0 21.177-7.976 23.496-19.105l43.331-208C578.592 77.991 567.215 64 551.991 64zM240 448c0 17.645-14.355 32-32 32s-32-14.355-32-32 14.355-32 32-32 32 14.355 32 32zm224 32c-17.645 0-32-14.355-32-32s14.355-32 32-32 32 14.355 32 32-14.355 32-32 32zm38.156-192H171.28l-36-192h406.876l-40 192z"></path>
			</svg>
		</div>
		<div class="my-cart-content-wrapper">
			<div class="my-cart-small-items-container" style="padding-top: 20px;" v-show="showPhoneRepairSection">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<div class="cart-title-left">
							<img :src="icon_src" :alt="device.device_title" style="width: auto;max-height: 40px;">
							<span class="cart-title">Phone Repair</span>
						</div>
						<div class="my-cart-small-items-edit" v-if="totalPrice" @click="editDevice">
							<svg width="12px" height="11px" xmlns="http://www.w3.org/2000/svg">
								<use xlink:href="#icon-pen"></use>
							</svg>
							<span>Edit</span>
						</div>
					</div>
					<div>
						<div class="my-cart-device-section-phone-item">
							<div v-if="device" v-text="device.device_title"></div>
							<div v-if="deviceModel" v-text="deviceModel.title"></div>
							<div v-if="hasDeviceColor">{{deviceColor.title}} - {{deviceColor.subtitle}}</div>
							<div></div>
							<div>
								<div class="my-cart-device-section-phone-issue-wrapper" v-for="issue in issues"
									 :key="issue.id">
									<span>{{issue.title}}</span>
									<span class="float-right-price">${{issue.price}}</span>
								</div>
							</div>
						</div>
					</div>
					<div class="my-cart-small-price-section" v-if="totalPrice">
						<div class="my-cart-small-total-price">
							<span>Subtotal </span>
							<span class="my-cart-small-text-bold">${{totalPrice}}</span>
						</div>
						<div class="my-cart-small-total-price">
							<span>Tax (7%)</span>
							<span class="my-cart-small-text-bold">${{totalTax}}</span>
						</div>
						<div class="my-cart-small-total-price">
							<span>Total Price</span>
							<span class="my-cart-small-text-bold">${{totalPrice + totalTax}}</span>
						</div>
					</div>
				</div>
			</div>

			<div class="my-cart-small-items-container" v-show="showDateTimeSection">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<div class="cart-title-left">
							<img :src="icons.clock" alt="Icon clock" style="width: 20px;height: 20px;">
							<span class="cart-title">Time</span>
						</div>
						<div class="my-cart-small-items-edit" v-if="date" @click="editTime">
							<svg width="12px" height="11px" version="1.1" xmlns="http://www.w3.org/2000/svg">
								<use xlink:href="#icon-pen"></use>
							</svg>
							<span>Edit</span>
						</div>
					</div>
					<div class="my-cart-small-items-text">{{date}}, {{timeRange}}</div>
				</div>
			</div>

			<div class="my-cart-small-items-container" v-if="showLocationSection">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<div class="cart-title-left">
							<img :src="icons.map" alt="Icon map" style="width: 20px;height: 20px;">
							<span class="cart-title">Location</span>
						</div>
					</div>
					<div class="my-cart-small-items-text" v-html="address"></div>
				</div>
			</div>

			<div class="my-cart-small-items-container" v-if="showContactDetailsSection">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<div class="cart-title-left">
							<img :src="icons.contact" alt="Icon contact" style="width: 20px;height: 20px;">
							<span class="cart-title">Contact Details</span>
						</div>
					</div>
					<div class="my-cart-small-items-text">
						<div v-text="firstName"></div>
						<div v-text="emailAddress"></div>
						<div v-text="phone"></div>
					</div>
				</div>
			</div>

		</div>
	</div>
</template>

<script>
	import {mapState} from 'vuex';

	export default {
		name: "cart",
		computed: {
			...mapState(['device', 'deviceModel', 'deviceColor', 'issues', 'date', 'timeRange', 'address',
				'firstName', 'lastName', 'emailAddress', 'phone', 'showCart', 'addressObject']),
			icons() {
				return window.Stackonet.icons;
			},
			icon_src() {
				if (this.device.image) {
					return this.device.image.src;
				}
				return this.icons.phone;
			},
			hasDevice() {
				return !!(this.device && this.device.device_title);
			},
			hasDeviceColor() {
				return !!(this.deviceColor && this.deviceColor.title);
			},
			totalPrice() {
				return this.issues.reduce((prev, next) => prev + next.price, 0);
			},
			totalTax() {
				if (this.totalPrice < 1) {
					return 0;
				}
				let tax = (this.totalPrice * .07), places = 2;
				return +(Math.round(tax + "e+" + places) + "e-" + places);
			},
			hasAddress() {
				return !!(this.address && this.address.length);
			},
			showPhoneRepairSection() {
				return Object.keys(this.device).length;
			},
			showDateTimeSection() {
				return this.totalPrice;
			},
			showLocationSection() {
				return !!(this.addressObject && this.address);
			},
			showContactDetailsSection() {
				return this.hasAddress;
			}
		},
		methods: {
			editDevice() {
				this.$store.commit('SET_DEVICE', {});
				this.$store.commit('SET_DEVICE_MODEL', {});
				this.$store.commit('SET_DEVICE_COLOR', {});
				this.$router.push('/');
			},
			editTime() {
				this.$store.commit('SET_DATE', '');
				this.$store.commit('SET_TIME_RANGE', '');
				this.$router.push('/select-time');
			}
		}
	}
</script>

<style lang="scss">
	.my-cart-wrapper {
		position: fixed;
		right: -355px;
		top: 68px;
		height: calc(100% - 68px);
		box-sizing: border-box;
		width: 355px;
		background-color: #fff;
		box-shadow: -5px 2px 5px 0 rgba(#c9c9c9, 0.25);
		flex-direction: column;
		transition: all .4s ease;
		z-index: 9999;

		.admin-bar & {
			top: 100px;
			height: calc(100% - 100px);
		}

		.is-cart-active & {
			display: flex;
			right: 0;
		}

		.is-small-screen.is-small-screen-active & {
			// top: 0;
		}
	}

	.my-cart-toggle-icon {
		display: none;
		justify-content: center;
		align-items: center;
		position: absolute;
		left: -48px;
		top: 30px;
		background: #fff;
		font-size: 20px;
		padding: 5px;
		width: 48px;
		height: 48px;
		text-align: center;
		box-shadow: -3px 1px 3px 0 rgba(#c9c9c9, 0.25);

		.is-small-screen & {
			display: flex;
		}
	}

	.my-cart-small-items-container {
		padding: 10px 20px;
	}

	.my-cart-small-items-wrapper-colored {
		background-color: #eff2f5;
		border-radius: 6px;
	}

	.my-cart-small-items-wrapper {
		padding: 15px;
		color: #46515b;

		.time-title {
			width: 50px;
			margin-left: 10px;
			font-size: 19px;
			line-height: 21px;
		}
	}

	.cart-title {
		margin-left: 1rem;
		font-size: 20px;
		line-height: 1.2;
	}

	.my-cart-small-items-edit {
		color: #f58730;
		cursor: pointer;
		float: right;
		font-size: 13px;

		svg {
			margin-right: 3px;
		}
	}

	.my-cart-device-section-phone-item {
		padding: 10px 0;
		margin: 10px 0;

		> div {
			line-height: 22px;
		}
	}

	.my-cart-small-price-section {
		border-top: 2px solid #e0e4e7;
		padding-top: 10px;
		margin-top: 20px;
	}

	.my-cart-small-total-price {
		font-size: 18px;
		align-items: center;
		margin-top: 5px;
		height: 25px;
		display: flex;
		justify-content: space-between;
	}

	.float-right-price {
		float: right;
	}

	.cart-title-container {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;

		.cart-title-left {
			display: inline-flex;
		}

		img {
			height: initial;
		}
	}
</style>
