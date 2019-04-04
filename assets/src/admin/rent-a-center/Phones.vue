<template>
	<div>
		<h1 class="wp-heading-inline">Phones</h1>
		<a href="#" class="page-title-action" @click.prevent="isModalActive = true">Add New</a>
		<div class="clear"></div>
		<wp-list-table
			:rows="phones"
			:columns="columns"
			:loading="loading"
			:actions="actions"
			:statuses="statuses"
			:bulk-actions="bulkActions"
			:total-items="pagination.totalCount"
			:total-pages="pagination.pageCount"
			:per-page="pagination.limit"
			:current-page="pagination.currentPage"
		></wp-list-table>
		<mdl-modal :active="isModalActive" :title="modalTitle" @close="isModalActive =false">
			<div class="columns is-multiline">
				<div class="column is-6">
					<div class="input-field">
						<label for="asset_number">Asset Number</label>
						<input type="text" id="asset_number"
							   class="woocommerce-Input woocommerce-Input--text input-text"
							   v-model="phone.asset_number">
					</div>
				</div>
				<div class="column is-6">
					<div class="input-field">
						<label for="brand_name">Brand Name</label>
						<vue-select
							id="brand_name"
							:options="devicesDropdown"
							placeholder="Choose brand"
							@input="chooseBrand"
							:value="phone.brand_name"
							taggable push-tags
						></vue-select>
					</div>
				</div>
				<div class="column is-6">
					<div class="input-field">
						<label for="device_model">Model</label>
						<vue-select
							id="device_model"
							:options="models"
							label="title"
							placeholder="Choose model"
							@input="chooseModel"
							:value="phone.model"
							taggable push-tags
						></vue-select>
					</div>
				</div>
				<div class="column is-6">
					<div class="input-field">
						<label for="device_color">Color</label>
						<vue-select
							id="device_color"
							:options="colors"
							label="title"
							placeholder="Choose color"
							@input="chooseColor"
							:value="phone.color"
							taggable push-tags
						></vue-select>
					</div>
				</div>
				<div class="column is-6">
					<div class="input-field">
						<label for="imei_number">IMEI Number</label>
						<input type="text" id="imei_number"
							   class="woocommerce-Input woocommerce-Input--text input-text"
							   v-model="phone.imei_number">
					</div>
				</div>
				<div class="column is-6">
					<div class="input-field">
						<label for="issues">Issues</label>
						<vue-select
							id="issues"
							:options="issues"
							label="title"
							placeholder="Choose issue(s)"
							@input="chooseIssue"
							:value="phone.issues"
							multiple taggable push-tags
						></vue-select>
					</div>
				</div>
				<div class="column is-6">
					<div class="input-field">
						<label>Is Screen broken?</label>
						<div>
							<label>
								<input type="radio" value="yes" v-model="phone.broken_screen">
								Yes
							</label>
							<label>
								<input type="radio" value="no" v-model="phone.broken_screen">
								No
							</label>
						</div>
					</div>
				</div>
			</div>
			<div slot="foot">
				<mdl-button type="raised" color="primary" @click="savePhone">Save</mdl-button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import VueSelect from 'vue-select';
	import wpListTable from '../../wp/wpListTable.vue'
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';

	export default {
		name: "Phones",
		components: {VueSelect, wpListTable, mdlModal, mdlButton},
		data() {
			return {
				isModalActive: false,
				modalTitle: 'Add New Phone',
				models: [],
				colors: [],
				selectedIssues: [],
				phone: {
					asset_number: '',
					brand_name: '',
					model: '',
					color: '',
					imei_number: '',
					issues: [],
					broken_screen: 'no',
				},
				errors: {},
				columns: [
					{key: 'asset_number', label: 'Asset Number'},
					{key: 'brand_name', label: 'Brand Name'},
					{key: 'model', label: 'Model'},
					{key: 'color', label: 'Color'},
					{key: 'imei_number', label: 'IMEI Number'},
				],
				status: 'all',
				default_statuses: [
					{key: 'all', label: 'All', count: 0, active: true},
					{key: 'processing', label: 'Processing', count: 0, active: false},
					{key: 'arriving-soon', label: 'Arriving Soon', count: 0, active: false},
					{key: 'picked-off', label: 'Picked off', count: 0, active: false},
					{key: 'not-picked-off', label: 'Not Picked off', count: 0, active: false},
					{key: 'repairing', label: 'Repairing', count: 0, active: false},
					{key: 'not-repaired', label: 'delivered', count: 0, active: false},
					{key: 'delivered', label: 'Delivered', count: 0, active: false},
					{key: 'trash', label: 'Trash', count: 0, active: false},
				]
			}
		},
		computed: {
			...mapState(['loading', 'phones', 'devices', 'issues', 'pagination', 'counts']),
			devicesDropdown() {
				if (!this.devices.length) return [];

				return this.devices.map((element) => {
					return {
						value: element.id,
						label: element.device_title,
					}
				})
			},
			modelsDropdown() {
				if (!this.models.length) return [];
				return this.models.map(element => element.title);
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

				return [{key: 'edit', label: 'Edit'}, {key: 'view', label: 'View'},
					{key: 'upload', label: 'Upload'}, {key: 'trash', label: 'Trash'}];
			},
			bulkActions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
		},
		mounted() {
			if (!this.devices.length) {
				this.$store.dispatch('fetchDevices');
			}
			if (!this.issues.length) {
				this.$store.dispatch('fetchIssues');
			}
			if (!this.phones.length) {
				this.$store.dispatch('fetchPhones');
			}
			this.$store.commit('SET_LOADING_STATUS', false);
		},
		methods: {
			savePhone() {
				this.isModalActive = false;
				let data = {
					asset_number: this.phone.asset_number,
					brand_name: this.phone.brand_name,
					model: this.phone.model,
					color: this.phone.color,
					imei_number: this.phone.imei_number,
					broken_screen: this.phone.broken_screen,
					issues: this.selectedIssues,
				};
				this.$store.dispatch('createPhone', data);
			},
			chooseBrand(data) {
				if (!data) {
					return this.phone.brand_name = '';
				}

				this.phone.brand_name = data.label;

				let models = this.devices.find(element => {
					if (element.id === data.value) {
						return element;
					}
				});

				this.models = models ? models.device_models : [];
			},
			chooseModel(data) {
				if (!data) {
					return this.phone.model = '';
				}

				if (typeof data === "object") {
					this.phone.model = data.title;
					this.colors = data.colors;
				} else if (typeof data === 'string') {
					this.phone.model = data;
				}
			},
			chooseColor(data) {
				if (!data) {
					return this.phone.color = '';
				}

				if (typeof data === "object") {
					this.phone.color = data.title;
				} else if (typeof data === 'string') {
					this.phone.color = data;
				}
			},
			chooseIssue(data) {
				if (!data) {
					return this.issues = [];
				}

				this.phone.issues = data;
				this.selectedIssues = data.map(element => element.title);
			}
		}
	}
</script>

<style lang="scss">

</style>
