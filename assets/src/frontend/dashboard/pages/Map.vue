<template>
	<div>
		<columns>
			<column :tablet="4">

				<search-box v-model="place_text" @submit="updatePlaceData" @clear="clearPlaceData"></search-box>
				<div class="radius-slider">
					<mdl-slider v-model="radius" :default="100" :max="500" :step="10"></mdl-slider>
				</div>

				<div class="places-box">
					<address-box
						v-for="(_place, index) in places" :key="index + 100" :place="_place"
						:active="(-1 !== selectedPlaces.indexOf(_place))" @click="selectPlace"
					/>
					<div class="places-box__more" v-if="hasNextPage">
						<mdl-button type="raised" color="primary" style="width: 100%;" @click="loadMore">Load More
						</mdl-button>
					</div>
				</div>
			</column>
			<column :tablet="8">
				<g-map-autocomplete type="text" label="Base Address" @change="setBaseAddress"></g-map-autocomplete>
				<div id="map"></div>
				<div class="selected-places">
					<div style="display: none;">
						<mdl-button type="raised" @click="showDateTime = !showDateTime">
							{{!showDateTime ? 'Show Time':'Hide Time'}}
						</mdl-button>
					</div>
					<columns>
						<column>
							<div v-if="user_formatted_address.length"
								 class="places-box__item places-box__selected-item mdl-shadow--4dp">
								<div class="places-box__left">
									<div class="places-box__name">Base Address:</div>
									<div class="places-box__formatted_address" v-html="user_formatted_address"></div>
								</div>
								<div class="places-box__right">
									<div class="places-box__index">A</div>
									<div style="position: relative;">
										<a class="input-button" title="toggle" data-toggle>
											<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
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
							</div>

							<label for="travelMode">Mode of Travel:</label>
							<select id="travelMode" v-model="travelMode">
								<option value="DRIVING">Driving</option>
								<option value="WALKING">Walking</option>
								<option value="BICYCLING">Bicycling</option>
								<option value="TRANSIT">Transit</option>
							</select>
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
		<modal :active="showFilterModal" class="selected-places" content-size="full" title="Address"
			   @close="showFilterModal = false">
			<draggable v-model="selectedPlaces" class="shapla-columns is-multiline" @change="updateMapRoute">
				<column :tablet="6" :desktop="4" v-for="(_place, index) in selectedPlaces" :key="index">
					<address-box :key="index + 200" :place="_place">
						<div class="places-box__index">{{alphabets[index+1]}}</div>
						<div style="position: relative;display: none">
							<a class="input-button" title="toggle" data-toggle>
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
									 viewBox="0 0 32 32">
									<title>clock</title>
									<path
										d="M16 32c8.822 0 16-7.178 16-16s-7.178-16-16-16-16 7.178-16 16 7.178 16 16 16zM16 1c8.271 0 15 6.729 15 15s-6.729 15-15 15-15-6.729-15-15 6.729-15 15-15zM20.061 21.768c0.098 0.098 0.226 0.146 0.354 0.146s0.256-0.049 0.354-0.146c0.195-0.195 0.195-0.512 0-0.707l-4.769-4.768v-6.974c0-0.276-0.224-0.5-0.5-0.5s-0.5 0.224-0.5 0.5v7.181c0 0.133 0.053 0.26 0.146 0.354l4.915 4.914zM3 16c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM27 16c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM15 4c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM15 28c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM7 8c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM23 24c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM24 8c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM7 24c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1z"></path>
								</svg>
							</a>
							<flat-pickr
								style="visibility: hidden;width: 1px;height: 1px;position: absolute;top: 0;left: 0;"
								:config="flatpickrConfig"
								value=""
								@input="chooseDate($event)"
								placeholder="Select date"
							/>
						</div>
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
	</div>
</template>

