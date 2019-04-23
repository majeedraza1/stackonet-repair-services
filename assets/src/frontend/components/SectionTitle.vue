<template>
	<div class="shapla-section-title">
		<div class="shapla-section-title__content">
			<slot></slot>
		</div>
	</div>
</template>

<script>
	export default {
		name: "SectionTitle"
	}
</script>

<style lang="scss">
	.shapla-section-title {
		display: flex;
		justify-content: space-around;

		&__content {
			flex: 1 1;
			text-align: center;
			font-size: 22px;
			font-weight: bold;
			color: #3d4248;
			margin: 0 0 1em !important;
		}
	}
</style>
