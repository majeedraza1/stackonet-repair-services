<template>
	<mdl-modal :active="active" :title="title" @close="$emit('close')">
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
						:options="issuesDropdown"
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
						<label> <input type="radio" value="yes" v-model="phone.broken_screen"> Yes </label>
						<label> <input type="radio" value="no" v-model="phone.broken_screen"> No </label>
					</div>
				</div>
			</div>
			<div class="column is-6" v-if="showStatus">
				<div class="field">
					<label for="status">Status</label><br>
					<select id="status" class="widefat woocommerce-Input" v-model="phone.status">
						<option v-for="(_status, key) in phone_statuses" :value="key">{{_status}}</option>
					</select>
				</div>
			</div>
		</div>
		<div slot="foot">
			<mdl-button @click="updatePhone">Update</mdl-button>
		</div>
	</mdl-modal>
</template>

<script>
	import {mapState} from 'vuex';
	import VueSelect from 'vue-select';
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';

	export default {
		name: "PhoneEditModal",
		components: {VueSelect, mdlModal, mdlButton},
		props: {
			active: {type: Boolean, default: false},
			showStatus: {type: Boolean, default: false},
			title: {type: String, default: 'Edit Phone'},
			phone: {
				type: Object, default: function () {
					return {
						asset_number: '',
						brand_name: '',
						model: '',
						color: '',
						imei_number: '',
						issues: [],
						broken_screen: '',
						status: '',
					}
				}
			}
		},
		data() {
			return {
				models: [],
				colors: [],
				selectedIssues: [],
			}
		},
		computed: {
			...mapState(['devices', 'issues']),
			phone_statuses() {
				return StackonetRentCenter.phone_statuses;
			},
			devicesDropdown() {
				if (!this.devices.length) return [];

				return this.devices.map(element => element.device_title);
			},
			modelsDropdown() {
				if (!this.models.length) return [];
				return this.models.map(element => element.title);
			},
			issuesDropdown() {
				if (!this.issues.length) return [];
				return this.issues.map(element => element.title);
			}
		},
		mounted() {
			this.chooseBrand(this.phone.brand_name);
			this.chooseModel(this.phone.model);
		},
		methods: {
			updatePhone() {
				this.$store.dispatch('updatePhone', this.phone);
			},
			chooseBrand(data) {
				if (!data) {
					return this.phone.brand_name = '';
				}

				this.phone.brand_name = data;

				let models = this.devices.find(element => {
					if (element.device_title === data) {
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
			},
		}
	}
</script>

<style scoped>

</style>
