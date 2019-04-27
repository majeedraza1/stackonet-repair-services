<template>
	<div>
		<h1 class="wp-heading-inline">Survey</h1>
		<div class="clear"></div>

		<wp-list-table
			:loading="loading"
			:rows="items"
			:columns="columns"
			:actions="actions"
			:bulk-actions="bulkActions"
			index="id"
			action-column="full_address"
			:show-search="false"
			search-key="survey"
			:statuses="statuses"
			:total-items="pagination.totalCount"
			:total-pages="pagination.pageCount"
			:per-page="pagination.limit"
			:current-page="pagination.currentPage"
			@action:click="onActionClick"
			@bulk:apply="onBulkAction"
			@status:change="changeStatus"
		>
			<template slot="display_name" slot-scope="data">
				<span>{{data.row.author.display_name}}</span>
			</template>
		</wp-list-table>
	</div>
</template>

<script>
	import axios from 'axios';
	import wpListTable from '../../wp/wpListTable.vue'

	export default {
		name: "SurveyListTable",
		components: {wpListTable},
		data() {
			return {
				loading: false,
				status: '',
				currentPage: 1,
				items: [],
				counts: {},
				pagination: {},
				columns: [
					{key: 'full_address', label: 'Address'},
					{key: 'device_status', label: 'Status'},
					{key: 'latitude', label: 'Latitude'},
					{key: 'longitude', label: 'Longitude'},
					{key: 'display_name', label: 'Created By'},
					{key: 'created_at', label: 'Date'},
				],
				default_statuses: [
					{key: 'all', label: 'All', count: 0, active: true},
					{key: 'affordable', label: 'Affordable', count: 0, active: false},
					{key: 'not-affordable', label: 'Not Affordable', count: 0, active: false},
					{key: 'not-pertain', label: 'Not Pertain', count: 0, active: false},
					{key: 'trash', label: 'Trash', count: 0, active: false},
				]
			}
		},
		mounted() {
			if (!this.items.length) {
				this.getItems();
			}
		},
		computed: {
			statuses() {
				let _status = [], self = this;
				self.default_statuses.forEach(status => {
					status.count = self.counts[status.key];
					_status.push(status);
				});

				return _status;
			},
			actions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				}

				return [
					{key: 'view', label: 'View'},
					{key: 'trash', label: 'Trash'}
				];
			},
			bulkActions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
		},
		methods: {
			getItems() {
				let self = this;
				axios
					.get(stackonetSettings.root + `/survey?status=${self.status}&paged=${self.currentPage}`)
					.then((response) => {
						let data = response.data.data;
						self.items = data.items;
						self.counts = data.counts;
						self.pagination = data.pagination;
					})
					.catch((error) => {
						console.log(error);
					})
			},

			changeStatus(status) {
				this.currentPage = 1;
				this.status = status.key;
				this.default_statuses.forEach(element => {
					element.active = false;
				});
				status.active = true;
				this.getItems();
			},
			onActionClick(action, item) {
				if ('view' === action) {
				}
				if ('trash' === action && window.confirm('Are you sure move this item to trash?')) {
					this.trashAction(item, 'trash');
				}
				if ('restore' === action && window.confirm('Are you sure restore this item again?')) {
					this.trashAction(item, 'restore');
				}
				if ('delete' === action && window.confirm('Are you sure to delete permanently?')) {
					this.trashAction(item, 'delete');
				}
			},
			onBulkAction(action, items) {
				if ('trash' === action) {
					if (confirm('Are you sure to trash all selected items?')) {
						this.batchTrashAction(items, action);
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete all selected items permanently?')) {
						this.batchTrashAction(items, action);
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore all selected items?')) {
						this.batchTrashAction(items, action);
					}
				}
			},
			trashAction(item, action) {
			},
			batchTrashAction(ids, action) {

			}
		}
	}
</script>

<style scoped>

</style>
