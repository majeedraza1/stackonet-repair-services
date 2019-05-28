<template>
	<div class="mdl-data-table-container">
		<div class="mdl-table-nav-top">
			<div class="mdl-table-nav-top__left">
				<mdl-bulk-actions :actions="bulkActions" :active="!!checkedItems.length" v-model="bulkLocal"
								  @bulk:click="handleBulkAction"></mdl-bulk-actions>

				<div class="mdl-table-nav-top__filters">
					<slot name="filters"></slot>
				</div>
			</div>
			<div class="mdl-table-nav-top__right">
				<mdl-pagination :current_page="currentPage" :per_page="perPage" :total_items="itemsTotal"
								@pagination="goToPage" size="medium"></mdl-pagination>
			</div>
		</div>
		<table :class="tableClasses">
			<thead>
			<tr>
				<th v-if="showCb" class="check-column">
					<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
					<mdl-checkbox id="cb-select-all-1" v-model="selectAll"></mdl-checkbox>
				</th>
				<th v-for="column in columns" :class="getHeadColumnClass(column.key, column)">
					<template v-if="!isSortable(column)">
						{{ column.label }}
					</template>
					<a href="#" v-else @click.prevent="handleSortBy(column.key)">
						<span>{{ column.label }}</span>
						<span class="sorting-indicator"></span>
					</a>
				</th>
			</tr>
			</thead>

			<tbody>
			<template v-if="rows.length">
				<tr v-for="row in rows" :key="row[index]" :class="{'is-selected':checkedItems.includes(row[index])}">
					<td scope="row" class="check-column" v-if="showCb">
						<mdl-checkbox :value="row[index]" v-model="checkedItems"></mdl-checkbox>
					</td>
					<td v-for="column in columns" :class="getBodyColumnClass(column)" :data-colname="column.label">

						<slot :name="column.key" :row="row">
							{{ row[column.key] }}
						</slot>

						<div v-if="actionColumn === column.key && hasActions" class="row-actions">
							<slot name="row-actions" :row="row">
                  				<span v-for="action in actions" :class="action.key">
                    				<a href="#" @click.prevent="actionClicked(action.key, row)">{{ action.label }}</a>
									<template v-if="!hideActionSeparator(action.key)"> | </template>
                  				</span>
							</slot>
						</div>
						<button type="button" class="toggle-row" v-if="actionColumn === column.key && hasActions">
							<span class="screen-reader-text">Show more details</span>
						</button>
					</td>
				</tr>
			</template>
			<tr v-else>
				<td :colspan="colspan" style="text-align: center">{{ notFound }}</td>
			</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
	import mdlCheckbox from '../checkbox/mdlCheckbox';
	import mdlBulkActions from './mdlBulkActions'
	import mdlPagination from './mdlPagination'

	export default {
		name: "mdlTable",

		components: {mdlCheckbox, mdlBulkActions, mdlPagination},

		props: {
			rows: {type: Array, required: true,},
			columns: {type: Array, required: true,},
			actions: {type: Array, required: false, default: () => []},
			bulkActions: {type: Array, required: false, default: () => []},
			statuses: {type: Array, required: false, default: () => []},
			index: {type: String, default: 'id'},
			showSearch: {type: Boolean, default: true},
			searchKey: {type: String, default: 'search_items'},
			actionColumn: {type: String, default: 'title'},
			showCb: {type: Boolean, default: true},
			loading: {type: Boolean, default: false},
			tableClass: {type: String, default: 'wp-list-table widefat fixed striped'},
			notFound: {type: String, default: 'No items found.'},
			totalItems: {type: Number, default: 0},
			totalPages: {type: Number, default: 1},
			perPage: {type: Number, default: 20},
			currentPage: {type: Number, default: 1},
			sortBy: {type: String, default: null},
			sortOrder: {type: String, default: "asc"}
		},

		data() {
			return {
				bulkLocal: '-1',
				checkedItems: [],
			}
		},

		computed: {

			tableClasses() {
				return {
					'mdl-data-table': true,
					'mdl-js-data-table': true,
				}
			},

			hasActions() {
				return this.actions.length > 0;
			},

			hasBulkActions() {
				return this.bulkActions.length > 0;
			},

			itemsTotal() {
				return this.totalItems || this.rows.length;
			},

			colspan() {
				let columns = Object.keys(this.columns).length;

				if (this.showCb) {
					columns += 1;
				}

				return columns;
			},

			selectAll: {

				get: function () {
					if (!this.rows.length) {
						return false;
					}

					return this.rows ? this.checkedItems.length === this.rows.length : false;
				},

				set: function (value) {
					let selected = [],
						self = this;

					if (value) {
						this.rows.forEach(function (item) {
							if (item[self.index] !== undefined) {
								selected.push(item[self.index]);
							} else {
								selected.push(item.id);
							}
						});
					}

					this.checkedItems = selected;
				}
			}
		},

		methods: {

			getHeadColumnClass(key, value) {
				let nonNumeric = typeof value.numeric === "undefined" || (typeof value.numeric !== "undefined" && value.numeric === false);
				return [
					'manage-column',
					'manage-' + key,
					{'mdl-data-table__cell--non-numeric': nonNumeric},
					{'column-primary': this.actionColumn === key},
					{'sortable': this.isSortable(value)},
					{'sorted': this.isSorted(key)},
					{'mdl-data-table__header--sorted-ascending': this.isSorted(key) && this.sortOrder === 'asc'},
					{'mdl-data-table__header--sorted-descending': this.isSorted(key) && this.sortOrder === 'desc'}
				]
			},

			getBodyColumnClass(value) {
				let nonNumeric = typeof value.numeric === "undefined" || (typeof value.numeric !== "undefined" && value.numeric === false);
				return [
					'manage-column',
					'manage-' + value.key,
					{'mdl-data-table__cell--non-numeric': nonNumeric},
					{'column-primary': this.actionColumn === value.key},
				]
			},

			hideActionSeparator(action) {
				return action === this.actions[this.actions.length - 1].key;
			},

			actionClicked(action, row) {
				this.$emit('action:click', action, row);
			},

			goToPage(page) {
				this.$emit('pagination', page);
			},

			handleBulkAction(action) {
				if (action === '-1') {
					return;
				}

				this.$emit('bulk:apply', action, this.checkedItems);
			},

			isSortable(column) {
				return column.hasOwnProperty('sortable') && column.sortable === true;
			},

			isSorted(column) {
				return column === this.sortBy;
			},

			handleSortBy(column) {
				let order = this.sortOrder === 'asc' ? 'desc' : 'asc';

				this.$emit('sort', column, order);
			},

			changeStatus(status) {
				this.$emit('status:change', status);
			},

			searchInput(query) {
				this.$emit('search', query);
			}
		}
	}
</script>

<style lang="scss">
	@import "data-table";

	.mdl-data-table-container {
		th.check-column, td.check-column {
			width: 70px;
		}

		.mdl-table-nav-top {
			align-items: flex-end;
			display: flex;
			justify-content: space-between;
			margin-bottom: 10px;
			margin-top: 10px;
			width: 100%;

			&__left {
				display: flex;
			}

			&__action,
			&__filters {
				display: flex;

				> * {
					margin-right: 5px;
				}
			}

			select {
				line-height: 1.2;
				padding: 3px 10px;
			}
		}

		.row-actions {
			visibility: hidden;

			.trash,
			.delete {
				color: #a00;

				a {
					color: currentColor;
				}
			}
		}

		tr:hover .row-actions {
			visibility: visible;
		}

		// Temp
		.toggle-row {
			display: none;
		}
	}
</style>
