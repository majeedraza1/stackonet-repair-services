<template>
	<div class="enter-details-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper">
				<span class="step-nav-title">Enter your contact details</span>
			</div>
		</div>
		<div class="enter-details-content-wrapper">
			<div class="animated-input" style="width: 100%;">
				<span class="blocking-span">
					<input type="text"
						   id="firstName"
						   name="firstName"
						   class="inputText"
						   placeholder=" " value=""
						   style="width: 100%;">
				</span>
				<span class="floating-label">First name</span>
			</div>

			<div class="animated-input" style="width: 100%;">
				<span class="blocking-span">
					<input type="text"
						   id="lastName"
						   name="lastName"
						   class="inputText"
						   placeholder=" " value=""
						   style="width: 100%;">
				</span>
				<span class="floating-label">Last name</span>
			</div>
			<div class="animated-input" style="width: 100%;">
				<span class="blocking-span">
					<input type="email" id="email"
						   name="email"
						   class="inputText"
						   placeholder=" " value=""
						   style="width: 100%;">
				</span>
				<span class="floating-label">Enter your email</span>
			</div>
			<div class="animated-input" style="width: 100%;">
				<span class="blocking-span">
					<input type="tel" id="phone"
						   name="instructions"
						   class="inputText"
						   placeholder=" " value=""
						   style="width: 100%;">
				</span>
				<span class="floating-label">Phone</span>
			</div>

			<div class="upsell-special-offer-wrapper desktop-mode">
				<div class="upsell-special-offer-title">Special offer</div>
				<div class="upsell-special-offer-item "><span>Tempered Glass</span>
					<button>+$10</button>
				</div>
			</div>

			<div class="enter-details-continue-button-wrapper desktop-mode">
				<div class="enter-details-continue-button ">Confirm Appointment</div>
			</div>

			<div class="userdetails-coupon-button">I have a coupon code</div>

			<div class="enter-details-continue-button-wrapper mobile-mode">
				<div class="enter-details-continue-button ">Continue</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "userDetails",
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
		},
		methods: {
			handleContinue() {
				this.$router.push('/thank-you');
			}
		}
	}
</script>

<style lang="scss">
	.enter-details-wrapper {
		width: 100%;
		padding: 0 10px;
		overflow: auto;
		box-sizing: border-box;
	}

	.enter-details-content-wrapper {
		padding-top: 10px;
		width: 100%;
		max-width: 520px;
		margin: 0 auto;
	}

	.enter-details-continue-button {
		color: #a9aeb3;
		background-color: #e1e8ec;
		border-radius: 5px;
		line-height: 64px;
		text-align: center;
		transition: all .4s ease;
		font-size: 18px;
		margin: 20px auto 40px;

		&.is-active {
			color: #0161c7;
			background-color: #12ffcd;
		}
	}

	.userdetails-coupon-button {
		margin: 30px auto 50px;
		text-align: center;
		color: #0161c7;
		font-size: 18px;
		font-weight: 300;
		line-height: 23px;
	}

	.upsell-special-offer-wrapper {
		box-sizing: border-box;
		border-radius: 10px;
		border: 1px solid #0161c7;
		padding: 13px;
		position: relative;
		max-width: 500px;
		margin: auto;
	}

	.upsell-special-offer-title {
		position: absolute;
		top: -10px;
		background: #eff2f5;
		padding: 0 15px 10px;
		color: #0161c7;
		font-size: 13px;
	}

	.upsell-special-offer-item span {
		font-size: 14px;
		color: #0161c7;
	}

	.upsell-special-offer-item button {
		float: right;
		background: none;
		outline: none;
		border: .5px solid #0161c7;
	}
</style>
