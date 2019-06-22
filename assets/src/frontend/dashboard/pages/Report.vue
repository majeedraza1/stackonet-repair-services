<template>
	<div class="stackonet-dashboard-report">
		<mdl-tabs>
			<mdl-tab name="Graph" selected>
				<div class="stackonet-dashboard-graph">
					<line-chart :chart-data="chartdata" :options="options"/>
				</div>
			</mdl-tab>
			<mdl-tab name="Calendar">
				<div class="stackonet-dashboard-calendar">
					<vue-fullcalendar :events="calendar_events" @eventClick="eventClick"></vue-fullcalendar>
					<modal :active="isModalOpen" :title="modalTitle" @close="closeModal" content-size="large">
						<div id="map"></div>
						<columns multiline mobile centered>
							<column v-for="_data in activeData" :key="_data.id" :mobile="6" :tablet="4" :desktop="3">
								<div class="shapla-box" :class="{'is-active':_data.id === activeDataOnMap.id}"
									 @click="updateMapCenter(_data)">
									<template v-if="dataType === 'order'">Order ID:</template>
									<template v-if="dataType === 'lead'">Lead ID:</template>
									{{_data.id}}
								</div>
							</column>
						</columns>
						<template v-if="dataType === 'order'">
							<div v-for="_data in activeData">
								<div class="calendar-list-item">
									<list-item label="Order Id">{{_data.id}}</list-item>
									<list-item label="Customer Name">{{_data.customer_full_name}}</list-item>
									<list-item label="Issues">{{_data.issues.toString()}}</list-item>
									<list-item label="Address">
										<span v-html="_data.address"></span><br>
										<a :href="_data.address_map_url" target="_blank">View on Map</a>
									</list-item>
									<list-item label="Total Amount">{{_data.order_total}}</list-item>
									<list-item label="Support Ticket">
										<router-link :to="{name: 'SingleSupportTicket', params: {id: _data.ticket_id}}"
													 active-class="is-active">#{{_data.ticket_id}}
										</router-link>
									</list-item>
								</div>
							</div>
						</template>
						<template v-if="dataType === 'lead'">
							<div v-for="_data in activeData">
								<div class="calendar-list-item">
									<list-item label="Id">{{_data.id}}</list-item>
									<list-item label="Store">{{_data.store_name}}</list-item>
									<list-item label="Issues">{{_data.device_issues.map(element =>
										element.title).toString()}}
									</list-item>
									<list-item label="Address">
										<span v-html="_data.full_address"></span><br>
									</list-item>
								</div>
							</div>
						</template>
						<template slot="foot">
							<mdl-button @click="closeModal">Close</mdl-button>
						</template>
					</modal>
				</div>
			</mdl-tab>
		</mdl-tabs>
	</div>
</template>

<script>
	import axios from "axios";
	import VueFullcalendar from "vue-fullcalendar";
	import LineChart from './LineChart'
	import modal from "../../../shapla/modal/modal";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import ListItem from "../../../components/ListItem";
	import Columns from "../../../shapla/columns/columns";
	import Column from "../../../shapla/columns/column";
	import MdlTabs from "../../../material-design-lite/tabs/mdlTabs";
	import MdlTab from "../../../material-design-lite/tabs/mdlTab";

	export default {
		name: "Report",
		components: {MdlTab, MdlTabs, Column, Columns, ListItem, MdlButton, modal, VueFullcalendar, LineChart},
		data() {
			return {
				isModalOpen: false,
				activeData: {},
				activeDataOnMap: {},
				events: [],
				dataType: '',
				chartdata: {},
				googleMap: null,
				markers: [],
				options: {
					responsive: true,
					maintainAspectRatio: false
				}
			}
		},
		computed: {
			modalTitle() {
				if (this.dataType === 'order') return 'Order';
				if (this.dataType === 'lead') return 'Lead';
				return 'Untitled';
			},
			calendar_events() {
				if (this.events.length < 1) return [];

				return this.events.map(element => {
					return {
						title: element.type + " " + element.counts,
						start: element.date,
						type: element.type,
						cssClass: element.type,
					}
				})
			}
		},
		mounted() {
			this.$store.commit('SET_TITLE', 'Dashboard');
			this.$store.commit('SET_LOADING_STATUS', false);
			this.getEvents();

			this.googleMap = new google.maps.Map(this.$el.querySelector('#map'), {
				zoom: 1,
				center: {lat: 32.8205865, lng: -96.871626},
			});
		},
		watch: {
			markers(_markers) {
				_markers.forEach(element => {
					new google.maps.Marker(element).setMap(this.googleMap);
				});
			}
		},
		methods: {
			updateMapCenter(data) {
				this.activeDataOnMap = data;
				this.googleMap.setZoom(18);
				this.googleMap.setCenter(new google.maps.LatLng(
					data.latitude_longitude.lat,
					data.latitude_longitude.lng
				));
			},
			eventClick(data) {
				this.$store.commit('SET_LOADING_STATUS', true);
				this.dataType = data.type;
				axios
					.get(window.PhoneRepairs.rest_root + `/calendar?date=${data.start}&type=${data.type}`,)
					.then(response => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.activeData = response.data.data;
						this.markers = this.activeData.map(element => {
							return {
								position: element.latitude_longitude,
								title: element.address
							}
						});
						this.isModalOpen = true;
					})
					.catch(error => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					})
			},
			closeModal() {
				this.isModalOpen = false;
				this.activeData = {};
				this.dataType = '';
			},
			getEvents() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.get(window.PhoneRepairs.rest_root + '/calendar',)
					.then(response => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.events = response.data.data.events;
						this.chartdata = response.data.data.chartData;
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
	.stackonet-dashboard-report {
		.shapla-box {
			cursor: pointer;
			text-align: center;

			&.is-active {
				background-image: linear-gradient(-90deg, #f9a73b, #f58730);
			}
		}
	}

	.stackonet-dashboard-graph {
		margin-top: 30px;
	}

	#map {
		height: 300px;
		margin-bottom: 1.5rem;
	}

	.stackonet-dashboard-calendar {
		.full-calendar-body .dates .dates-events .events-week .events-day .event-box .event-item {
			&.order {
				background-color: #f58730;
				color: #ffffff;
			}

			&.lead {
				background-color: #f9a73b;
				color: #ffffff;
			}
		}

		.calendar-list-item {
			border: 1px solid rgba(#000, 0.1);
			margin-bottom: 15px;

			.mdl-list-item {
				display: flex;
				padding: 8px;

				&-label {
					min-width: 130px;
				}

				&-separator {
					width: 15px;
					display: inline-flex;
					justify-content: center;
				}
			}
		}
	}
</style>
