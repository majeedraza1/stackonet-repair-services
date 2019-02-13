<template>
	<div class="repair-services-issues-list">
		<h1 class="wp-heading-inline">Issues</h1>
		<a href="" class="page-title-action" @click.prevent="openModal">Add New</a>
		<div class="clear"></div>
		<list-table
				:columns="columns"
				:rows="issues"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="title"
				@action:click="onActionClick"
				@bulk:click="onBulkAction"
		></list-table>
		<mdl-modal :active="modalActive" @close="closeModal" title="Add New Area">
			<p class="">
				<label for="title">Issue Title</label><br>
				<input type="text" id="title" class="regular-text" v-model="title">
			</p>
			<p class="">
				<label for="price">Default Price (optional)</label><br>
				<input type="number" id="price" v-model="price" class="regular-text" min="0" step="0.01"/>
			</p>
			<div slot="foot">
				<button class="button" @click="addNewIssue">Save</button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import ListTable from '../../components/ListTable';
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';

	export default {
		name: "Issues",
		components: {ListTable, mdlModal},
		data() {
			return {
				modalActive: false,
				id: '',
				title: '',
				price: '',
				rows: [],
				columns: [
					{key: 'title', label: 'Issue'},
					{key: 'price', label: 'Default Price'},
				],
				actions: [{key: 'edit', label: 'Edit'}, {key: 'delete', label: 'Delete'}],
				bulkActions: [],
				counts: {},
			}
		},
		computed: {
			loading() {
				return this.$store.state.loading;
			},
			issues() {
				return this.$store.state.issues;
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.issues.length) {
				this.fetchIssues();
			}
		},
		methods: {
			openModal() {
				this.modalActive = true;
			},
			closeModal() {
				this.modalActive = false;
			},
			fetchIssues() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_device_issues',
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_ISSUES', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			addNewIssue() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'POST',
					url: ajaxurl,
					data: {
						action: 'create_device_issue',
						id: self.id,
						title: self.title,
						price: self.price,
					},
					success: function (response) {
						if (response.data) {
							let issues = self.issues;
							if (self.id) {
								let result = issues.filter(obj => {
									return obj.id === self.id
								});
								issues.splice(issues.indexOf(result), 1, response.data);
							} else {
								issues.push(response.data);
								self.$store.commit('SET_ISSUES', issues);
							}
						}
						self.title = '';
						self.price = '';
						self.$store.commit('SET_LOADING_STATUS', false);
						self.closeModal();
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},

			onActionClick(action, row) {
				if ('edit' === action) {
					this.id = row.id;
					this.title = row.title;
					this.price = row.price;
					this.openModal();
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
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'POST',
					url: ajaxurl,
					data: {
						action: 'delete_device_issue',
						id: item.id,
						task: 'delete',
					},
					success: function (response) {
						if (response.data) {
							let issues = self.issues;
							issues.splice(issues.indexOf(item), 1);
							self.$store.commit('SET_ISSUES', issues);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
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

</style>
