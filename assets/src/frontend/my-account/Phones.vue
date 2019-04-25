<template>
	<div class="my-account-phones">
		<div class="action-button-container">
			<button class="button button--primary button-add-new-phone"
					@click="openAddNewModal">Add New
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
				<td class="woocommerce-orders-table__cell" data-title="Issues">
					<span v-if="phone.issues && phone.issues.length">{{phone.issues.join(', ')}}</span>
				</td>
				<td class="woocommerce-orders-table__cell" data-title="LCD Broken?" v-html="phone.broken_screen"></td>
				<td class="woocommerce-orders-table__cell" data-title="Actions">
					<a href="" title="Edit" class="icon-container" @click.prevent="editPhoneDetails(phone)">
						<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
							 viewBox="0 0 16 16">
							<path
								d="M13.813 4.688l-1.219 1.219-2.5-2.5 1.219-1.219c0.25-0.25 0.688-0.25 0.938 0l1.563 1.563c0.25 0.25 0.25 0.688 0 0.938zM2 11.5l7.375-7.375 2.5 2.5-7.375 7.375h-2.5v-2.5z"></path>
						</svg>
					</a>
					<a href="" title="View" class="icon-container" @click.prevent="viewPhoneDetails(phone)">
						<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
							 viewBox="0 0 16 16">
							<path
								d="M8 6c1.094 0 2 0.906 2 2s-0.906 2-2 2-2-0.906-2-2 0.906-2 2-2zM8 11.344c1.844 0 3.344-1.5 3.344-3.344s-1.5-3.344-3.344-3.344-3.344 1.5-3.344 3.344 1.5 3.344 3.344 3.344zM8 3c3.344 0 6.188 2.063 7.344 5-1.156 2.938-4 5-7.344 5s-6.188-2.063-7.344-5c1.156-2.938 4-5 7.344-5z"></path>
						</svg>
					</a>
					<a href="" title="Note" class="icon-container" v-if="phone.notes.length"
					   @click.prevent="viewNotes(phone)">
						<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
							 viewBox="0 0 16 16">
							<path
								d="M12 5.344v-1.344h-5.344v1.344h5.344zM12 7.344v-1.344h-5.344v1.344h5.344zM10 9.344v-1.344h-3.344v1.344h3.344zM5.344 5.344v-1.344h-1.344v1.344h1.344zM5.344 7.344v-1.344h-1.344v1.344h1.344zM5.344 9.344v-1.344h-1.344v1.344h1.344zM13.344 1.344c0.719 0 1.313 0.594 1.313 1.313v8c0 0.719-0.594 1.344-1.313 1.344h-9.344l-2.656 2.656v-12c0-0.719 0.594-1.313 1.313-1.313h10.688z"></path>
						</svg>
					</a>
				</td>
			</tr>
			</tbody>
		</table>
		<mdl-modal :active="isModalActive" :title="modalTitle" @close="closePhoneAddModal">
			<div class="columns is-multiline">
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
						<label>LCD Broken?</label>
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
				<list-item label="Asset Number">{{activePhone.id}}</list-item>
				<list-item label="Brand Name">{{activePhone.brand_name}}</list-item>
				<list-item label="Model">{{activePhone.model}}</list-item>
				<list-item label="Color">{{activePhone.color}}</list-item>
				<list-item label="IMEI Number">{{activePhone.imei_number}}</list-item>
				<list-item label="LCD Broken?">{{activePhone.broken_screen}}</list-item>
				<list-item label="Issues">{{activePhone.issues.join(', ')}}</list-item>
				<list-item label="Status">{{activePhone.status}}</list-item>
				<list-item label="Created">{{activePhone.created_at}}</list-item>
				<list-item label="Modified">{{activePhone.updated_at}}</list-item>
			</div>
			<div slot="foot">
				<mdl-button @click="closeViewModel">Close</mdl-button>
			</div>
		</mdl-modal>
		<phone-edit-modal
			:active="isEditModalActive"
			:phone="editPhone"
			title="Edit Phone"
			@close="closePhoneEditModal"
		/>
		<mdl-modal :active="isNoteModalActive" title="Notes" @close="closeNoteModal">
			<div class="phone-note-list-container">
				<div class="phone-note-list" v-if="notePhone && notePhone.notes">
					<div class="phone-note-list__item mdl-shadow--2dp" v-for="_note in notePhone.notes">
						<div class="note_content">
							<p>{{_note.note}}</p>
						</div>
						<p class="meta">
							<abbr class="exact-date" :title="_note.created_at">added on {{_note.created_at}}</abbr>
						</p>
					</div>
				</div>
			</div>
			<div slot="foot">
				<mdl-button @click="closeNoteModal">Close</mdl-button>
			</div>
		</mdl-modal>
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
				isNoteModalActive: false,
				modalTitle: 'Add New Phone',
				models: [],
				colors: [],
				zIndex: [],
				selectedIssues: [],
				isViewModalActive: false,
				activePhone: {},
				editPhone: {},
				notePhone: {},
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

			this.zIndex['navbar'] = jQuery('#navbar').css('z-index');
		},
		methods: {
			zIndexToInitial() {
				jQuery('#navbar').css('z-index', this.zIndex['navbar']);
			},
			fixInitialzIndex() {
				jQuery('#navbar').css('z-index', 'auto');
			},
			openAddNewModal() {
				this.isModalActive = true;
				this.fixInitialzIndex();
			},
			editPhoneDetails(phone) {
				this.editPhone = phone;
				this.isEditModalActive = true;
				this.fixInitialzIndex();
			},
			viewPhoneDetails(phone) {
				this.activePhone = phone;
				this.isViewModalActive = true;
				this.fixInitialzIndex();
			},
			viewNotes(phone) {
				this.notePhone = phone;
				this.isNoteModalActive = true;
				this.fixInitialzIndex();
			},
			closePhoneAddModal() {
				this.isModalActive = false;
				this.zIndexToInitial();
			},
			closePhoneEditModal() {
				this.isEditModalActive = false;
				this.zIndexToInitial();
			},
			closeNoteModal() {
				this.isNoteModalActive = false;
				this.zIndexToInitial();
			},
			closeViewModel() {
				this.activePhone = {};
				this.isViewModalActive = false;
				this.zIndexToInitial();
			},
			savePhone() {
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
				this.isModalActive = false;
				this.zIndexToInitial();
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
	.phone-note-list__item {
		padding: 0.5rem;
		margin-bottom: 1rem;

		p {
			margin: 0;
			padding: 0;
		}
	}

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

		input[type="text"] {
			width: 100% !important;
		}

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

	.icon-container {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 20px;
		height: 20px;
		float: left;
		text-align: center;
		border: 1px solid rgba(#333, 0.85);
		border-radius: 3px;

		&:not(:last-child) {
			margin-right: 5px;
		}

		svg {
			overflow: hidden;
			fill: rgba(#333, 0.85);
		}
	}
</style>
