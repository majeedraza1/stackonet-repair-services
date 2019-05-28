<template>
	<div class="stackont-support-ticket-container" style="margin: 100px auto;">
		<div class="display-flex justify-space-between">
			<div class="flex-item">
				<mdl-button type="raised" color="primary" @click="newTicketModel = true">+ New Ticket</mdl-button>
			</div>
			<div class="flex-item">
				<mdl-button type="raised" color="default" @click="openTrash">Trash ({{count_trash}})</mdl-button>
				<mdl-button type="raised" color="default">Export Excel</mdl-button>
			</div>
		</div>

		<mdl-table
			action-column="ticket_subject"
			:columns="columns"
			:rows="items"
			:total-items="pagination.totalCount"
			:total-pages="pagination.pageCount"
			:per-page="pagination.limit"
			:current-page="pagination.currentPage"
			:actions="actions"
			:bulk-actions="bulkActions"
			@action:click="onActionClick"
			@bulk:apply="onBulkAction"
			@pagination="paginate"
		>
			<template slot="created_by" slot-scope="data" class="button--status">
				<span v-html="getAssignedAgents(data.row.assigned_agents)"></span>
			</template>
			<span slot="ticket_status" slot-scope="data" class="button--status" :class="data.row.status.slug">
				{{data.row.status.name}}
			</span>
			<span slot="ticket_category" slot-scope="data" class="button--category" :class="data.row.category.slug">
				{{data.row.category.name}}
			</span>
			<span slot="ticket_priority" slot-scope="data" class="button--priority" :class="data.row.priority.slug">
				{{data.row.priority.name}}
			</span>
			<template slot="filters">
				<label for="filter-address" class="screen-reader-text">Filter by status</label>
				<select id="filter-address" v-model="status" @change="changeStatus">
					<option :value="_status.key" v-for="_status in statuses">
						{{_status.label}} ({{_status.count}})
					</option>
				</select>
				<label for="filter-category" class="screen-reader-text">Filter by Category</label>
				<select id="filter-category" v-model="category" @change="changeStatus">
					<option :value="_status.key" v-for="_status in default_categories">
						{{_status.label}}
					</option>
				</select>
				<label for="filter-priority" class="screen-reader-text">Filter by Priority</label>
				<select id="filter-priority" v-model="priority" @change="changeStatus">
					<option :value="_status.key" v-for="_status in default_priorities">
						{{_status.label}}
					</option>
				</select>
				<mdl-button type="raised" color="default" @click="clearFilter">Clear Filter</mdl-button>
			</template>
		</mdl-table>
		<wp-pagination :current_page="pagination.currentPage" :per_page="pagination.limit"
					   :total_items="pagination.totalCount" size="medium" @pagination="paginate"></wp-pagination>

		<modal :active="newTicketModel" title="Add new ticket" @close="newTicketModel = false">

		</modal>
	</div>
</template>

<script>
	import axios from 'axios';
	import mdlTable from '../../material-design-lite/data-table/mdlTable'
	import mdlButton from '../../material-design-lite/button/mdlButton'
	import wpStatusList from '../../wp/wpStatusList'
	import wpPagination from '../../wp/wpPagination'
	import wpBulkActions from '../../wp/wpBulkActions'
	import modal from '../../shapla/modal/modal'

	export default {
		name: "SupportTicketList",
		components: {mdlTable, mdlButton, wpStatusList, wpPagination, wpBulkActions, modal},
		data() {
			return {
				loading: false,
				newTicketModel: false,
				default_statuses: [],
				default_categories: [],
				default_priorities: [],
				columns: [
					{key: 'id', label: 'ID', numeric: true},
					{key: 'ticket_subject', label: 'Subject', numeric: false},
					{key: 'ticket_status', label: 'Status', numeric: false},
					{key: 'customer_name', label: 'Name', numeric: false},
					{key: 'customer_email', label: 'Email Address', numeric: false},
					{key: 'created_by', label: 'Assigned Agent', numeric: false},
					{key: 'ticket_category', label: 'Category', numeric: false},
					{key: 'ticket_priority', label: 'Priority', numeric: false},
					{key: 'updated', label: 'Updated', numeric: false},
				],
				items: [],
				counts: [],
				pagination: {},
				currentPage: 1,
				count_trash: 0,
				status: 'all',
				category: 'all',
				priority: 'all',
			}
		},
		mounted() {
			if (!this.items.length) {
				this.getItems();
			}
			this.default_statuses = SupportTickets.statuses;
			this.default_categories = SupportTickets.categories;
			this.default_priorities = SupportTickets.priorities;
			this.count_trash = SupportTickets.count_trash;
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
			getAssignedAgents(data) {
				if (data.length < 1) return 'None';

				let html = '';
				for (let i = 0; i < data.length; i++) {
					html += (i !== 0) ? ', ' : '';
					html += data[i].display_name;
				}
				return html;
			},
			clearFilter() {
				this.status = 'all';
				this.category = 'all';
				this.priority = 'all';
				this.getItems();
			},
			changeStatus() {
				this.currentPage = 1;
				this.getItems();
			},
			paginate(page) {
				this.currentPage = page;
				this.getItems();
			},
			openTrash() {
				this.status = 'trash';
				this.getItems();
				// this.status = 'all';
			},
			getItems() {
				let self = this;
				let parms = `ticket_status=${self.status}&ticket_category=${self.category}&ticket_priority=${self.priority}&paged=${self.currentPage}`;
				axios
					.get(PhoneRepairs.rest_root + `/support-ticket?${parms}`)
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

			onActionClick(action, item) {
				if ('view' === action) {
					this.$router.push({name: 'SingleSupportTicket', params: {id: item.id}});
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
			trashAction(item, action) {
				let self = this;
				self.loading = true;
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/delete', {
						id: item.id,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.loading = false;
					})
					.catch((error) => {
						self.loading = false;
					});
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
			batchTrashAction(ids, action) {
				let self = this;
				self.loading = true;
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/batch_delete', {
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

<style lang="scss">
	.stackont-support-ticket-container {
		td.manage-column.manage-ticket_subject {
			max-width: 250px;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		td.manage-updated {
			white-space: nowrap;
		}

		thead {
			tr {
				background: rgba(32, 33, 36, 0.059);
			}
		}

		.display-flex {
			display: flex;
		}

		.justify-space-between {
			justify-content: space-between;
		}
	}
</style>
