<template>
	<div :class="wrapperClass">
		<button class="big-button" @click="handleClick" :disabled="disabled">
			<slot>{{text}}</slot>
		</button>
	</div>
</template>

<script>
	export default {
		name: "BigButton",
		props: {
			text: {type: String, default: 'Submit'},
			disabled: {type: Boolean, default: false},
		},
		computed: {
			wrapperClass() {
				return {
					'big-button-wrapper': true,
				}
			}
		},
		methods: {
			handleClick() {
				this.$emit('click');
			}
		}
	}
</script>

<style lang="scss">
	.big-button-wrapper {
		.big-button {
			color: #0161c7;
			background-color: #12ffcd;
			border-width: 0;
			cursor: pointer;
			border-radius: 5px;
			line-height: 1.2;
			text-align: center;
			transition: all .4s ease;
			font-size: 18px;
			padding: 1em;
			width: 100%;

			&:disabled {
				background-color: #e1e8ec;
				color: #9db2bf;
				cursor: not-allowed;
			}
		}
	}
</style>
