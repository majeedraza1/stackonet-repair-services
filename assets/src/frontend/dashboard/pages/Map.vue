<template>
	<div class="stackonet-dashboard-map">
		<mdl-tabs>
			<mdl-tab name="Map " selected>
				<columns>
					<column :tablet="4">
						<search-box v-model="place_text" @submit="updatePlaceData" @clear="clearPlaceData"></search-box>
						<div class="radius-slider">
							<mdl-slider v-model="radius" :default="100" :max="500" :step="10"></mdl-slider>
						</div>

						<div class="places-box">
							<address-box
								v-for="(_place, index) in places" :key="index + 100" :place="_place"
								:active="(-1 !== selectedPlaces.findIndex(el => el.place_id === _place.place_id))"
								@click="selectPlace"
							/>
							<div class="places-box__more" v-if="hasNextPage">
								<mdl-button type="raised" color="primary" style="width: 100%;" @click="loadMore">Load
									More
								</mdl-button>
							</div>
						</div>
					</column>
					<column :tablet="8">
						<g-map-autocomplete type="text" label="Base Address"
											@change="setBaseAddress"></g-map-autocomplete>
						<div id="map"></div>
						<div class="selected-places">
							<columns>
								<column>
									<div v-if="user_formatted_address.length"
										 class="places-box__item places-box__selected-item mdl-shadow--4dp">
										<div class="places-box__left">
											<div class="places-box__name">Base Address:</div>
											<div class="places-box__formatted_address"
												 v-html="user_formatted_address"></div>
										</div>
										<div class="places-box__right">
											<div class="places-box__index">A</div>
											<div style="position: relative;">
												<a class="input-button" title="toggle" data-toggle>
													<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24"
														 height="24"
														 viewBox="0 0 32 32">
														<title>clock</title>
														<path
															d="M16 32c8.822 0 16-7.178 16-16s-7.178-16-16-16-16 7.178-16 16 7.178 16 16 16zM16 1c8.271 0 15 6.729 15 15s-6.729 15-15 15-15-6.729-15-15 6.729-15 15-15zM20.061 21.768c0.098 0.098 0.226 0.146 0.354 0.146s0.256-0.049 0.354-0.146c0.195-0.195 0.195-0.512 0-0.707l-4.769-4.768v-6.974c0-0.276-0.224-0.5-0.5-0.5s-0.5 0.224-0.5 0.5v7.181c0 0.133 0.053 0.26 0.146 0.354l4.915 4.914zM3 16c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM27 16c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM15 4c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM15 28c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM7 8c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM23 24c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM24 8c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM7 24c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1z"></path>
													</svg>
												</a>
												<flat-pickr
													style="visibility: hidden;width: 1px;height: 1px;position: absolute;top: 0;left: 0;"
													:config="flatpickrConfig"
													v-model="baseTime"
													placeholder="Select date"/>
											</div>
											<div v-html="formatDate(baseTime)"></div>
											<div v-html="formatTime(baseTime)"></div>
										</div>
									</div>
								</column>
								<column>
									<div v-if="selectedPlaces.length > 1">
										<mdl-button type="raised" color="primary" @click="showFilterModal = true">
											Re-Arrange Address
										</mdl-button>
										<mdl-button type="raised" color="primary" @click="showRecordTitleModal = true">
											Save as Route
										</mdl-button>
									</div>

									<template v-if="user_formatted_address.length">
										<label for="travelMode">Mode of Travel:</label>
										<select id="travelMode" v-model="travelMode">
											<option value="DRIVING">Driving</option>
											<option value="WALKING">Walking</option>
											<option value="BICYCLING">Bicycling</option>
											<option value="TRANSIT">Transit</option>
										</select>
									</template>
								</column>
							</columns>
							<columns multiline>
								<column :tablet="6" v-for="(_place, index) in selectedPlaces" :key="index">
									<address-box :place="_place">
										<div class="places-box__index">{{alphabets[index+1]}}</div>
										<div class="places-box__action">
											<mdl-button type="icon" @click="openIntervalModal(_place)">+</mdl-button>
										</div>
									</address-box>
								</column>
							</columns>
						</div>
					</column>
				</columns>
			</mdl-tab>
			<mdl-tab name="Saved Maps List">
				<map-list-table></map-list-table>
			</mdl-tab>
		</mdl-tabs>


		<modal :active="showFilterModal" class="selected-places" content-size="full" title="Address"
			   @close="showFilterModal = false">
			<draggable v-model="selectedPlaces" class="shapla-columns is-multiline" @change="updateMapRoute">
				<column :tablet="6" :desktop="4" v-for="(_place, index) in selectedPlaces" :key="_place.place_id">
					<address-box :key="index + 200" :place="_place">
						<div class="places-box__index">{{alphabets[index+1]}}</div>
					</address-box>
				</column>
			</draggable>
			<div slot="foot">
				<mdl-button @click="showFilterModal = false">Close</mdl-button>
			</div>
		</modal>
		<modal :active="openBoxActionModal" @close="closeIntervalModal" title="Interval Hours" content-size="small">
			<div>
				<label for="hours">Hours</label>
				<input type="text" id="hours" v-model="intervalHours">
			</div>
			<div>
				<label for="minutes">Minutes</label>
				<input type="text" id="minutes" v-model="intervalMinutes">
			</div>
			<div slot="foot">
				<mdl-button type="raised" color="primary" @click="confirmInterval">Confirm</mdl-button>
			</div>
		</modal>
		<modal :active="showRecordTitleModal" content-size="small" title="Record Title"
			   @close="showRecordTitleModal = false">
			<animated-input label="Title" v-model="recordTitle"></animated-input>
			<div slot="foot">
				<mdl-button @click="saveAsRoute">Save</mdl-button>
			</div>
		</modal>
	</div>
