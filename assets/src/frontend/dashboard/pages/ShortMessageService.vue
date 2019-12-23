<template>
	<div class="stackonet-dashboard-sms">
		<tabs alignment="center" size="large">
			<tab name="SMS" selected>
				<div class="stackonet-dashboard-sms__sms">

					<div class="filter-nav-top">
						<div class="filter-nav-top__left">
							<div class="filter-nav-top__data-source">
								<select v-model="data_source">
									<option value="">Choose Source</option>
									<option value="all">All</option>
									<option value="orders">Orders</option>
									<option value="survey">Survey</option>
									<option value="appointment">Appointment</option>
									<option value="support-agents">Support Agents</option>
									<option value="carrier-stores">Carrier Stores</option>
								</select>
							</div>
							<mdl-button type="raised" :color="filter_criteria === 'pre-define'?'primary':'default'"
										@click="filter_criteria = 'pre-define'">Filter Criteria
							</mdl-button>
							<mdl-button type="raised" :color="filter_criteria === 'custom'?'primary':'default'"
										@click="setFilterCriteria('custom')">Custom
							</mdl-button>
						</div>

						<div>
							<mdl-button type="raised" color="primary" @click="filterData" :disabled="!canFilter">Filter
							</mdl-button>
						</div>
					</div>

					<div class="filter-nav-bottom">
						<div class="filter-nav-top__filter_datetime" v-if="filter_criteria === 'pre-define'">
							<mdl-button type="raised" :color="filter_datetime === 'today'?'primary':'default'"
										@click="filter_datetime = 'today'">Today
							</mdl-button>
							<mdl-button type="raised" :color="filter_datetime === 'yesterday'?'primary':'default'"
										@click="filter_datetime = 'yesterday'">Yesterday
							</mdl-button>
							<mdl-button type="raised" :color="filter_datetime === 'this-week'?'primary':'default'"
										@click="filter_datetime = 'this-week'">This Week
							</mdl-button>
							<mdl-button type="raised" :color="filter_datetime === 'last-week'?'primary':'default'"
										@click="filter_datetime = 'last-week'">Last Week
							</mdl-button>
							<mdl-button type="raised" :color="filter_datetime === 'this-month'?'primary':'default'"
										@click="filter_datetime = 'this-month'">This Month
							</mdl-button>
							<mdl-button type="raised" :color="filter_datetime === 'last-month'?'primary':'default'"
										@click="filter_datetime = 'last-month'">Last Month
							</mdl-button>
						</div>

						<div v-if="filter_criteria === 'custom'">
							<flat-pickr v-model="date_from" placeholder="Select start date"></flat-pickr>
							<flat-pickr v-model="date_to" placeholder="Select end date"></flat-pickr>
						</div>
					</div>


					<columns>
						<column>
							<mdl-table :rows="items" :columns="columns" :total-items="items.length" :mobile-width="300"
									   @checkedItems="checkedItems" :per-page="items.length" index="phone"></mdl-table>
						</column>
						<column>
							<div class="form-field" style="margin-top: 40px;">
								<mdl-button type="raised" color="default" @click="addTemplateModalOpen = true">Select
									SMS
									Template
								</mdl-button>
							</div>
							<div class="form-field">
								<div class="form-field__sms-content">
									<textarea v-model="sms_content" cols="30" rows="10"></textarea>
									<div class="status-bar">
							<span class="status-bar__sms-num-count">
								Total Numbers:
								<span class="count">{{selected_items.length}}</span>
							</span>
										<span class="status-bar__sms-chr-count">
								Total Characters:
								<span class="count">{{sms_content.length}}</span>
							</span>
									</div>
								</div>
							</div>
							<div class="form-field form-field--send-sms">
								<mdl-button type="raised" color="primary" :disabled="!canSendMessage" @click="sendSms">
									Send SMS
								</mdl-button>
							</div>
						</column>
					</columns>
				</div>
			</tab>
			<tab name="SMS Template">
				<div class="stackonet-dashboard-sms__sms-template">
					<mdl-table
							:show-cb="false"
							:rows="sms_templates"
							:columns="templateColumns"
							:actions="templateActions"
							:total-items="sms_templates.length"
							:per-page="sms_templates.length"
							action-column="content"
							@action:click="onTemplateActionClick"
					></mdl-table>

					<div class="stackonet-dashboard-sms__fab-button">
						<mdl-fab @click="addNewTemplateModalOpen = true">+</mdl-fab>
					</div>
				</div>
			</tab>
		</tabs>

		<modal :active="addNewTemplateModalOpen" title="Add new template" @close="addNewTemplateModalOpen = false">
			<textarea v-model="sms_template_content" cols="30" rows="10"></textarea>
			<template slot="foot">
				<mdl-button type="raised" color="primary" @click="saveTemplate"
							:disabled="sms_template_content.length < 5">Save
				</mdl-button>
			</template>
		</modal>

		<modal :active="editTemplateModalOpen" title="Edit SMS template" @close="editTemplateModalOpen = false">
			<textarea v-model="active_sms_template_content" cols="30" rows="10"></textarea>
			<template slot="foot">
				<mdl-button type="raised" color="primary" @click="updateTemplate"
							:disabled="active_sms_template_content.length < 5">Update
				</mdl-button>
			</template>
		</modal>

		<modal title="Choose SMS Template" :active="addTemplateModalOpen" @close="addTemplateModalOpen = false">
			<columns multiline>
				<column :tablet="6" v-for="_template in sms_templates" :key="_template.id">
					<div class="template-box" @click="selected_sms_template_content = _template.content"
						 :class="{'is-active':selected_sms_template_content === _template.content}">
						{{_template.content}}
					</div>
				</column>
			</columns>
			<template slot="foot">
				<mdl-button type="raised" color="primary" @click="insertFromTemplate">
					Insert
				</mdl-button>
			</template>
		</modal>
	</div>
