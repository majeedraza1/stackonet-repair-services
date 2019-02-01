<template>
	<div ref="container" @mousedown="addRipple" class="ripple-outer">
		<transition-group class="ripples" name="grow" tag="div">
			<div class="ripple" v-for="ripple in ripples" :key="ripple.id" :style="getStyle(ripple)"></div>
		</transition-group>
		<slot></slot>
	</div>
</template>

<script>
	export default {
		name: "RippleEffect",
		props: {
			color: {
				type: String,
				default: "rgba(255, 255, 255, 0.3)"
			}
		},
		data() {
			return {
				ripples: []
			};
		},
		mounted() {
			const width = this.$refs.container.offsetWidth;
			const height = this.$refs.container.offsetHeight;
			this.rippleWidth = width > height ? width : height;
			this.halfRippleWidth = this.rippleWidth / 2;

			window.addEventListener("mouseup", this.purgeRipples);
		},
		beforeDestroy() {
			window.removeEventListener("mouseup", this.purgeRipples)
		},
		methods: {
			addRipple(e) {
				const {left, top} = this.$refs.container.getBoundingClientRect();
				const rippleId = Date.now();
				this.ripples.push({
					width: `${this.rippleWidth}px`,
					height: `${this.rippleWidth}px`,
					left: `${e.clientX - left - this.halfRippleWidth}px`,
					top: `${e.clientY - top - this.halfRippleWidth}px`,
					id: rippleId
				});
			},
			purgeRipples() {
				this.ripples = [];
			},
			getStyle(ripple) {
				return {
					top: ripple.top,
					left: ripple.left,
					width: ripple.width,
					height: ripple.height,
					background: this.color
				};
			}
		}
	}
</script>

<style lang="scss">
	.ripple-outer {
		position: relative;
		z-index: 1;
		overflow: hidden;
		cursor: pointer;
	}

	.ripples {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: -1;
		pointer-events: none;
	}

	.ripple {
		width: 100%;
		height: 100%;
		position: absolute;
		border-radius: 50%;
		opacity: 0;
		pointer-events: none;
	}

	.grow-enter-active,
	.grow-enter-to-active {
		transition: all 1500ms ease-out;
	}

	.grow-leave-active,
	.grow-leave-to-active {
		transition: all 700ms ease-out;
	}

	.grow-enter {
		transform: scale(0);
		opacity: 1;
	}

	.grow-enter-to {
		transform: scale(4);
		opacity: 1;
	}

	.grow-leave {
		transform: scale(4);
		opacity: 1;
	}

	.grow-leave-to {
		transform: scale(4);
		opacity: 0;
	}
</style>
