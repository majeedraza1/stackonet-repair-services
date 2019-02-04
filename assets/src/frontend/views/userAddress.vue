<template>
	<div class="select-address-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper"><span class="step-nav-title">Where can we meet you?</span></div>
		</div>
		<div class="select-address-content-wrapper">
			<div class="animated-input" style="width: 100%;">
			<span class="blocking-span">
				<div>
				<input placeholder=""
					   types="address"
					   type="text"
					   id="address"
					   name="address"
					   class="inputText"
					   autocomplete="off"
					   role="combobox"
					   aria-autocomplete="list"
					   aria-expanded="false"
					   value=""
					   style="width: 100%;">
				</div>
			</span>
				<span class="floating-label floating-label-focused">Enter exact address</span>
			</div>

			<div class="animated-input" style="width: 100%;">
			<span class="blocking-span">
				<input type="text" id="additional"
					   name="additional"
					   class="data-hj-whitelist inputText"
					   placeholder=" " value=""
					   style="width: 100%;">
			</span>
				<span class="floating-label">Apt / Suite / Floor No. (optional)</span>
			</div>

			<div class="animated-input" style="width: 100%;">
			<span class="blocking-span">
				<input type="text"
					   id="instructions"
					   name="instructions"
					   class="data-hj-whitelist inputText"
					   placeholder=" " value=""
					   style="width: 100%;">
			</span>
				<span class="floating-label">Add instructions (optional)</span></div>
			<div>
				<div class="select-address-continue-button ">Continue</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "userAddress",
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
		},
		methods: {
			handleContinue() {
				this.$router.push('/user-details');
			}
		}
	}
</script>

<style lang="scss">
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

	.select-address-content-wrapper {
		padding-top: 10px;
		width: 100%;
		max-width: 520px;
		margin: 0 auto;
	}

	.select-address-continue-button {
		width: 100%;
		color: #a9aeb3;
		background-color: #e1e8ec;
		border-radius: 5px;
		line-height: 64px;
		text-align: center;
		transition: all .4s ease;
		font-size: 18px;
		margin: 13px auto 40px;

		&.select-address-continue-button-active {
			color: #0161c7;
			background-color: #12ffcd;
		}
	}
</style>