</template>

<script>
	import axios from 'axios';
	import modal from "shapla-modal";
	import {column, columns} from "shapla-columns";
	import {tab, tabs} from 'shapla-tabs'
	import flatPickr from 'vue-flatpickr-component';
	import MdlFab from "../../../material-design-lite/button/mdlFab";
	import MdlTable from "../../../material-design-lite/data-table/mdlTable";
	import MdlButton from "../../../material-design-lite/button/mdlButton";

	export default {
		name: "ShortMessageService",
		components: {flatPickr, modal, MdlFab, column, columns, MdlButton, MdlTable, tab, tabs},
		props: {},
		data() {
			return {
				addTemplateModalOpen: false,
				addNewTemplateModalOpen: false,
				data_source: 'all',
				filter_datetime: 'this-month',
				filter_criteria: 'pre-define',
				date_from: '',
				date_to: '',
				sms_content: '',
				sms_template_content: '',
				selected_sms_template_content: '',
				items: [],
				selected_items: [],
				sms_templates: [],
				active_sms_template: {},
				active_sms_template_content: '',
				editTemplateModalOpen: false,
			}
		},
		computed: {
			columns() {
				let columns = [
					{key: 'name', label: 'Name'},
					{key: 'phone', label: 'Phone', numeric: false}
				];

				if ('all' === this.data_source) {
					// columns.push({key: 'data_source', label: 'Source', numeric: false},);
				}

				columns.push({key: 'date', label: 'Date', numeric: false});

				return columns;
			},
			templateColumns() {
				return [
					{key: 'content', label: 'Template Content', numeric: false}
				];
			},
			templateActions() {
				return [{key: 'edit', label: 'Edit'}, {key: 'delete', label: 'Delete'},];
			},
			canFilter() {
				if (!this.data_source.length || !this.filter_datetime.length) {
					return false;
				}

				return !('custom' === this.filter_datetime && (!this.date_from.length || !this.date_to.length));
			},
			canSendMessage() {
				return (this.sms_content.length > 5 && this.selected_items.length > 0);
			},
			phoneNumbers() {
				if (this.items.length < 1) return [];

				return this.items.map(element => element.phone);
			}
		},
		mounted() {
			this.$store.commit('SET_TITLE', 'SMS');
			this.$store.commit('SET_LOADING_STATUS', false);
			this.filterData();
			this.getTemplates();
		},
		methods: {
			setFilterCriteria(criteria) {
				if ('custom' === criteria) {
					this.filter_criteria = 'custom';
					this.filter_datetime = 'custom';
				}
			},
			insertFromTemplate() {
				this.sms_content = this.selected_sms_template_content;
				this.addTemplateModalOpen = false;
			},
			onTemplateActionClick(action, item) {
				if ('edit' === action) {
					this.active_sms_template = item;
					this.active_sms_template_content = item.content;
					this.editTemplateModalOpen = true;
				}
				if ('delete' === action && window.confirm('Are you sure to delete permanently?')) {
					this.trashAction(item);
				}
			},
			trashAction(item) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
						.delete(PhoneRepairs.rest_root + '/sms/template', {params: {id: item.id},})
						.then((response) => {
							self.getTemplates();
							self.$store.commit('SET_LOADING_STATUS', false);
						})
						.catch((error) => {
							console.log(error);
							self.$store.commit('SET_LOADING_STATUS', false);
						});
			},
			getTemplates() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
						.get(window.PhoneRepairs.rest_root + '/sms/template',)
						.then(response => {
							this.$store.commit('SET_LOADING_STATUS', false);
							this.sms_templates = response.data.data.items;
						})
						.catch(error => {
							console.log(error);
							this.$store.commit('SET_LOADING_STATUS', false);
						})
			},
			saveTemplate() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
						.post(window.PhoneRepairs.rest_root + '/sms/template', {
							content: this.sms_template_content
						})
						.then(response => {
							this.sms_template_content = '';
							this.$store.commit('SET_LOADING_STATUS', false);
							this.addNewTemplateModalOpen = false;
							this.getTemplates();
							alert('SMS template has been save.');
						})
						.catch(error => {
							console.log(error);
							this.$store.commit('SET_LOADING_STATUS', false);
						})
			},
			updateTemplate() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
						.put(window.PhoneRepairs.rest_root + '/sms/template/' + this.active_sms_template.id, {
							content: this.active_sms_template_content
						})
						.then(response => {
							this.active_sms_template_content = '';
							this.$store.commit('SET_LOADING_STATUS', false);
							this.editTemplateModalOpen = false;
							this.getTemplates();
							alert('SMS template has been save.');
						})
						.catch(error => {
							console.log(error);
							this.$store.commit('SET_LOADING_STATUS', false);
						})
			},
			checkedItems(selected_items) {
				this.selected_items = selected_items;
			},
			filterData() {
				this.$store.commit('SET_LOADING_STATUS', true);
				let parms = `?source=${this.data_source}&type=${this.filter_datetime}&from=${this.date_from}&to=${this.date_to}`;
				axios
						.get(window.PhoneRepairs.rest_root + '/sms' + parms)
						.then(response => {
							if (response.data.data) {
								this.items = response.data.data.items;
							}
							this.$store.commit('SET_LOADING_STATUS', false);
						})
						.catch(error => {
							console.log(error);
							this.$store.commit('SET_LOADING_STATUS', false);
						})
			},
			sendSms() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
						.post(window.PhoneRepairs.rest_root + '/sms', {
							numbers: this.selected_items,
							content: this.sms_content
						})
						.then(response => {
							this.selected_items = [];
							this.sms_content = '';
							this.$store.commit('SET_LOADING_STATUS', false);
							alert('SMS has been set.');
						})
						.catch(error => {
							console.log(error);
							this.$store.commit('SET_LOADING_STATUS', false);
						})
			},
			addNewTemplate() {
				this.addNewTemplateModalOpen = true;
			}
		}
	}
