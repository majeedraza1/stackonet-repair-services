<template>
	<div class="stackonet-pricing-section">
		<div class="phone-services-container">
			<div class="device-issue-container">
				<pricing-accordion
					label="Choose device"
					:selected-issue="device.device_title"
					:active="activeDeviceAccordion"
					@toggle="activeDeviceAccordion = !activeDeviceAccordion"
				>
					<div class="device-item-container">
						<div class="device-item"
							 v-for="_device in devices"
							 @click="chooseDevice(_device)"
							 v-html="_device.device_title"
						></div>
					</div>
				</pricing-accordion>
				<pricing-accordion
					label="Select your model"
					:selected-issue="device_model.title"
					:active="activeModelAccordion"
					@toggle="activeModelAccordion = !activeModelAccordion"
				>
					<div class="device-item-container">
						<div class="device-item"
							 v-for="_model in devices_models"
							 @click="chooseModel(_model)"
							 v-html="_model.title"
						></div>
					</div>
				</pricing-accordion>
				<pricing-accordion
					label="Choose issue (pick up to 3)"
					multiple
					:selected-issues="selectedIssueNames"
					:active="activeIssuesAccordion"
					@toggle="activeIssuesAccordion = !activeIssuesAccordion"
				>
					<div class="device-item" v-for="_issue in _issues"
						 @click="chooseIssue(_issue)">
						<div class="device-item__inner" :class="issueClass(_issue)">
							<div class="device-item__icon">+</div>
							<div class="device-item__content">
								<div class="device-item__title">{{_issue.title}}</div>
								<div class="device-item__description"
									 v-if="_issue.description">{{_issue.description}}
								</div>
							</div>
						</div>
					</div>
				</pricing-accordion>
			</div>

			<div class="price-calculator">
				<div class="price-calculator__item price-calculator__subtotal">
					<span>Subtotal:</span>
					<span>${{subTotal}}</span>
				</div>
				<div class="price-calculator__item price-calculator__discount" v-if="discount">
					<span>Discount (15%):</span>
					<span>- ${{discount}}</span>
				</div>
				<div class="price-calculator__item price-calculator__total">
					<span>Our price:</span>
					<span>${{taxableAmount}}</span>
				</div>
			</div>
		</div>

		<div class="button-wrapper">
			<big-button @click="goToRepairPage">Repair My Device</big-button>
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
	import PricingAccordion from '../../components/PricingAccordion';

	export default {
		name: "Pricing",
		components: {BigButton, PricingAccordion},
		data() {
			return {
				selectedIssues: [],
				activeDeviceAccordion: false,
				activeModelAccordion: false,
				activeIssuesAccordion: false,
				cta_url: '',
				loading: true,
				devices: [],
				devices_models: [],
				device: {},
				device_model: {},
				device_issues: [],
			}
		},
		computed: {
			icons() {
				return window.Stackonet.icons;
			},
			issue_count() {
				return this.selectedIssues.length;
			},
			can_add_issue() {
				return this.issue_count < 3;
			},
			_issues() {
				let brokenPrice = this.device_model.broken_screen_price;
				let issues = this.device_issues.map(issue => {
					if (issue.title === 'Broken Screen') {
						issue.price = brokenPrice;
					}

					return issue;
				});

				if (brokenPrice) {
					issues.unshift({
						id: '',
						title: 'Front Glass',
						price: brokenPrice,
						description: 'Glass price is subject to undamaged display.',
					});
				}

				return issues;
			},
			subTotal() {
				let num = this.selectedIssues.reduce((prev, next) => prev + next.price, 0);
				return this.round(num, 2);
			},
			discount() {
				if (this.selectedIssues.length < 2) {
					return 0;
				}

				return this.round(this.subTotal * 0.15, 2);
			},
			taxableAmount() {
				let amount = (this.subTotal - this.discount);

				return amount > 0 ? this.round(amount, 2) : 0;
			},
			tax() {
				let amount = (this.taxableAmount * 0.07);

				return amount > 0 ? this.round(amount, 2) : 0;
			},
			totalPrice() {
				let amount = (this.taxableAmount - this.tax);

				return amount > 0 ? this.round(amount, 2) : 0;
			},
			selectedIssueNames() {
				let names = this.selectedIssues.map(issue => issue.title);
				return names.join(', ');
			}
		},
		mounted() {
			this.devices = window.Stackonet.devices;

			this.defaultDevice();

			let $ = window.jQuery;
			let container = $(this.$el).closest('.stackonet_pricing_container');
			if (container) {
				let cta_url = container.data('cta_url');
				if (cta_url) {
					this.cta_url = cta_url;
				}
			}
		},
		methods: {
			round(value, decimals) {
				return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
			},
			defaultDevice() {
				let device = this.devices[0];
				this.device = device;
				this.devices_models =device.device_models;
				this.device_model = device.device_models[0];
				this.device_issues = device.multi_issues;
				this.selectedIssues = [];
			},
			chooseDevice(device) {
				this.device = device;
				this.devices_models =device.device_models;
				this.device_model = device.device_models[0];
				this.device_issues = device.multi_issues;
				this.activeDeviceAccordion = false;
				this.selectedIssues = [];
			},
			chooseModel(model) {
				this.device_model = model;
				this.activeModelAccordion = false;
				this.selectedIssues = [];
			},
			chooseIssue(issue) {
				let issues = this.selectedIssues, index = issues.indexOf(issue);
				if (-1 === index) {
					if (this.can_add_issue) {
						issues.push(issue);
					}
				} else {
					issues.splice(index, 1);
				}

				this.selectedIssues = issues;
			},
			issueClass(issue) {
				let issues = this.selectedIssues,
					index = issues.indexOf(issue),
					isSelected = -1 !== index;
				return {
					'selected-issue': isSelected,
					'disabled-issue': !isSelected && !this.can_add_issue,
				}
			},
			goToRepairPage() {
				let url = this.cta_url, device_id = this.device.id, device_model = this.device_model.title;
				let tUrl = `${url}#/?device=${device_id}&model=${device_model}`;
				window.location.href = tUrl;
			}
		}
	}
</script>

<style lang="scss">
	.pricing-accordion__panel.is-panel-opened {
		padding: 0;
	}

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
			width: 100%;

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

		.device-item-container {
			transition: max-height .3s ease-in;
			max-height: 500px;

			.device-item {
				padding: 1rem;

				&__inner {
					display: flex;
				}

				&__icon {
					min-width: 1em;
				}

				&__content {
				}

				&__title {
				}

				&__description {
					font-size: .75em;
					color: rgba(#000, .85);
				}

				&:not(.disabled-issue):hover {
					background: #eee;
					cursor: pointer;
				}

				.selected-issue {
					color: #f58730;
				}

				.disabled-issue {
					color: #ccc;
				}
			}
		}

		@media screen and (min-width: 768px) {
			.service-includes-item {
				width: 33%;
			}
		}

		.price-calculator {
			display: flex;
			flex-direction: column;
			margin-top: 3rem;
			max-width: 430px;
			width: 100%;

			&__item {
				display: flex;
				font-size: 16px;
				justify-content: space-between;
			}
		}
	}
</style>
