<template>
	<div class="mdl-spinner mdl-js-spinner is-upgraded" :class="getClass" v-if="active">
		<div :class="itemClass(index)" v-for="index in [1, 2, 3, 4]">
			<div class="mdl-spinner__circle-clipper mdl-spinner__left">
				<div class="mdl-spinner__circle"></div>
			</div>
			<div class="mdl-spinner__gap-patch">
				<div class="mdl-spinner__circle"></div>
			</div>
			<div class="mdl-spinner__circle-clipper mdl-spinner__right">
				<div class="mdl-spinner__circle"></div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "spinner",
		props: {
			active: {type: Boolean, default: true},
			single: {type: Boolean, default: false},
		},
		computed: {
			getClass() {
				return {
					'is-active': this.active,
					'mdl-spinner--single-color': this.single,
				}
			}
		},
		methods: {
			itemClass(index) {
				return [
					'mdl-spinner__layer',
					'mdl-spinner__layer-' + index
				]
			}
		}
	}
</script>

<style lang="scss">
	// @import "spinner";
</style>
