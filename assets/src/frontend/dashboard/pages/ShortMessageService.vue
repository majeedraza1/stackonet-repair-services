<template>
	<div>
		<div class="filter-nav-top">
			<div class="filter-nav-top__data-source">
				<select v-model="data_source">
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

			<mdl-button type="raised" color="primary" @click="filterData">Filter</mdl-button>
		</div>

		<div v-if="filter_datetime === 'custom'">
			<flat-pickr v-model="date_from" placeholder="Select start date"></flat-pickr>
			<flat-pickr v-model="date_to" placeholder="Select end date"></flat-pickr>
		</div>


		<columns>
			<column>
				<mdl-table :rows="items" :columns="columns"></mdl-table>
			</column>
			<column>
				<div class="form-field" style="margin-top: 40px;">
					<mdl-button type="raised" color="default">Select SMS Template</mdl-button>
				</div>
				<div class="form-field">
					<textarea name="" id="" cols="30" rows="10"></textarea>
				</div>
				<div class="form-field">
					<mdl-button type="raised" color="primary">Send SMS</mdl-button>
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
		data() {
			return {
				data_source: 'all',
				filter_datetime: 'today',
				date_from: '',
				date_to: '',
				items: [
					{name: 'Sayful', phone: '123456'},
					{name: 'Sayful', phone: '123456'},
					{name: 'Sayful', phone: '123456'},
					{name: 'Sayful', phone: '123456'},
				],
				columns: [
					{key: 'name', label: 'Name'},
					{key: 'phone', label: 'Phone', numeric: false},
					{key: 'date', label: 'Date', numeric: false}
				]
			}
		},
		mounted() {
			this.$store.commit('SET_TITLE', 'SMS');
			this.$store.commit('SET_LOADING_STATUS', false);
		},
		methods: {
			filterData() {
				let parms = `?source=${this.data_source}&type=${this.filter_datetime}&from=${this.date_from}&to=${this.date_to}`;
				axios
					.get(window.PhoneRepairs.rest_root + '/sms' + parms)
					.then(response => {
						console.log(response.data);
						if (response.data.data) {
							this.items = response.data.data.items;
						}
					})
					.catch(error => {
						console.log(error);
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
</style>
