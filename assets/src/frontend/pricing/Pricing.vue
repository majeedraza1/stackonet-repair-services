<template>
	<div class="stackonet-pricing-section">
		<div class="title-wrapper">
			<h2 class="pricing-title">SmartPhone and Tablet Repair Prices</h2>
			<div class="pricing-subtitle">
				<p>Select your device and issue for an ASAP Quote!</p>
			</div>
		</div>
		<div class="img-wrapper">
			<img src="https://d7gh5vrfihrl.cloudfront.net/website/pricing/phone_pricing.svg">
		</div>

		<div class="phone-services-container">
			<div class="device-issue-container">
				<div class="device-issue-item"><div class="device-label">Choose device</div><div class="device-issue-input">iPhone<img class="arrow-toggle" src="https://d7gh5vrfihrl.cloudfront.net/website/arrow.svg"></div><div class="device-item-container"><div class="device-item">iPhone</div><div class="device-item">iPad</div><div class="device-item">Google</div><div class="device-item">Motorola</div></div></div>
				<div class="device-issue-item"><div><div class="device-label">Choose issue</div><div class="device-issue-input"><img class="arrow-toggle" src="https://d7gh5vrfihrl.cloudfront.net/website/arrow.svg"></div><div class="multiple-issues">LCD install</div></div><div class="issue-item-container"><div class="device-item"><span class="selected-issue"> + <!-- -->LCD install</span></div></div></div>
			</div>

			<div class="price-container">
				<div>Our price:</div>
				<span>$109</span>
			</div>
		</div>

		<div class="button-wrapper">
			<big-button>Repair My Device</big-button>
		</div>

		<div class="service-includes-container">
			<div class="pricing-subtitle">
				<p class="body-text-big">Service Includes</p>
			</div>

			<div class="service-includes-items">
				<div class="service-includes-item">
					<img :src="icons.checkCircle">
					<span>Well travel to you anywhere!</span>
				</div>
				<div class="service-includes-item">
					<img :src="icons.checkCircle">
					<span>High Quality parts</span>
				</div>
				<div class="service-includes-item">
					<img :src="icons.checkCircle">
					<span>Life Time Warranty</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton';

	export default {
		name: "Pricing",
		components: {BigButton},
		computed: {
			icons() {
				return window.Stackonet.icons;
			},
		},
	}
</script>

<style lang="scss">
	.stackonet-pricing-section {
		border-bottom: 2px solid #d8d8d8;
		display: flex;
		flex-direction: column;
		margin-left: auto;
		margin-right: auto;
		max-width: 750px;
		padding: 15px;
		width: 100%;

		.body-text-big {
			margin: 0;
			padding: 0;
		}

		.pricing-title {
			color: #000;
			font-size: 36px;
			font-weight: 500;
			font-style: normal;
			text-align: center;
		}

		.pricing-subtitle {
			color: #696969;
			font-size: 22px;
			line-height: 1.6;
			margin: 12px auto 30px;
			text-align: center;
		}

		.service-includes-container {
			margin: 50px 0;
		}

		.service-includes-items {
			display: flex;
			flex-wrap: wrap;
		}

		.service-includes-item {
			display: flex;
			flex-direction: column;
			width: 100%;
			align-items: center;
			justify-content: center;
			padding: 1rem;

			img {
				width: 32px;
				height: 32px;
				display: block;
				margin: 0 auto 15px;
			}
		}

		.img-wrapper {
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.button-wrapper {
			display: flex;
			justify-content: center;
			padding: 15px;

			.big-button-wrapper {
				min-width: 250px;
				max-width: 300px;

			}
		}

		.phone-services-container {
			align-items: center;
			display: flex;
			flex-direction: column;
			justify-content: center;

			.price-container {
				font-size: 36px;
				margin: 40px 0 0;

				div {
					display: inline-block;
				}

				span {
					margin: 0 0 0 4px;
				}
			}
		}

		.device-issue-container {
			.device-issue-item {
				display: block;
				max-width: 430px;
				border-bottom: 1px solid #d5d5d5;
				font-size: 16px;
				font-weight: 400;
				font-style: normal;
				font-stretch: normal;
				line-height: 1.44;
				letter-spacing: normal;
				color: #000;
				margin: 0 auto;
				padding: 20px 0;

				.device-label {
					display: inline-block;
					width: 60%;
					text-align: left;
					white-space: normal;
					vertical-align: text-bottom;
				}

				.device-issue-input {
					display: inline-block;
					width: 40%;
					vertical-align: top;
					text-align: right;

					.arrow-toggle {
						width: 16px;
						transform: rotate(90deg);
						margin: 0 0 0 8px;
						padding: 0 0 0 5px;
						transition: all .1s linear;
					}
				}
			}
		}

		@media screen and (min-width: 768px) {
			.service-includes-item {
				width: 33%;
			}
		}
	}
</style>
