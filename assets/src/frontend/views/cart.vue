<template>
	<div class="my-cart-wrapper" :class="{'is-active':showCart}">
		<div class="my-cart-content-wrapper">

			<div class="my-cart-small-items-container" style="padding-top: 20px;">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<svg width="20px" height="20px" xmlns="http://www.w3.org/2000/svg">
							<use xlink:href="#icon-phone"></use>
						</svg>
						<span class="time-title">Phone Repair</span>
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

			<div class="my-cart-small-items-container">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<svg width="20px" height="20px" xmlns="http://www.w3.org/2000/svg">
							<use xlink:href="#icon-clock"></use>
						</svg>
						<span class="time-title">Time</span>
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

			<div class="my-cart-small-items-container">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<svg width="20px" height="20px" version="1.1" xmlns="http://www.w3.org/2000/svg">
							<use xlink:href="#icon-map"></use>
						</svg>
						<span class="time-title">Location</span>
					</div>
					<div class="my-cart-small-items-text" v-html="address"></div>
				</div>
			</div>

			<div class="my-cart-small-items-container">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div class="cart-title-container">
						<svg width="20px" height="20px" version="1.1" xmlns="http://www.w3.org/2000/svg">
							<use xlink:href="#icon-contact"></use>
						</svg>
						<span class="time-title">Contact Details</span>
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
	export default {
		name: "cart",
		computed: {
			device() {
				return this.$store.state.device;
			},
			hasDevice() {
				return !!(this.device && this.device.device_title);
			},
			deviceModel() {
				return this.$store.state.deviceModel;
			},
			deviceColor() {
				return this.$store.state.deviceColor;
			},
			hasDeviceColor() {
				return !!(this.deviceColor && this.deviceColor.title);
			},
			issues() {
				return this.$store.state.issues;
			},
			totalPrice() {
				return this.issues.reduce((prev, next) => prev + next.price, 0);
			},
			date() {
				return this.$store.state.date;
			},
			timeRange() {
				return this.$store.state.timeRange;
			},
			address() {
				return this.$store.state.address;
			},
			firstName() {
				return this.$store.state.firstName;
			},
			lastName() {
				return this.$store.state.lastName;
			},
			emailAddress() {
				return this.$store.state.emailAddress;
			},
			phone() {
				return this.$store.state.phone;
			},
			showCart() {
				return this.$store.state.showCart;
			},
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
		top: 104px;
		right: 0;
		height: calc(100% - 104px);
		box-sizing: border-box;
		width: 355px;
		background-color: #fff;
		box-shadow: -50px 7px 50px 0 hsla(0, 0%, 79%, .25);
		display: none;
		flex-direction: column;
		transition: all .4s ease;
		overflow: auto;
		z-index: 9999;

		&.is-active {
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
</style>
