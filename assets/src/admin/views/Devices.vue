<template>
	<div class="repair-services-devices-list">
		<h1 class="wp-heading-inline">Devices</h1>
		<a href="" class="page-title-action" @click.prevent="addNewDevice">Add New</a>
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
	</div>
</template>

<script>
	import ListTable from '../../components/ListTable';

	export default {
		name: "Devices",
		components: {ListTable},
		data() {
			return {
				rows: [],
				columns: [
					{key: 'title', label: 'Title'},
					{key: 'image', label: 'Image'},
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
			addNewDevice() {
				this.$router.push('/device/new');
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
	.repair-services-devices-list {
		.mdl-button--fab {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 100;
		}
	}
</style>