</template>

<script>
	import axios from 'axios'
	import {column, columns} from 'shapla-columns';
	import draggable from 'vuedraggable'
	import deleteIcon from "shapla-delete";
	import modal from "shapla-modal";
	import FlatPickr from "vue-flatpickr-component/src/component";
	import {MapMixin} from "./MapMixin";
	import MapListTable from "./MapListTable";
	import Icon from "../../../shapla/icon/icon";
	import SearchBox from "../../../components/SearchBox";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import MdlSlider from "../../../material-design-lite/slider/mdlSlider";
	import GMapAutocomplete from "../../components/gMapAutocomplete";
	import AddressBox from "../../../components/AddressBox";
	import MdlTabs from "../../../material-design-lite/tabs/mdlTabs";
	import MdlTab from "../../../material-design-lite/tabs/mdlTab";
	import AnimatedInput from "../../../components/AnimatedInput";

	let mapStyles = require('./map-style.json');

	export default {
		name: "Map",
		mixins: [MapMixin],
		components: {
			AnimatedInput,
			MapListTable,
			MdlTab,
			MdlTabs,
			AddressBox, FlatPickr, GMapAutocomplete, MdlSlider, MdlButton,
			SearchBox, deleteIcon, Icon, columns, column, modal, draggable
		},
		data() {
			return {
				showFilterModal: false,
				openBoxActionModal: false,
				showRecordTitleModal: false,
				googleMap: '',
				placesService: '',
				directionsService: '',
				directionsRenderer: '',
				selectedPlaces: [],
				places: [],
				markers: [],
				place_text: '',
				location: '',
				pagination: null,
				hasNextPage: false,
				showDateTime: false,
				radius: 100,
				latitude: 0,
				longitude: 0,
				user_formatted_address: '',
				address: '',
				travelMode: 'DRIVING',
				flatpickrConfig: {
					dateFormat: 'Y-m-d h:i K',
					enableTime: true,
					minDate: new Date(),
					wrap: true,
				},
				alphabets: [],
				legs: [],
				baseTime: '',
				activePlace: {},
				intervalHours: '',
				intervalMinutes: '',
				recordTitle: '',
			}
		},
		watch: {
			user_formatted_address(newValue) {
				let latLng = new google.maps.LatLng(this.latitude, this.longitude);
				this.googleMap.setCenter(latLng);
				new google.maps.Marker({
					map: this.googleMap,
					title: newValue,
					position: latLng
				});
			},
			travelMode() {
				this.updateMapRoute();
			}
		},
		computed: {
			radius_meters() {
				return this.radius * 100;
			}
		},
		mounted() {
			let self = this;
			this.$store.commit('SET_LOADING_STATUS', false);

			// Set base time
			this.baseTime = new Date();

			// Get user location from geo-location
			if (navigator.geolocation && self.geolocation) {
				navigator.geolocation.getCurrentPosition(function (position) {
					self.latitude = position.coords.latitude;
					self.longitude = position.coords.longitude;
					self.geoCodeToAddress(position.coords.latitude, position.coords.longitude);
				});
			}

			this.alphabets = String.fromCharCode(..." ".repeat(26).split("").map((e, i) => i + 'A'.charCodeAt())).split('');

			// Create the map.
			this.location = new google.maps.LatLng(this.latitude, this.longitude);
			this.googleMap = new google.maps.Map(this.$el.querySelector('#map'), {
				center: self.location,
				zoom: 17,
				styles: mapStyles
			});
			// Create the places service.
			this.placesService = new google.maps.places.PlacesService(self.googleMap);
			// Create the direction service
			this.directionsService = new google.maps.DirectionsService;
			this.directionsRenderer = new google.maps.DirectionsRenderer({
				map: this.googleMap,
			});

			google.maps.event.addListener(this.googleMap, 'click', (event) => {
				this.placesService.getDetails({placeId: event.placeId}, (place, status) => {
					if (status === google.maps.places.PlacesServiceStatus.OK) {
						let _place = {
							place_id: place.place_id,
							name: place.name,
							formatted_address: place.formatted_address,
							location: place.geometry.location,
							distance: 0,
							interval_hour: 0,
							interval_minute: 0,
							reach_time: 0,
							leave_time: 0,
							leg: ''
						};
						this.$modal.confirm({
							message: `Do you want to add this address to list?<br><br><strong>${place.name}</strong><br>${place.formatted_address}`,
							confirmButton: 'Yes',
							cancelButton: 'No'
						}).then(confirmed => {
							if (confirmed) {
								this.places.unshift(_place);
								this.selectedPlaces.push(_place);
								setTimeout(() => {
									this.updateMapRoute();
								})
							}
						})
					}
				});
			});
		},
		methods: {
			saveAsRoute() {
				let _data = {
					title: this.recordTitle,
					formatted_base_address: this.user_formatted_address,
					base_address_latitude: this.latitude,
					base_address_longitude: this.longitude,
					base_datetime: this.baseTime,
					place_text: this.place_text,
					travel_mode: this.travelMode,
					places: this.selectedPlaces,
				};
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/map', _data)
					.then(response => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.showRecordTitleModal = false;
						this.$root.$emit('show-notification', {
							title: 'Success!',
							message: 'Map data has been saved successfully.',
							type: 'success',
						});
						this.$store.dispatch('refreshMapList');
					})
					.catch(error => {
						this.$store.commit('SET_LOADING_STATUS', false);
						console.log(error);
					})
			},
			openIntervalModal(place) {
				this.openBoxActionModal = true;
				this.activePlace = place;
			},
			closeIntervalModal() {
				this.intervalHours = '';
				this.intervalMinutes = '';
				this.openBoxActionModal = false;
				this.activePlace = {};
			},
			confirmInterval() {
				let place = this.activePlace, addresses = this.selectedPlaces;
				let index = addresses.findIndex(el => el.place_id === place.place_id);
				place['interval_hour'] = this.intervalHours.length ? parseInt(this.intervalHours) : 0;
				place['interval_minute'] = this.intervalMinutes.length ? parseInt(this.intervalMinutes) : 0;

				let interval_seconds = (place['interval_hour'] * 60 * 60 * 1000) + (place['interval_minute'] * 60 * 1000);
				if (place.reach_time) {
					place['leave_time'] = (place.reach_time + interval_seconds);
				}

				addresses[index] = place;
				this.reCalculateArrivalAndDepartureTime(addresses);
				this.closeIntervalModal();
			},
			geoCodeToAddress(latitude, longitude) {
				let self = this,
					geocoder = new google.maps.Geocoder;
				geocoder.geocode({'location': {lat: latitude, lng: longitude}},
					function (results, status) {
						if (status === 'OK') {
							if (results[0]) {
								self.address = results[0];
								self.user_formatted_address = results[0].formatted_address;
							}
						}
					}
				);
			},
			setBaseAddress(placeData) {
				this.latitude = placeData.latitude;
				this.longitude = placeData.longitude;
				this.location = new google.maps.LatLng(placeData.latitude, placeData.longitude);
				this.user_formatted_address = placeData.formatted_address;
				this.address = placeData.address;
			},
			itemClass(place) {
				if (-1 === this.selectedPlaces.indexOf(place)) return {};
				return ['is-active'];
			},
			selectPlace(place) {
				let _place = place, addresses = this.selectedPlaces;
				let index = addresses.findIndex(el => el.place_id === _place.place_id);
				_place['interval_hour'] = 0;
				_place['interval_minute'] = 0;
				_place['reach_time'] = 0;
				_place['leave_time'] = 0;

				if (-1 !== index) {
					addresses.splice(index, 1);
				} else {
					addresses.push(_place);
				}
				this.selectedPlaces = addresses;
				this.updateMapRoute();
			},
			clearPlaceData() {
				this.places = [];
				this.hasNextPage = false;
				this.clearMarkers(this.markers);
			},
			updatePlaceData() {
				if (this.place_text.length < 3) {
					this.$root.$emit('show-notification', {
						title: 'Error!',
						message: 'Please enter at least three characters.',
						type: 'error',
					});
					return;
				}
				if (!this.latitude || !this.longitude) {
					this.$root.$emit('show-notification', {
						title: 'Error!',
						message: 'Please set base address first.',
						type: 'error',
					});
					return;
				}
				let self = this,
					request = {
						location: self.location,
						radius: self.radius_meters,
						query: self.place_text
					};
				self.$store.commit('SET_LOADING_STATUS', true);

				// this.nearbySearch(this.placesService, {
				// 	location: this.location,
				// 	radius: this.radius_meters,
				// 	keyword: this.place_text,
				// 	rankby: 'distance',
				// }).then(response => {
				// 	console.log(response);
				// 	this.$store.commit('SET_LOADING_STATUS', false);
				// 	this.pagination = response.pagination;
				// 	this.hasNextPage = response.pagination.hasNextPage;
				// 	this.createMarkers(response.results);
				// }).catch(() => {
				// 	this.$store.commit('SET_LOADING_STATUS', false);
				// });

				// Perform a text search.
				self.places = [];
				this.textSearch(this.placesService, {
					query: this.place_text,
					location: this.location,
					radius: this.radius_meters,
				}).then(response => {
					this.$store.commit('SET_LOADING_STATUS', false);
					this.pagination = response.pagination;
					this.hasNextPage = response.pagination.hasNextPage;
					this.createMarkers(response.results);
				}).catch(error => {
					this.$store.commit('SET_LOADING_STATUS', false);
				});
			},
			loadMore() {
				if (this.hasNextPage) {
					this.pagination.nextPage();
				}
			},
			createMarkers(places) {
				let self = this,
					bounds = new google.maps.LatLngBounds();

				for (let i = 0; i < places.length; i++) {
					let place = places[i];
					let image = {
						url: place.icon,
						size: new google.maps.Size(71, 71),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(17, 34),
						scaledSize: new google.maps.Size(25, 25)
					};

					let marker = new google.maps.Marker({
						map: self.googleMap,
						icon: image,
						title: place.name,
						position: place.geometry.location
					});

					place.distance = self.distance(self.location, place.geometry.location);

					self.markers.push(marker);

					self.places.push({
						place_id: place.place_id,
						name: place.name,
						formatted_address: place.formatted_address,
						location: place.geometry.location,
						distance: place.distance,
					});

					bounds.extend(place.geometry.location);
				}
				self.googleMap.fitBounds(bounds);
			},
			/**
			 * Update map routes
			 */
			updateMapRoute() {
				// Clear current route
				this.directionsRenderer.setDirections({routes: []});

				let addresses = this.selectedPlaces;

				// Get total selected item length
				let totalItem = addresses.length;

				// Exit if length is less than 1
				if (totalItem < 1) return;

				let lastIndex = totalItem - 1,
					lastItem = addresses[lastIndex];

				let waypoints = [];
				for (let i = 0; i < totalItem; i++) {
					if (i !== lastIndex) {
						waypoints.push({
							location: this.selectedPlaces[i].formatted_address,
							stopover: true
						});
					}
				}
				// Display Route
				this.displayRoute(
					this.address.geometry.location,
					lastItem.location,
					waypoints
				);
			},
			displayRoute(origin, destination, waypoints = []) {
				let self = this,
					request = {
						origin: origin,
						waypoints: waypoints,
						destination: destination,
						travelMode: google.maps.TravelMode[this.travelMode],
						avoidTolls: true,
						drivingOptions: {
							departureTime: new Date(this.baseTime),
							trafficModel: 'optimistic'
						}
					};
				this.directionsService.route(request, function (response, status) {
					if (status === 'OK') {
						if (response.routes && response.routes[0].legs) {
							self.addLegOnSelectedPlaces(response.routes[0].legs);
						}
						self.directionsRenderer.setDirections(response);
					}
				});
			},
			addLegOnSelectedPlaces(routesLegs) {
				let legs = [], _selectedPlaces = [];

				for (let i = 0; i < routesLegs.length; i++) {
					legs.push({distance: routesLegs[i].distance, duration: routesLegs[i].duration});
				}
				if (legs.length) {
					if (typeof this.baseTime === "string") {
						this.baseTime = new Date(this.baseTime);
					}
					for (let i = 0; i < legs.length; i++) {
						let _data = this.selectedPlaces[i];
						_data['leg'] = legs[i];
						_data['interval_hour'] = _data.interval_hour ? _data.interval_hour : 0;
						_data['interval_minute'] = _data.interval_minute ? _data.interval_minute : 0;
						_data['reach_time'] = _data.reach_time ? _data.reach_time : 0;
						_data['leave_time'] = _data.leave_time ? _data.leave_time : 0;
						if (i === 0) {
							_data['reach_time'] = this.baseTime.getTime() + (_data.leg.duration.value * 1000);
						}
						_selectedPlaces.push(_data);
					}

					this.selectedPlaces = _selectedPlaces;
				}
			}
		}
	}
