<template>
	<div>
		<h1 class="wp-heading-inline">Technicians</h1>
		<div class="clear"></div>

		<wp-list-table
			:loading="loading"
			:rows="items"
			:columns="columns"
			:actions="actions"
			:bulk-actions="bulkActions"
			index="id"
			action-column="first_name"
			:show-search="false"
			search-key="technician"
			:statuses="statuses"
			:total-items="pagination.totalCount"
			:total-pages="pagination.pageCount"
			:per-page="pagination.limit"
			:current-page="pagination.currentPage"
			@action:click="onActionClick"
			@bulk:apply="onBulkAction"
			@status:change="changeStatus"
			@pagination="paginate"
		/>
		<modal :active="has_active_item" title="Technician Info" @close="activeItem = {}">
			<list-item label="ID">{{activeItem.id}}</list-item>
			<list-item label="First Name">{{activeItem.first_name}}</list-item>
			<list-item label="Last Name">{{activeItem.last_name}}</list-item>
			<list-item label="Email">{{activeItem.email}}</list-item>
			<list-item label="Phone">{{activeItem.phone}}</list-item>
			<list-item label="Date">{{activeItem.created_at}}</list-item>
			<list-item label="Resume">
				<a :href="activeItem.resume_url" target="_blank">{{activeItem.resume_title}}</a>
			</list-item>
			<div slot="foot">
				<button @click="activeItem = {}">Close</button>
			</div>
		</modal>
	</div>
</template>

<script>
	import axios from 'axios';
	import modal from 'shapla-modal';
	import wpListTable from '../../wp/wpListTable.vue';
	import ListItem from '../../components/ListItem.vue';
	import imageContainer from '../../shapla/image/image';

	export default {
		name: "BecomeTechListTable",
		components: {wpListTable, modal, ListItem, imageContainer},
		data() {
			return {
				loading: false,
				status: '',
				currentPage: 1,
				items: [],
				counts: {},
				pagination: {},
				columns: [
					{key: 'first_name', label: 'First Name'},
					{key: 'last_name', label: 'Last Name'},
					{key: 'email', label: 'Email'},
					{key: 'phone', label: 'Phone'},
					{key: 'created_at', label: 'Date'},
				],
				default_statuses: [
					{key: 'all', label: 'All', count: 0, active: true},
					{key: 'trash', label: 'Trash', count: 0, active: false},
				],
				activeItem: {},
			}
		},
		mounted() {
			if (!this.items.length) {
				this.getItems();
			}
		},
		computed: {
			has_active_item() {
				return !!Object.keys(this.activeItem).length;
			},
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
					.get(stackonetSettings.root + `/technician?status=${self.status}&paged=${self.currentPage}`)
					.then((response) => {
						let data = response.data.data;
						self.items = data.items;
						self.counts = data.counts;
						self.pagination = data.pagination;
					})
					.catch((error) => {
						console.log(error);
					});
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
			paginate(page) {
				this.currentPage = page;
				this.getItems();
			},
			onActionClick(action, item) {
				if ('view' === action) {
					this.activeItem = item;
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
				let self = this;
				self.loading = true;
				axios
					.post(stackonetSettings.root + '/technician/delete', {
						id: item.id,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.loading = false;
					})
					.catch((error) => {
						console.log(error);
						self.loading = false;
					});
			},
			batchTrashAction(ids, action) {
				let self = this;
				self.loading = true;
				axios
					.post(stackonetSettings.root + '/technician/batch_delete', {
						ids: ids,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.loading = false;
					})
					.catch((error) => {
						console.log(error);
						self.loading = false;
					});
			}
		}
	}
</script>

<style scoped>

</style>
