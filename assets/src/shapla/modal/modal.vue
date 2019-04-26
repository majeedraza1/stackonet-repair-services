<template>
	<div class="shapla-modal is-active" v-show="active">
		<div class="shapla-modal-background"></div>

		<template v-if="!is_card">
			<div class="shapla-modal-content">
				<slot></slot>
			</div>

			<delete-icon fixed large @click="close"></delete-icon>
		</template>

		<div class="shapla-modal-card" v-if="is_card">
			<div class="shapla-modal-card-head">
				<p class="shapla-modal-card-title">{{title}}</p>
				<delete-icon @click="close"></delete-icon>
			</div>
			<div class="shapla-modal-card-body">
				<slot></slot>
			</div>
			<div class="shapla-modal-card-foot is-pulled-right">
				<slot name="foot">
					<button @click.prevent="close">Cancel</button>
				</slot>
			</div>
		</div>
	</div>
</template>

<script>
	import deleteIcon from '../delete/deleteIcon';

	export default {
		name: "modal",

		components: {deleteIcon},

		props: {
			active: {type: Boolean, required: true},
			title: {type: String, default: 'Untitled'},
			type: {type: String, default: 'card'},
		},

		data() {
			return {}
		},

		computed: {
			is_card() {
				return this.type === 'card';
			}
		},

		methods: {
			close() {
				this.$emit('close');
			}
		}
	}
</script>

<style lang="scss">
	@import "modal";
</style>
