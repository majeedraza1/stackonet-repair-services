<template>
	<div class="stackont-support-ticket-container">
		<div class="display-flex justify-space-between" style="flex-wrap: wrap">
			<div class="flex-item display-flex align-items-start" style="flex-wrap: wrap">
				<mdl-button type="raised" color="primary" @click="openNewTicket">
					<icon><i class="fa fa-plus" aria-hidden="true"></i></icon>
					New Ticket
				</mdl-button>
				<search :dropdown-items="dropdownCategories" @search="categorySearch"></search>
			</div>
			<div class="flex-item display-flex">
				<mdl-button type="raised" color="default" @click="exportExcel">Export Excel</mdl-button>
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
			:mobile-width="1499"
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
			<template slot="row-actions" slot-scope="data">
				<span v-for="action in actions" :class="action.key">
					<template v-if="action.key === 'view'">
						<router-link
							:to="{name: 'SingleSupportTicket', params: {id: data.row.id}}">{{ action.label }}</router-link>
					</template>
					<a v-else href="#" @click.prevent="onActionClick(action.key, data.row)">{{ action.label }}</a>
					<template v-if="!hideActionSeparator(action.key)"> | </template>
				</span>
				<span class="call" v-if="data.row.customer_phone">
					<template> | </template>
					<a :href="`tel:${data.row.customer_phone}`" @click="markAsCalled(data.row)">
						<template v-if="data.row.called_to_customer === 'yes'">Called</template>
						<template v-else>Call</template>
					</a>
				</span>
				<span class="note-status-circle-container" v-if="data.row.last_note_diff > 0">
					<template> | </template>
					<span class="note-status-circle"
						  :class="getCircleColor(data.row.last_note_diff)"></span>
				</span>
			</template>
			<template slot="filters">
				<div style="min-width: 150px;">
					<v-select :options="statuses" v-model="vStatus" @input="_changeStatus" :clearable="false"
							  placeholder="All Status">
						<template slot="option" slot-scope="option">
							{{option.label}} ({{option.count}})
						</template>
					</v-select>
				</div>
				<div style="min-width: 150px;">
					<v-select :options="default_categories" v-model="vCategory" @input="_changeCategory"
							  :clearable="false"
							  placeholder="All Categories">
						<template slot="option" slot-scope="option">
							{{option.label}} ({{option.count}})
						</template>
					</v-select>
				</div>
				<div style="min-width: 120px;">
					<v-select :options="default_priorities" v-model="vPriority" @input="_changePriority"
							  :clearable="false"
							  placeholder="All Priorities"></v-select>
				</div>
				<div style="min-width: 120px;">
					<v-select :options="_cities" v-model="vCity" @input="_changeCity" :clearable="false"
							  placeholder="All Cities"></v-select>
				</div>
				<div style="min-width: 120px;">
					<v-select :options="support_agents" v-model="vAgent" @input="_changeAgent" :clearable="false"
							  label="display_name"
							  placeholder="All Agents"></v-select>
				</div>
				<mdl-button type="raised" color="default" @click="clearFilter">Clear Filter</mdl-button>
			</template>
		</mdl-table>
		<wp-pagination :current_page="pagination.currentPage" :per_page="pagination.limit"
					   :total_items="pagination.totalCount" size="medium" @pagination="paginate"></wp-pagination>
		<modal :active="activeNoteModal" title="Note" @close="closeNoteModal">
			<textarea cols="30" rows="4" v-model="note" style="width: 100%;"></textarea>
			<div slot="foot">
				<mdl-button @click="saveNote">Save Note</mdl-button>
			</div>
		</modal>
		<modal :active="activeQuickViewModal" @close="activeQuickViewModal = false" title="Quick View">
			<template v-if="activeQuickViewItem.threads">
				<ticket-threads :threads="activeQuickViewItem.threads"></ticket-threads>
			</template>
		</modal>
	</div>
</template>

