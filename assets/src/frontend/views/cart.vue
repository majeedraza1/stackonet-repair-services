<template>
	<div class="my-cart-wrapper" :class="{'is-active':showCart}">
		<div class="my-cart-toggle-icon">
			<i class="icon-basket"></i>
		</div>
		<div class="my-cart-content-wrapper">
			<div class="my-cart-small-items-container" style="padding-top: 20px;" v-show="showPhoneRepairSection">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<div class="cart-title-left">
							<img :src="icons.phone" alt="Icon phone" style="width: 20px;height: 40px;">
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
					<div class="my-cart-small-price-section">
						<div class="my-cart-small-total-price">
							<span>Total Price</span>
							<span class="my-cart-small-text-bold">${{totalPrice}}</span>
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
			hasDevice() {
				return !!(this.device && this.device.device_title);
			},
			hasDeviceColor() {
				return !!(this.deviceColor && this.deviceColor.title);
			},
			totalPrice() {
				return this.issues.reduce((prev, next) => prev + next.price, 0);
			},
			hasAddress() {
				return !!(this.address && this.address.length);
			},
			showPhoneRepairSection() {
				return this.device;
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
	}

	.my-cart-toggle-icon {
		display: none;
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
			display: block;
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
		color: #0161c7;
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
