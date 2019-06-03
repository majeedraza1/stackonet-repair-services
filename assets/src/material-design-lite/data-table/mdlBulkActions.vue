<template>
	<div class="mdl-table-nav-top__action" v-if="hasBulkActions">
		<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
		<select name="action" id="bulk-action-selector-bottom" :value="value" @input="handleChangeEvent($event)">
			<option value="-1">Bulk Actions</option>
			<option v-for="action in actions" :value="action.key">{{ action.label }}</option>
		</select>

		<mdl-button type="raised" color="default" @click="handleBulkAction" :disabled="!isApplyActive">Apply
		</mdl-button>
	</div>
</template>

<script>
	import mdlButton from '../button/mdlButton'

	export default {
		name: "mdlBulkActions",
		components: {mdlButton},
		props: {
			value: {type: String, default: '-1'},
			actions: {type: Array, required: false, default: () => []},
			active: {type: Boolean, default: false}
		},
		data() {
			return {
				localModel: '-1',
			}
		},
		mounted() {
			this.localModel = this.value;
		},
		computed: {
			isApplyActive() {
				if (this.value === '-1') return false;

				return this.active;
			},
			hasBulkActions() {
				return this.actions.length > 0;
			}
		},
		methods: {
			handleBulkAction() {
				if (this.localModel === '-1') {
					return;
				}

				this.$emit('bulk:click', this.localModel);
			},
			handleChangeEvent(event) {
				this.localModel = event.target.value;
				this.$emit('input', this.localModel);
			}
		}
	}
</script>

<style scoped>

</style>
