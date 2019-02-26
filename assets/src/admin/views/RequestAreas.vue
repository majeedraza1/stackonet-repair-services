<template>
	<div class="repair-services-issues-list">
		<h1 class="wp-heading-inline">Request Area</h1>
		<div class="clear"></div>
		<wp-list-table
				:loading="loading"
				:columns="columns"
				:rows="requested_areas"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="zip_code"
				:current-page="currentPage"
				:per-page="perPage"
				:total-items="totalItems"
				@action:click="onActionClick"
				@bulk:apply="onBulkAction"
				:statuses="statuses"
				:show-search="false"
		></wp-list-table>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import wpListTable from '../../wp/wpListTable';

	export default {
		name: "RequestAreas",
		components: {wpListTable},
		data() {
			return {
				default_statuses: [
					{key: 'all', label: 'All', count: 0, active: true},
					{key: 'read', label: 'Read', count: 0, active: false},
					{key: 'unread', label: 'Unread', count: 0, active: false},
					{key: 'trash', label: 'Trash', count: 0, active: false},
				],
				activeStatus: 'all',
				columns: [
					{key: 'zip_code', label: 'Zip Code'},
					{key: 'email', label: 'Email'},
					{key: 'user_ip', label: 'User IP'},
					{key: 'device_title', label: 'Device'},
					{key: 'device_model', label: 'Device Model'},
					{key: 'device_color', label: 'Device Color'},
					{key: 'created_at', label: 'Date & Time'},
				],
				currentPage: 1,
				perPage: 20,
				counts: {},
			}
		},
		computed: {
			...mapState(['loading', 'requested_areas', 'requested_areas_counts']),
			statuses() {
				let _status = [], self = this;
				this.default_statuses.forEach(status => {
					status.count = self.requested_areas_counts[status.key];
					_status.push(status);
				});

				return _status;
			},
			activeStatus_() {
				let active = {};
				this.statuses.forEach(status => {
					if (status.active === true) {
						active = status;
					}
				});

				return active;
			},
			totalItems() {
				return this.requested_areas_counts[this.activeStatus_.key];
			},
			actions() {
				if (this.activeStatus === 'trash') {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Trash'}]
				}
			},
			bulkActions() {
				if (this.activeStatus === 'trash') {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.requested_areas.length) {
				this.get_items();
			}
		},
		methods: {
			get_items() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_request_areas',
						per_page: self.perPage,
						page: self.currentPage,
						status: self.activeStatus,
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_REQUESTED_AREAS', response.data.items);
							self.$store.commit('SET_REQUESTED_AREAS_COUNTS', response.data.counts);
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
