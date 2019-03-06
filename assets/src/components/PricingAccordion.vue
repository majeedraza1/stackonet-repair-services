<template>
	<div class="pricing-accordion device-issue-item" :class="{'is-opened':isActive}">
		<div class="pricing-accordion__heading" :class="{'is-heading-opened':isActive}"
			 @click="isActive = !isActive">
			<div class="pricing-accordion__title">
				<div class="pricing-accordion__sub-title">
					<div class="pricing-accordion__label">Choose device</div>
					<div v-if="!multiple" class="pricing-accordion__issue">iPhone</div>
				</div>
				<div v-if="multiple" class="pricing-accordion__issues">LCD install, Screen Broken</div>
			</div>

			<span class="pricing-accordion__icon">
				<svg class="pricing-accordion__arrow" width="24px" height="24px" viewBox="0 0 24 24"
					 xmlns="http://www.w3.org/2000/svg">
					<g><path fill="none" d="M0,0h24v24H0V0z"></path></g>
					<g v-if="isActive"><path d="M12,8l-6,6l1.41,1.41L12,10.83l4.59,4.58L18,14L12,8z"></path></g>
					<g v-else><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g>
				</svg>
			</span>
		</div>
		<div class="device-item-container pricing-accordion__panel" :class="{'is-panel-opened':isActive}">
			<slot>
				<div class="device-item">iPhone</div>
				<div class="device-item">iPad</div>
				<div class="device-item">Google</div>
				<div class="device-item">Motorola</div>
			</slot>
		</div>
	</div>
</template>

<script>
	export default {
		name: "PricingAccordion",
		props: {
			multiple: {type: Boolean, default: false}
		},
		data() {
			return {
				isActive: true,
			}
		}
	}
</script>

<style lang="scss">
	.pricing-accordion {
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

		&__heading {
			display: flex;
		}

		&__title {
			display: flex;
			flex-direction: column;
			width: calc(100% - 24px);
		}

		&__sub-title {
			display: flex;
			justify-content: space-between;
		}

		&__issue, &__issues {
			font-weight: bold;
		}

		&__icon {
			width: 24px;
		}

		&__label {
			display: inline-block;
			width: 60%;
			text-align: left;
			white-space: normal;
			vertical-align: text-bottom;
		}

		&__input {
			display: inline-block;
			width: 40%;
			vertical-align: top;
			text-align: right;
		}

		&__panel {
			display: none;

			&.is-panel-opened {
				padding: 15px;
				display: block;
			}
		}

		@media screen and (min-width: 600px) {
			min-width: 430px;
		}
	}
</style>
