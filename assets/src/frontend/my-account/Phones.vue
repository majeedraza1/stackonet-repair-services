<template>
	<div class="my-account-phones">
		<div class="action-button-container">
			<button class="button button--primary button-add-new-phone"
					@click="isModalActive = true">Add New
			</button>
		</div>
		<table class="shop_table shop_table_responsive my_account_phones">
			<thead>
			<tr>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Device</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Issues</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">LCD Broken?</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Actions</span>
				</th>
			</tr>
			</thead>

			<tbody>
			<tr class="woocommerce-orders-table__row" v-for="phone in phones">
				<td class="woocommerce-orders-table__cell" data-title="Device">
					{{phone.brand_name}} {{phone.model}}
				</td>
				<td class="woocommerce-orders-table__cell" data-title="Issues"
					v-html="phone.issues.join(', ')"></td>
				<td class="woocommerce-orders-table__cell" data-title="LCD Broken?" v-html="phone.broken_screen"></td>
				<td class="woocommerce-orders-table__cell" data-title="Actions">
					<a href="" class="woocommerce-button button view" @click.prevent="editPhoneDetails(phone)">Edit</a>
					<a href="" class="woocommerce-button button view" @click.prevent="viewPhoneDetails(phone)">View</a>
				</td>
			</tr>
			</tbody>
		</table>
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
		<mdl-modal :active="isViewModalActive" title="Phone Details" v-if="Object.keys(activePhone).length"
				   @close="closeViewModel">
			<div class="phone-detail-info">
				<list-item label="Brand Name">{{activePhone.brand_name}}</list-item>
				<list-item label="Model">{{activePhone.model}}</list-item>
				<list-item label="Color">{{activePhone.color}}</list-item>
				<list-item label="IMEI Number">{{activePhone.imei_number}}</list-item>
				<list-item label="Is broken screen?">{{activePhone.broken_screen}}</list-item>
				<list-item label="Issues">{{activePhone.issues.join(', ')}}</list-item>
				<list-item label="Status">{{activePhone.status}}</list-item>
				<list-item label="Created">{{activePhone.created_at}}</list-item>
				<list-item label="Modified">{{activePhone.updated_at}}</list-item>
				<list-item label="Author">{{activePhone.author}}</list-item>
			</div>
			<div slot="foot">
				<mdl-button @click="closeViewModel">Close</mdl-button>
			</div>
		</mdl-modal>
		<phone-edit-modal
			:active="isEditModalActive"
			:phone="editPhone"
			title="Edit Phone"
			@close="isEditModalActive = false"
		/>
		<mdl-snackbar :options="snackbar"></mdl-snackbar>
		<div class="loading-container" :class="{'is-active':loading}">
			<div class="mdl-loader">
				<mdl-spinner :active="loading"></mdl-spinner>
			</div>
		</div>
	</div>
</template>

<script>
	import VueSelect from 'vue-select';
	import PhoneEditModal from '../../admin/rent-a-center/PhoneEditModal.vue'
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner.vue';
	import mdlSnackbar from '../../material-design-lite/snackbar/mdlSnackbar.vue';
	import AnimatedInput from '../../components/AnimatedInput.vue';
	import ListItem from '../../components/ListItem.vue';
	import {mapState} from 'vuex';

	export default {
		name: "Phones",
		components: {mdlModal, AnimatedInput, mdlButton, VueSelect, mdlSpinner, ListItem, PhoneEditModal, mdlSnackbar},
		data() {
			return {
				isModalActive: false,
				isEditModalActive: false,
				modalTitle: 'Add New Phone',
				models: [],
				colors: [],
				selectedIssues: [],
				isViewModalActive: false,
				activePhone: {},
				editPhone: {},
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
			}
		},
		computed: {
			...mapState(['loading', 'devices', 'issues', 'phones', 'snackbar']),
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
			}
		},
		mounted() {
			if (!this.devices.length) {
				this.$store.dispatch('fetchDevices');
			}
			if (!this.issues.length) {
				this.$store.dispatch('fetchIssues');
			}
			if (!this.phones.length) {
				this.$store.dispatch('getPhones');
			}
		},
		methods: {
			editPhoneDetails(phone) {
				this.editPhone = phone;
				this.isEditModalActive = true;
			},
			viewPhoneDetails(phone) {
				this.activePhone = phone;
				this.isViewModalActive = true;
			},
			closeViewModel() {
				this.activePhone = {};
				this.isViewModalActive = false;
			},
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
	.action-button-container {
		width: 100%;
		margin-bottom: 1rem;
		display: flex;
		justify-content: flex-end;

		.button-add-new-phone {
			float: right;
		}
	}

	.my-account-phones {
		.v-select {
			.dropdown-toggle {
				border-radius: 0;
				border-color: #999999;
			}
		}

		.v-select input[type=search],
		.v-select input[type=search]:focus {
			padding: 5px 7px;
		}
	}

	.loading-container.is-active {
		display: flex;
		position: fixed;
		left: 0;
		top: 0;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		width: 100%;
		height: 100%;
		background-color: rgba(#000, 0.6);
		z-index: 999999;
	}

	.phone-detail-info {
		.mdl-list-item {
			display: block;
			margin: 1rem 0;

			&-label {
				display: inline-block;
				font-weight: bold;
				min-width: 120px;
			}

			&-separator {
				width: 30px;
				display: inline-block;
				text-align: center;
			}
		}
	}
</style>
