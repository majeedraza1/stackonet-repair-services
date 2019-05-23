<template>
	<div class="latest-status-box" :style="{borderColor:borderColor}" @click="$emit('click')">
		<div class="latest-status-box__content">
			<div class="latest-status-box__number">{{number}}</div>
			<div class="latest-status-box__label">{{label}}</div>
		</div>
		<div class="latest-status-box__icon" :style="{background:iconBackground,color:iconColor}">
			<slot></slot>
		</div>
	</div>
</template>

<script>
	export default {
		name: "StatusBox",
		props: {
			number: {type: String, default: ''},
			label: {type: String, default: ''},
			borderColor: {type: String, default: '#f1f1f1'},
			iconBackground: {type: String, default: '#f1f1f1'},
			iconColor: {type: String, default: '#323232'},
		}
	}
</script>

<style lang="scss">
	.latest-status-box {
		align-items: center;
		border: 1px solid rgba(#000, 0.2);
		border-radius: 6px;
		display: flex;
		justify-content: flex-start;
		position: relative;

		&__content {
			flex-grow: 1;
			padding: 0.5em;
		}

		&__number {
			font-size: 2em;
			font-weight: bold;
			line-height: 1.2;
		}

		&__label {
			font-size: 1em;
			line-height: 1.2;
			text-transform: uppercase;
		}

		&__icon {
			background: rgba(#000, 0.12);
			border-top-right-radius: 4px;
			border-bottom-right-radius: 4px;
			padding: 0.5em;
			height: 4.6em;
			width: 4.6em;
			justify-content: center;
			align-items: center;
			display: flex;
		}
	}
</style>
