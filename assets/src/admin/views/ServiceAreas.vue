<template>
	<div class="repair-services-areas-list">
		<h1 class="wp-heading-inline">Service Areas</h1>
		<a href="" class="page-title-action" @click.prevent="openModal">Add New</a>
		<div class="clear"></div>
		<wp-list-table
				:columns="columns"
				:rows="services_areas"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="zip_code"
				@action:click="onActionClick"
				@bulk:click="onBulkAction"
				:show-cb="false"
				:show-search="false"
				:total-items="totalItems"
				:per-page="totalItems"
		></wp-list-table>
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
				<button class="button" @click="addNewServiceArea">Save</button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import wpListTable from '../../wp/wpListTable';
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';
	import {mapState} from 'vuex';

	export default {
		name: "ServiceAreas",
		components: {wpListTable, mdlModal},
		data() {
			return {
				modalActive: false,
				id: '',
				zipCode: '',
				address: '',
				rows: [],
				columns: [
					{key: 'zip_code', label: 'Zip Code'},
					{key: 'address', label: 'Address'},
				],
				actions: [{key: 'edit', label: 'Edit'}, {key: 'delete', label: 'Delete'}],
				bulkActions: [],
				counts: {},
			}
		},
		computed: {
			...mapState(['loading', 'services_areas']),
			totalItems() {
				return this.services_areas.length;
			},
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.services_areas.length) {
				this.fetchServicesAreas();
			}
		},
		methods: {
			openModal() {
				this.modalActive = true;
			},
			closeModal() {
				this.modalActive = false;
				this.resetData();
			},
			resetData() {
				this.id = '';
				this.zipCode = '';
				this.address = '';
			},
			fetchServicesAreas() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_services_areas',
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_SERVICES_AREAS', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			addNewServiceArea() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'POST',
					url: ajaxurl,
					data: {
						action: 'create_service_area',
						id: self.id,
						zip_code: self.zipCode,
						address: self.address,
					},
					success: function (response) {
						if (response.data) {
							let services_areas = self.services_areas;
							if (self.id) {
								let result = services_areas.filter(obj => {
									return obj.id === self.id
								});
								services_areas.splice(services_areas.indexOf(result), 1, response.data);
							} else {
								services_areas.push(response.data);
								self.$store.commit('SET_SERVICES_AREAS', services_areas);
							}
							self.resetData();
						}
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
					this.zipCode = row.zip_code;
					this.address = row.address;
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
						action: 'delete_service_area',
						id: item.id,
						task: 'delete',
					},
					success: function (response) {
						if (response.data) {
							let services_areas = self.services_areas;
							services_areas.splice(services_areas.indexOf(item), 1);
							self.$store.commit('SET_SERVICES_AREAS', services_areas);
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
	.repair-services-areas-list {
		.mdl-button--fab {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 100;
		}
	}
</style>
