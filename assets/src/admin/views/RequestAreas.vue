<template>
	<div class="repair-services-issues-list">
		<h1 class="wp-heading-inline">Issues</h1>
		<a href="" class="page-title-action" @click.prevent="openModal">Add New</a>
		<div class="clear"></div>
		<list-table
				:columns="columns"
				:rows="requested_areas"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="zip_code"
				@action:click="onActionClick"
				@bulk:click="onBulkAction"
		></list-table>
	</div>
</template>

<script>
	import ListTable from '../../components/ListTable';

	export default {
		name: "RequestAreas",
		components: {ListTable},
		data() {
			return {
				columns: [
					{key: 'zip_code', label: 'Zip Code'},
					{key: 'email', label: 'Email'},
					{key: 'device_title', label: 'Device'},
					{key: 'device_model', label: 'Device Model'},
					{key: 'device_color', label: 'Device Color'},
				],
				counts: {},
				actions: [],
				bulkActions: [],
			}
		},
		computed: {
			loading() {
				return this.$store.state.loading;
			},
			requested_areas() {
				return this.$store.state.requested_areas;
			},
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.requested_areas.length) {
				this.fetchAreas();
			}
		},
		methods: {
			fetchAreas() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_request_areas',
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_REQUESTED_AREAS', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			onActionClick(action, row) {
				if ('trash' === action) {
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
				let $ = window.jQuery, self = this;
				// self.$store.commit('SET_LOADING_STATUS', true);
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

<style scoped>

</style>