</script>

<style lang="scss">
	@import "../../../scss/vendors/flatpick";

	.filter-nav-top {
		align-items: center;
		display: flex;
		justify-content: space-between;
		margin-bottom: 15px;

		&__left {
			display: flex;
			flex-wrap: wrap;

			> * {
				margin-right: 5px;
				margin-bottom: 5px;
			}
		}

		&__filter_datetime {
			> * {
				margin-right: 5px;
				margin-bottom: 5px;
			}
		}
	}

	.filter-nav-bottom {
		display: flex;
		flex-direction: column;

		input.flatpickr-input {
			margin-bottom: 5px;
		}
	}

	.form-field {
		margin-bottom: 15px;
	}

	.stackonet-dashboard-sms {

		.template-box {
			border: 1px solid rgba(#000, 0.12);
			cursor: pointer;
			padding: 15px;
			height: 100%;
			display: flex;
			border-radius: 6px;

			&.is-active,
			&:hover {
				background: rgba(249, 167, 59, 0.65);
				border-color: #f58730;
				color: #fff;
			}
		}

		textarea {
			padding: 1rem;
			width: 100%;
			height: 7.5em;
		}

		&__sms,
		&__sms-template {
			margin-top: 10px;
		}

		&__fab-button {
			position: fixed;
			right: 30px;
			bottom: 30px;
		}

		.status-bar {
			background: #eee;
			padding: 1rem;
			margin-top: -8px;
			display: flex;
			align-items: center;
			justify-content: space-between;

			.count {
				color: #f58730;
				min-width: 2em;
				display: inline-block;
				text-align: center;
			}
		}
	}
</style>
