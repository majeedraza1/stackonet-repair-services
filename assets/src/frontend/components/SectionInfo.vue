<template>
	<div class="shapla-section-info">
		<div class="shapla-section-info__content">
			<slot></slot>
		</div>
	</div>
</template>

<script>
	export default {
		name: "SectionInfo"
	}
</script>

<style lang="scss">
	.shapla-section-info {
		text-align: center;
		position: relative;
		padding: 0 10px;

		&__content {
			background: #fff;
			box-shadow: 0 2px 0 0 #f58730;
			border-radius: 15px;
			color: #f9a73b;
			display: inline-block;
			font-size: 16px;
			line-height: 1.6;
			margin-bottom: 20px !important;
			max-width: 650px;
			padding: 5px 15px;
		}
	}
</style>
