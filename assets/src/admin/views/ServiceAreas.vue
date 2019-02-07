<template>
	<div class="repair-services-areas-list">
		<h1 class="wp-heading-inline">Service Areas</h1>
		<a href="" class="page-title-action" @click.prevent="openModal">Add New</a>
		<div class="clear"></div>
		<list-table
				:columns="columns"
				:rows="rows"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="title"
				@action:click="onActionClick"
				@bulk:click="onBulkAction"
		></list-table>
		<mdl-modal :active="modalActive" @close="closeModal" title="Add New Area">
			<p class="">
				<label for="zipCode">Zip Code</label><br>
				<input type="text" id="zipCode" class="regular-text" v-model="zipCode">
			</p>
			<p class="">
				<label for="address">Address (optional)</label><br>
				<textarea id="address" v-model="address" class="regular-text"></textarea>
			</p>
			<div slot="foot">
				<button class="button">Save</button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import ListTable from '../../components/ListTable';
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';

	export default {
		name: "ServiceAreas",
		components: {ListTable, mdlModal},
		data() {
			return {
				modalActive: false,
				zipCode: '',
				address: '',
				rows: [],
				columns: [
					{key: 'zipCode', label: 'Zip Code'},
					{key: 'address', label: 'Address'},
				],
				actions: [],
				bulkActions: [],
				counts: {},
			}
		},
		computed: {
			loading() {
				return this.$store.state.loading;
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false)
		},
		methods: {
			openModal() {
				this.modalActive = true;
			},
			closeModal() {
				this.modalActive = false;
			},
			onActionClick(action, row) {
				if ('edit' === action) {
					window.location.href = "#/" + row.id;
				} else if ('trash' === action) {
					if (confirm('Are you sure to move this item to trash?')) {
						this.trashItem(row);
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore this item?')) {
						this.restoreItem(row);
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete this item permanently?')) {
						this.deleteItem(row);
					}
				}
			},
			onBulkAction(action, items) {
				if ('trash' === action) {
					if (confirm('Are you sure to trash all selected items?')) {
						this.trashItems(items);
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete all selected items permanently?')) {
						this.deleteItems(items);
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore all selected items?')) {
						this.restoreItems(items);
					}
				}
			},
			trashItem(item) {
			},
			restoreItem(item) {
			},
			deleteItem(item) {
			},
			trashItems(item) {
			},
			deleteItems(item) {
			},
			restoreItems(item) {
			},
		}
	}
</script>

<style lang="scss">
	.repair-services-areas-list {
		.mdl-button--fab {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 100;
		}
	}
</style>
