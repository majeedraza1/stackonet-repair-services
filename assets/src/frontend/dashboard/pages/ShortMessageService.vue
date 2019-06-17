<template>
	<div class="stackonet-dashboard-sms">
		<div class="filter-nav-top">
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

			<div class="filter-nav-top__filter_datetime">
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
				<mdl-button type="raised" :color="filter_datetime === 'custom'?'primary':'default'"
							@click="filter_datetime = 'custom'">Custom
				</mdl-button>
			</div>

			<mdl-button type="raised" color="primary" @click="filterData" :disabled="!canFilter">Filter</mdl-button>
		</div>

		<div v-if="filter_datetime === 'custom'">
			<flat-pickr v-model="date_from" placeholder="Select start date"></flat-pickr>
			<flat-pickr v-model="date_to" placeholder="Select end date"></flat-pickr>
		</div>

		<columns>
			<column>
				<mdl-table :rows="items" :columns="columns" :total-items="items.length" @checkedItems="checkedItems"
						   :per-page="items.length" index="phone"></mdl-table>
			</column>
			<column>
				<div class="form-field" style="margin-top: 40px;">
					<mdl-button type="raised" color="default">Select SMS Template</mdl-button>
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
					<mdl-button type="raised" color="primary" :disabled="!canSendMessage" @click="sendSms">Send SMS
					</mdl-button>
				</div>
			</column>
		</columns>
	</div>
</template>

<script>
	import axios from 'axios';
	import MdlTable from "../../../material-design-lite/data-table/mdlTable";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import flatPickr from 'vue-flatpickr-component';
	import Columns from "../../../shapla/columns/columns";
	import Column from "../../../shapla/columns/column";

	export default {
		name: "ShortMessageService",
		components: {Column, Columns, MdlButton, MdlTable, flatPickr},
		props: {},
		data() {
			return {
				data_source: 'all',
				filter_datetime: 'this-month',
				date_from: '',
				date_to: '',
				sms_content: '',
				items: [],
				selected_items: [],
			}
		},
		computed: {
			columns() {
				let columns = [
					{key: 'name', label: 'Name'},
					{key: 'phone', label: 'Phone', numeric: false}
				];

				if ('all' === this.data_source) {
					columns.push({key: 'data_source', label: 'Data Source', numeric: false},);
				}

				columns.push({key: 'date', label: 'Date', numeric: false});

				return columns;
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
		},
		methods: {
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

		&__filter_datetime {
			> * {
				margin-right: 5px;
			}
		}
	}

	.form-field {
		margin-bottom: 15px;
	}

	.stackonet-dashboard-sms {
		textarea {
			padding: 1rem;
			width: 100%;
			height: 7.5em;
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
