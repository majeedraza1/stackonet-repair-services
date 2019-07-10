<template>
	<div>
		<columns>
			<column :tablet="4">

				<search-box v-model="place_text" @submit="updatePlaceData" @clear="clearPlaceData"></search-box>
				<div class="radius-slider">
					<mdl-slider v-model="radius" :default="100" :max="500" :step="10"></mdl-slider>
				</div>

				<div class="places-box">
					<div
						class="places-box__item mdl-shadow--4dp"
						v-for="(_place, index) in places"
						:key="index"
						:class="itemClass(_place)"
						@click="selectPlace(_place)"
					>
						<div class="places-box__name">{{_place.name}}</div>
						<div class="places-box__formatted_address">{{_place.formatted_address}}</div>
						<div class="places-box__formatted_distance" v-html="metres_to_km(_place.distance)"></div>
					</div>
					<div class="places-box__more" v-if="hasNextPage">
						<mdl-button type="raised" color="primary" style="width: 100%;" @click="loadMore">Load More
						</mdl-button>
					</div>
				</div>
			</column>
			<column :tablet="8">
				<g-map-autocomplete label="Base Address" @change="setBaseAddress"></g-map-autocomplete>
				<div id="map"></div>
				<div class="selected-places">
					<div style="display: none;">
						<mdl-button type="raised" @click="showDateTime = !showDateTime">
							{{!showDateTime ? 'Show Time':'Hide Time'}}
						</mdl-button>
					</div>
					<draggable v-model="selectedPlaces" class="shapla-columns is-multiline">
						<column :tablet="6" v-for="(_place, index) in selectedPlaces" :key="index">
							<div class="places-box__item places-box__selected-item mdl-shadow--4dp">
								<div class="places-box__left">
									<div class="places-box__name">{{_place.name}}</div>
									<div class="places-box__formatted_address">{{_place.formatted_address}}</div>
									<div class="places-box__formatted_distance"
										 v-html="metres_to_km(_place.distance)"></div>
								</div>
								<div class="places-box__right">
									<div>Jul 10, 2019</div>
									<div>08:55 PM</div>
									<div>
										<flat-pickr value="" placeholder="Select date"/>
										<input type="time" name="" id="">
									</div>
								</div>
							</div>
						</column>
					</draggable>
				</div>
			</column>
		</columns>
	</div>
</template>

<script>
	import {column, columns} from 'shapla-columns';
	import draggable from 'vuedraggable'
	import deleteIcon from "shapla-delete";
	import Icon from "../../../shapla/icon/icon";
	import SearchBox from "../../../components/SearchBox";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import MdlSlider from "../../../material-design-lite/slider/mdlSlider";
	import GMapAutocomplete from "../../components/gMapAutocomplete";
	import FlatPickr from "vue-flatpickr-component/src/component";

	export default {
		name: "Map",
		components: {
			FlatPickr,
			GMapAutocomplete, MdlSlider, MdlButton, SearchBox, deleteIcon, Icon, columns, column, draggable
		},
		data() {
			return {
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
			selectedPlaces(newValue) {
				let totalItem = newValue.length;
				if (totalItem < 2) return;
				let firstItem = newValue[0];
				let lastItem = newValue[totalItem - 1];
				let waypoints = [];
				for (let i = 0; i < totalItem; i++) {
					if (i !== 0 && i !== (totalItem - 1)) {
						waypoints.push({
							location: newValue[i].formatted_address,
							stopover: true
						});
					}
				}
				console.log(waypoints);
				// Display Route
				this.displayRoute(
					firstItem.geometry.location,
					lastItem.geometry.location,
					waypoints
				);
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

			if (navigator.geolocation && self.geolocation) {
				let geocoder = new google.maps.Geocoder;

				navigator.geolocation.getCurrentPosition(function (position) {
					self.latitude = position.coords.latitude;
					self.longitude = position.coords.longitude;

					geocoder.geocode({'location': {lat: self.latitude, lng: self.longitude}},
						function (results, status) {
							if (status === 'OK') {
								if (results[0]) {
									self.user_formatted_address = results[0].formatted_address;
								}
							}
						}
					);
				});
			}

			// Test Value
			// this.latitude = 12.9372094;
			// this.longitude = 77.61974409999993;
			// this.place_text = 'Anand sweets';
			// setTimeout(() => {
			// 	self.updatePlaceData();
			// }, 1000);
			// Test Value End

			// Create the map.
			this.location = new google.maps.LatLng(this.latitude, this.longitude);
			this.googleMap = new google.maps.Map(this.$el.querySelector('#map'), {
				center: self.location,
				zoom: 17,
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
			displayRoute(origin, destination, waypoints = []) {
				let self = this, request = {
					origin: origin,
					waypoints: waypoints,
					destination: destination,
					travelMode: 'DRIVING',
					avoidTolls: true
				};
				this.directionsService.route(request, function (response, status) {
					if (status === 'OK') {
						self.directionsRenderer.setDirections(response);
					}
				});
			},
			metres_to_km(metres) {
				if (metres < 100) return Math.round(metres) + " metres";
				if (metres < 1000) return (metres / 1000).toFixed(2) + " km";
				return (metres / 1000).toFixed(1) + " km";
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
			},
			clearPlaceData() {
				this.places = [];
				this.selectedPlaces = [];
				this.hasNextPage = false;
				this.dataLoaded = false;
				this.clearMarkers();
			},
			updatePlaceData() {
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
					self.places.push(place);

					bounds.extend(place.geometry.location);
				}
				self.googleMap.fitBounds(bounds);
			},
			clearMarkers() {
				for (let i = 0; i < this.markers.length; i++) {
					this.markers[i].setMap(null);
				}
				this.markers = [];
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
	}

	.sortable-ghost {
		.places-box__item {
			background-color: #f58730;
			color: #ffffff;
		}
	}
</style>
