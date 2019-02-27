<template>
	<div class="repair-services-devices-list">
		<h1 class="wp-heading-inline">Devices</h1>
		<a href="" class="page-title-action" @click.prevent="addNewDevice">Add New</a>
		<div class="clear"></div>
		<wp-list-table
				:columns="columns"
				:rows="devices"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="device_title"
				@action:click="onActionClick"
				@bulk:click="onBulkAction"
				:total-items="totalItems"
				:per-page="totalItems"
				:show-search="false"
				:show-cb="false"
		>
			<template slot="image_object" slot-scope="data">
				<img class="list-table-image" :src=" data.row.image.src " :alt="data.row.title">
			</template>
		</wp-list-table>
	</div>
</template>

<script>
	import wpListTable from '../../wp/wpListTable';
	import {mapState} from 'vuex';

	export default {
		name: "Devices",
		components: {wpListTable},
		data() {
			return {
				rows: [],
				columns: [
					{key: 'device_title', label: 'Title'},
					{key: 'image_object', label: 'Image'},
				],
				actions: [{key: 'edit', label: 'Edit'}, {key: 'delete', label: 'Delete'}],
				bulkActions: [],
				counts: {},
			}
		},
		computed: {
			...mapState(['loading', 'devices']),
			totalItems() {
				return this.devices.length;
			},
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.devices.length) {
				this.fetchDevices();
			}
		},
		methods: {
			addNewDevice() {
				this.$router.push('/device/new');
			},
			fetchDevices() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_devices',
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_DEVICES', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			onActionClick(action, row) {
				if ('edit' === action) {
					this.$router.push('/device/edit/' + row.id);
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
						action: 'delete_device',
						id: item.id,
						task: 'delete',
					},
					success: function (response) {
						if (response.data) {
							let devices = this.devices;
							devices.splice(devices.indexOf(item), 1);
							self.$store.commit('SET_DEVICES', devices);
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
	.repair-services-devices-list {
		.mdl-button--fab {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 100;
		}

		.list-table-image {
			max-width: 64px;
			max-height: 64px;
			width: auto;
		}
	}
</style>