</script>

<style lang="scss">
	.stackonet-dashboard-map {
		.mdl-tabs__panel.is-active {
			margin-top: 1rem;
		}
	}

	.selected-places {
		margin-bottom: 3rem;
		margin-top: 3rem;
	}

	.radius-slider {
		margin-top: 1rem;
	}

	.places-box {
		background: #f1f1f1;
		border: 1px solid #f1f1f1;
		height: 70vh;
		margin-top: 1rem;
		overflow: auto;
		padding: 0 1rem;

		&__selected-item {
			display: flex;
			position: relative;

			.places-box__right {
				display: flex;
				flex-direction: column;
				min-width: 90px;
				padding-left: 10px;
			}
		}

		&__item {
			background: white;
			margin: 10px 0;
			padding: 12px;

			&.is-active {
				background-color: #f58730;
				color: #ffffff;
			}

			.selected-places & {
				height: 100%;
			}
		}

		&__name {
			font-weight: bold;
		}

		&__more {
			margin-bottom: 1rem;
		}

		&__index {
			background-color: #f58730;
			color: #ffffff;
			width: 32px;
			height: 32px;
			position: absolute;
			top: 0;
			right: 0;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		&__formatted_distance {
			background: #faa644;
			display: inline-flex;
			padding: 0.5rem 1rem;
			margin-top: 1rem;
		}

		&__action {
			position: absolute;
			right: 0;
			bottom: 0;
		}
	}

	.google-address-box {
		.sortable-ghost & {
			background-color: #f58730;
			color: #ffffff;
		}

		.selected-places & {
			height: 100%;
		}
	}
</style>
