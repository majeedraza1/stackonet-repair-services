<template>
	<div class="thank-you-content">
		<svg class="thank-you-check" width="55" height="55" version="1.1" xmlns="http://www.w3.org/2000/svg"
			 xmlns:xlink="http://www.w3.org/1999/xlink">
			<use xlink:href="#icon-success"></use>
		</svg>
		<div class="title-wrapper theme-dark centered small">
			<h1 class="h1-headline">Thank you!</h1>
		</div>
		<div class="theme-dark centered">
			<p class="body-text-big">We've received your appointment request.</p>
		</div>
		<div class="thank-you-rows-wrapper">
			<div class="section-item-container">
				<div class="section-item-img">
					<svg width="28px" height="18px" version="1.1" xmlns="http://www.w3.org/2000/svg"
						 xmlns:xlink="http://www.w3.org/1999/xlink">
						<use xlink:href="#icon-envelope"></use>
					</svg>
				</div>
				<div class="section-item-text">You will recieve a confirmation email and SMS with all the details.</div>
			</div>
			<div class="section-item-container">
				<div class="section-item-img">
					<svg width="23px" height="15px" version="1.1" xmlns="http://www.w3.org/2000/svg"
						 xmlns:xlink="http://www.w3.org/1999/xlink">
						<use xlink:href="#icon-check"></use>
					</svg>
				</div>
				<div class="section-item-text">We’re selecting the best technician in your area and will confirm your
					appointment soon.
				</div>
			</div>
			<div class="section-item-container">
				<div class="section-item-img">
					<svg width="19px" height="26px" version="1.1" xmlns="http://www.w3.org/2000/svg"
						 xmlns:xlink="http://www.w3.org/1999/xlink">
						<use xlink:href="#icon-location"></use>
					</svg>
				</div>
				<div class="section-item-text">Once we assign a technician, we will provide the technician's name and
					arrival time by email and SMS.
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "thankyou",
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
		},
	}
</script>

<style lang="scss">
	.thank-you-content {
		.thank-you-check {
			display: block;
			text-align: center;
			margin: 25px auto 15px;
		}

		.centered {
			text-align: center;
		}

		.thank-you-rows-wrapper {
			display: flex;
			flex-direction: column;
		}

		.section-item-container {
			max-width: 500px;
			width: 100%;
			display: flex;
			background-color: rgb(255, 255, 255);
			box-shadow: rgba(0, 0, 0, 0.04) 0 2px 4px 0;
			border-radius: 6px;
			margin: 10px auto;
			padding: 35px 0;

			.section-item-img {
				width: 125px;
				text-align: center;
				margin: auto;
			}

			.section-item-text {
				color: rgb(95, 95, 95);
				font-size: 16px;
				line-height: 20px;
				width: 90%;
				margin: 0 40px 0 0;
			}
		}
	}
</style>