<script>
	import {mapGetters} from 'vuex';
	import axios from 'axios';
	import modal from 'shapla-modal'
	import vSelect from 'vue-select'
	import mdlTable from '../../material-design-lite/data-table/mdlTable'
	import mdlButton from '../../material-design-lite/button/mdlButton'
	import wpStatusList from '../../wp/wpStatusList'
	import wpPagination from '../../wp/wpPagination'
	import wpBulkActions from '../../wp/wpBulkActions'
	import Icon from "../../shapla/icon/icon";
	import Search from "../../shapla/search/Search";
	import TicketThreads from "./TicketThreads";

	export default {
		name: "SupportTicketList",
		components: {
			vSelect,
			TicketThreads,
			Icon,
			mdlTable,
			mdlButton,
			wpStatusList,
			wpPagination,
			wpBulkActions,
			modal,
			Search
		},
		data() {
			return {
				loading: false,
				default_statuses: [],
				default_categories: [],
				default_priorities: [],
				search_categories: [],
				cities: [],
				columns: [
					{key: 'id', label: 'ID', numeric: true},
					{key: 'ticket_subject', label: 'Subject', numeric: false},
					{key: 'ticket_status', label: 'Status', numeric: false},
					{key: 'customer_name', label: 'Name', numeric: false},
					{key: 'customer_email', label: 'Email Address', numeric: false},
					{key: 'customer_phone', label: 'Phone', numeric: false},
					{key: 'created_by', label: 'Assigned Agent', numeric: false},
					{key: 'ticket_category', label: 'Category', numeric: false},
					{key: 'ticket_priority', label: 'Priority', numeric: false},
					{key: 'updated_human_time', label: 'Updated', numeric: false},
				],
				items: [],
				counts: [],
				pagination: {},
				currentPage: 1,
				count_trash: 0,
				status: 'all',
				category: 'all',
				priority: 'all',
				city: 'all',
				agent: 'all',
				vStatus: {},
				vCategory: {},
				vPriority: {},
				vCity: {},
				vAgent: {},
				query: '',
				activeItem: {},
				activeNoteModal: false,
				activeQuickViewModal: false,
				activeQuickViewItem: {},
				note: '',
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_TITLE', 'Support Tickets');
			if (!this.items.length) {
				this.getItems();
			}
			this.default_statuses = SupportTickets.statuses;
			this.default_categories = SupportTickets.categories;
			this.default_priorities = SupportTickets.priorities;
			this.count_trash = SupportTickets.count_trash;
			this.cities = SupportTickets.cities;
			this.search_categories = SupportTickets.search_categories;
		},
		computed: {
			...mapGetters(['support_agents']),
			dropdownCategories() {
				let _categories = [{label: 'All', value: 'all'}], self = this;
				self.default_categories.forEach(function (element) {
					if (-1 !== self.search_categories.indexOf(element.key)) {
						_categories.push({label: element.label, value: element.key,})
					}
				});

				return _categories;
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
					{key: 'quick_view', label: 'Quick View'},
					{key: 'note', label: 'Note'},
					// {key: 'trash', label: 'Trash'}
				];
			},
			bulkActions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
			_cities() {
				let cities = Object.values(this.cities);

				return cities.map(e => {
					return {label: e, value: e}
				});
			}
		},
		methods: {
			getQuickViewItem(item_id) {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.get(PhoneRepairs.rest_root + '/support-ticket/' + item_id)
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.activeQuickViewItem = response.data.data;
					})
					.catch((error) => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			getCircleColor(last_note_diff) {
				return {
					'is-green': last_note_diff <= 1440,
					'is-yellow': last_note_diff > 1440 && 4320 >= last_note_diff,
					'is-red': last_note_diff > 4320,
				}
			},
			openNewTicket() {
				this.$router.push({name: 'NewSupportTicket'});
			},
			markAsCalled(data) {
				console.log(data);
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.put(PhoneRepairs.rest_root + `/support-ticket/${data.id}/call`)
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
					})
					.catch((error) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						console.log(error);
					});
			},
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
				this.city = 'all';
				this.vStatus = this.vCategory = this.vPriority = this.vCity = {};
				this.getItems();
			},
			_changeStatus(value) {
				this.status = value.key;
				this.changeStatus();
			},
			_changeCategory(value) {
				this.category = value.key;
				this.changeStatus();
			},
			_changePriority(value) {
				this.priority = value.key;
				this.changeStatus();
			},
			_changeCity(value) {
				this.city = value.value;
				this.changeStatus();
			},
			_changeAgent(value) {
				this.agent = value.id;
				this.changeStatus();
			},
			changeStatus() {
				this.currentPage = 1;
				this.getItems();
			},
			paginate(page) {
				this.currentPage = page;
				this.getItems();
			},
			categorySearch(data) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				let parms = `ticket_status=${self.status}&ticket_category=${data.cat}&ticket_priority=${self.priority}&paged=${self.currentPage}&city=${self.city}&agent=${self.agent}&search=${data.query}`;
				axios
					.get(PhoneRepairs.rest_root + `/support-ticket?${parms}`)
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						let data = response.data.data;
						self.items = data.items;
						self.counts = data.counts;
						self.pagination = data.pagination;
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						console.log(error);
					});
			},
			search() {
				this.getItems();
			},
			exportExcel() {
				let url = `${PhoneRepairs.ajaxurl}?action=download_support_ticket&ticket_status=${this.status}&ticket_category=${this.category}&ticket_priority=${this.priority}`;
				window.location.href = url;
			},
			getItems() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				let parms = `ticket_status=${self.status}&ticket_category=${self.category}&ticket_priority=${self.priority}&paged=${self.currentPage}&city=${self.city}&agent=${self.agent}&search=${self.query}`;
				axios
					.get(PhoneRepairs.rest_root + `/support-ticket?${parms}`)
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						let data = response.data.data;
						self.items = data.items;
						self.counts = data.counts;
						self.pagination = data.pagination;
						// self.$root.$emit('show-notification', {
						// 	type: 'info',
						// 	message: 'Data has been updated.',
						// });
					})
					.catch((error) => {
						console.log(error);
					});
			},

			hideActionSeparator(action) {
				return action === this.actions[this.actions.length - 1].key;
			},

			closeNoteModal() {
				this.activeNoteModal = false;
				this.note = '';
			},

			saveNote() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/' + this.activeItem.id + '/thread/', {
						thread_type: 'note',
						thread_content: this.note,
						ticket_attachments: [],
					})
					.then((response) => {
						this.note = '';
						this.activeNoteModal = false;
						this.$store.commit('SET_LOADING_STATUS', false);
						this.$root.$emit('show-notification', {
							message: 'Note has been added successfully.',
							type: 'success',
						});
					})
					.catch((error) => {
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},

			onActionClick(action, item) {
				if ('view' === action) {
					this.$router.push({name: 'SingleSupportTicket', params: {id: item.id}});
				}
				if ('quick_view' === action) {
					this.activeQuickViewModal = true;
					this.activeQuickViewItem = item;
					this.getQuickViewItem(item.id);
				}
				if ('note' === action) {
					this.activeNoteModal = true;
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
			trashAction(item, action) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/delete', {
						id: item.id,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.$store.commit('SET_LOADING_STATUS', false);
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
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
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/batch_delete', {
						ids: ids,
						action: action
					})
					.then((response) => {
						self.getItems();
						self.$store.commit('SET_LOADING_STATUS', false);
					})
					.catch((error) => {
						console.log(error);
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			}
		}
	}
</script>

<style lang="scss">
	.stackont-support-ticket-container {

		.row-actions {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			visibility: visible;

			> span {
				&:not(:last-child) a {
					margin-right: 10px;
				}

				&:not(:first-child) a {
					margin-left: 10px;
				}
			}
		}

		.note-status-circle {
			border: 4px solid #ddd;
			border-radius: 50%;
			display: inline-flex;
			height: 16px;
			width: 16px;
			margin-left: 10px;
			margin-top: 2px;

			&.is-green {
				border: 4px solid #43A047;
			}

			&.is-yellow {
				border: 4px solid #f58730;
			}

			&.is-red {
				border: 4px solid #b00020;
			}

			&-container {
				display: flex;
			}
		}

		.mdl-data-table--mobile {
			.mdl-data-table tr td.manage-column.manage-id {
				display: none !important;
			}
		}

		.mdl-data-table-container:not(.mdl-data-table--mobile) {
			td.manage-column.manage-ticket_subject {
				max-width: 250px;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
		}

		select#filter-address {
			max-width: 160px;
		}

		select#filter-category {
			width: 150px;
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

		.align-items-start {
			align-items: flex-start;

			button {
				margin-right: 5px;
			}
		}
	}
</style>