<script>
	import {column, columns} from 'shapla-columns';
	import draggable from 'vuedraggable'
	import deleteIcon from "shapla-delete";
	import modal from "shapla-modal";
	import Icon from "../../../shapla/icon/icon";
	import SearchBox from "../../../components/SearchBox";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import MdlSlider from "../../../material-design-lite/slider/mdlSlider";
	import GMapAutocomplete from "../../components/gMapAutocomplete";
	import FlatPickr from "vue-flatpickr-component/src/component";
	import AddressBox from "../../../components/AddressBox";

	let mapStyles = require('./map-style.json');
	import {MapMixin} from "./MapMixin";

	export default {
		name: "Map",
		mixins: [MapMixin],
		components: {
			AddressBox, FlatPickr, GMapAutocomplete, MdlSlider, MdlButton,
			SearchBox, deleteIcon, Icon, columns, column, modal, draggable
		},
		data() {
			return {
				showFilterModal: false,
				openBoxActionModal: false,
				googleMap: '',
				placesService: '',
				directionsService: '',
				directionsRenderer: '',
				selectedPlaces: [],
				places: [],
				markers: [],
				place_text: '',
				location: '',
				dataLoaded: false,
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

			// Test Value
			this.latitude = 12.9372094;
			this.longitude = 77.61974409999993;
			this.place_text = 'Anand sweets';
			setTimeout(() => {
				self.updatePlaceData();
				self.geoCodeToAddress(this.latitude, this.longitude);
			}, 1000);
			// Test Value End

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
		},
		methods: {
			openIntervalModal(place) {
				this.openBoxActionModal = true;
				this.activePlace = place;
				// this.intervalHours = place.interval_hour;
				// this.intervalMinutes = place.interval_hour;
			},
			closeIntervalModal() {
				this.intervalHours = '';
				this.intervalMinutes = '';
				this.openBoxActionModal = false;
				this.activePlace = {};
			},
			confirmInterval() {
				let index = this.selectedPlaces.indexOf(this.activePlace);
				this.activePlace.interval_hour = this.intervalHours;
				this.activePlace.interval_minute = this.intervalMinutes;
				this.selectedPlaces[index] = this.activePlace;
				this.closeIntervalModal();
			},
			chooseDate(event) {
				console.log(event);
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
			//Returns Distance between two latlng objects using haversine formula
			distance(placeOne, placeTwo) {
				if (!placeOne || !placeTwo) return 0;

				let R = 6371000; // Radius of the Earth in m
				let dLat = (placeTwo.lat() - placeOne.lat()) * Math.PI / 180;
				let dLon = (placeTwo.lng() - placeOne.lng()) * Math.PI / 180;
				let a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
					Math.cos(placeOne.lat() * Math.PI / 180) * Math.cos(placeTwo.lat() * Math.PI / 180) *
					Math.sin(dLon / 2) * Math.sin(dLon / 2);
				let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
				return R * c;
			},
			metres_to_km(metres) {
				if (metres < 100) return Math.round(metres) + " metres away";
				if (metres < 1000) return (metres / 1000).toFixed(2) + " km away";
				return (metres / 1000).toFixed(1) + " km away";
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
				let index = this.selectedPlaces.indexOf(place);
				if (-1 !== index) {
					this.selectedPlaces.splice(index, 1);
				} else {
					this.selectedPlaces.push(place);
				}
				this.updateMapRoute();
			},
			clearPlaceData() {
				this.places = [];
				this.hasNextPage = false;
				this.dataLoaded = false;
				this.clearMarkers();
			},
			updatePlaceData() {
				if (this.place_text.length < 3) {
					alert('Please enter at least three characters.');
					return;
				}
				let self = this,
					request = {
						location: self.location,
						radius: self.radius_meters,
						query: self.place_text
					};
				self.$store.commit('SET_LOADING_STATUS', true);

				// Perform a nearby search.
				self.places = [];
				self.placesService.textSearch(request, function (results, status, pagination) {
						self.$store.commit('SET_LOADING_STATUS', false);
						if (status !== 'OK') return;
						self.pagination = pagination;
						self.hasNextPage = pagination.hasNextPage;
						self.createMarkers(results);
					}
				);
				self.dataLoaded = true;
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
						name: place.name,
						formatted_address: place.formatted_address,
						location: place.geometry.location,
						distance: place.distance,
					});

					bounds.extend(place.geometry.location);
				}
				self.googleMap.fitBounds(bounds);
			},
			clearMarkers() {
				for (let i = 0; i < this.markers.length; i++) {
					this.markers[i].setMap(null);
				}
				this.markers = [];
			},

			/**
			 * Update map routes
			 */
			updateMapRoute() {
				// Clear current route
				this.directionsRenderer.setDirections({routes: []});

				// Get total selected item length
				let totalItem = this.selectedPlaces.length;

				// Exit if length is less than 1
				if (totalItem < 1) return;

				let lastIndex = totalItem - 1,
					lastItem = this.selectedPlaces[lastIndex];

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
						_data.leg = legs[i];
						_data.interval_hour = _data.interval_hour ? _data.interval_hour : '';
						_data.interval_minute = _data.interval_minute ? _data.interval_minute : '';
						_data.reach_time = '';
						_data.leave_time = '';
						if (i === 0) {
							_data.reach_time = this.baseTime.getTime() + (_data.leg.duration.value * 1000);
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
