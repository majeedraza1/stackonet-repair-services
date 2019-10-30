<template>
	<div class="shapla-help-line">
		<div class="shapla-help-line__content">
			<slot>Need help? Call us at <a href="tel:561-349-4835">561-349-4835</a></slot>
		</div>
	</div>
</template>

<script>
	export default {
		name: "SectionHelp"
	}
</script>

<style lang="scss">
	.shapla-help-line {
		display: flex;
		justify-content: space-around;
		margin: 2em auto !important;

		&__content {
			font-size: 1em;
			font-weight: bold;
		}
	}
</style>
