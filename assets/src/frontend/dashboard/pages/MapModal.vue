<template>
	<div v-if="has_place">
		<modal :active="active" @close="close" content-size="full" :title="title">
			<columns multiline>
				<template v-if="mode === 'edit'">
					<column :tablet="4">
						<search-box v-model="place.place_text" @submit="updatePlaceData"
									@clear="clearPlaceData"></search-box>
						<div class="radius-slider">
							<mdl-slider v-model="radius" :default="100" :max="500" :step="10"></mdl-slider>
						</div>
						<div class="places-box">
							<address-box
								v-for="(_place, index) in places" :key="index + 500" :place="_place"
								:active="(-1 !== place.places.findIndex(el => el.place_id === _place.place_id))"
								@click="selectPlace"
							/>
							<div class="places-box__more" v-if="hasNextPage">
								<mdl-button type="raised" color="primary" style="width: 100%;" @click="loadMore">
									Load More
								</mdl-button>
							</div>
						</div>
					</column>
				</template>
				<column :tablet="mode === 'edit' ? 8 : 12">
					<div id="modal-map" style="height: 300px;"></div>
					<div>
						<div class="selected-places">
							<columns multiline>
								<column v-if="place.formatted_base_address">
									<div class="places-box__item places-box__selected-item mdl-shadow--4dp">
										<div class="places-box__left">
											<div class="places-box__name">Base Address:</div>
											<div class="places-box__formatted_address"
												 v-html="place.formatted_base_address"></div>
											<div class="google-address-box__formatted_distance"
												 v-if="place.base_datetime">
												<span>ETD :&nbsp; </span>
												<span v-html="formatDate(place.base_datetime)"></span>;
												<span v-html="formatTime(place.base_datetime)"></span>
											</div>
										</div>
										<div class="places-box__right">
											<div class="places-box__index">A</div>
										</div>
									</div>
								</column>
								<column :tablet="6" :desktop="4" v-for="(_place, index) in place.places"
										:key="_place.place_id">
									<address-box :place="_place">
										<div class="places-box__index">{{alphabets[index+1]}}</div>
										<div class="places-box__action">
											<mdl-button type="icon" @click="openIntervalModal(_place)">+</mdl-button>
										</div>
										<div class="places-box__action-left">
											<mdl-button type="icon" @click="removePlace(_place)">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<rect x="0" fill="none" width="20" height="20"></rect>
													<path
														d="M12 4h3c.6 0 1 .4 1 1v1H3V5c0-.6.5-1 1-1h3c.2-1.1 1.3-2 2.5-2s2.3.9 2.5 2zM8 4h3c-.2-.6-.9-1-1.5-1S8.2 3.4 8 4zM4 7h11l-.9 10.1c0 .5-.5.9-1 .9H5.9c-.5 0-.9-.4-1-.9L4 7z"></path>
												</svg>
											</mdl-button>
										</div>
									</address-box>
								</column>
							</columns>
						</div>
					</div>
				</column>
			</columns>
			<template slot="foot">
				<mdl-button @click="showDirectionModal = true">Get Direction</mdl-button>
				<mdl-button v-if="dataChanged" type="raised" color="primary" @click="updateData">Update Data
				</mdl-button>
				<mdl-button @click="close">Close</mdl-button>
			</template>
		</modal>
		<modal :active="showIntervalModal" @close="closeIntervalModal" title="Interval Hours" content-size="small">
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
		<modal :active="showDirectionModal" title="Get Direction" @close="closeDirectionModal">
			<table class="mdl-data-table" v-if="place.places">
				<thead>
				<tr>
					<td class="mdl-data-table__cell--non-numeric">Start Place</td>
					<td class="mdl-data-table__cell--non-numeric">Destination</td>
					<td>&nbsp;</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="mdl-data-table__cell--non-numeric">
						<strong>Base Address</strong><br>
						{{place.formatted_base_address}}
					</td>
					<td class="mdl-data-table__cell--non-numeric">
						<strong>{{place.places[0].name}}</strong><br>
						{{place.places[0].formatted_address}}
					</td>
					<td>
						<mdl-button
							@click="confirmDirection(place.formatted_base_address,place.places[0].formatted_address)">
							Start
						</mdl-button>
					</td>
				</tr>
				<template v-for="(_place, index) in place.places" v-if="index > 0">
					<tr>
						<td class="mdl-data-table__cell--non-numeric">
							<strong>{{place.places[index-1].name}}</strong><br>
							{{place.places[index-1].formatted_address}}
						</td>
						<td class="mdl-data-table__cell--non-numeric">
							<strong>{{_place.name}}</strong><br>
							{{_place.formatted_address}}
						</td>
						<td>
							<mdl-button
								@click="confirmDirection(place.places[index-1].formatted_address,_place.formatted_address)">
								Start
							</mdl-button>
						</td>
					</tr>
				</template>
				</tbody>
			</table>
			<template slot="foot">
				<mdl-button @click="closeDirectionModal">Close</mdl-button>
			</template>
		</modal>
	</div>
</template>

<script>
	import modal from 'shapla-modal';
	import {columns, column} from 'shapla-columns';
	import {MapMixin} from "./MapMixin";
	import AddressBox from "../../../components/AddressBox";
	import GMapAutocomplete from "../../components/gMapAutocomplete";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import SearchBox from "../../../components/SearchBox";
	import MdlSlider from "../../../material-design-lite/slider/mdlSlider";
	import Icon from "../../../shapla/icon/icon";

	let mapStyles = require('./map-style.json');

	export default {
		name: "MapModal",
		mixins: [MapMixin],
		components: {
			Icon,
			MdlSlider,
			SearchBox,
			MdlButton,
			GMapAutocomplete,
			AddressBox,
			modal,
			columns,
			column
		},

		props: {
			mode: {type: String, default: 'view'},
			active: {type: Boolean, default: false},
			place: {type: Object, required: true}
		},

		data() {
			return {
				showIntervalModal: false,
				showDirectionModal: false,
				dataChanged: false,
				activePlace: {},
				intervalHours: '',
				intervalMinutes: '',
				location: '',
				googleMap: '',
				placesService: '',
				directionsService: '',
				directionsRenderer: '',
				radius: 100,
				pagination: null,
				hasNextPage: false,
				places: [],
				markers: [],
			}
		},

		watch: {
			place(newValue) {
				if (!!Object.keys(newValue).length) {
					// Clear current route
					this.directionsRenderer.setDirections({routes: []});
					this.getDirectionRoutes(this.directionsService, newValue)
						.then(response => {
							if (response.routes && response.routes[0].legs) {
								// this.addLegOnSelectedPlaces(addresses, response.routes[0].legs);
							}
							this.directionsRenderer.setDirections(response);
						});
				}
			}
		},

		computed: {
			has_place() {
				return !!Object.keys(this.place);
			},
			title() {
				return `Map: ${this.place.title}`;
			},
			alphabets() {
				return String.fromCharCode(..." ".repeat(26).split("").map((e, i) => i + 'A'.charCodeAt())).split('');
			},
			radius_meters() {
				return this.radius * 100;
			}
		},
		mounted() {
			// Create the map.
			this.location = new google.maps.LatLng(0, 0);
			this.googleMap = new google.maps.Map(this.$el.querySelector('#modal-map'), {
				center: this.location,
				zoom: 17,
				styles: mapStyles
			});
			// Create the places service.
			this.placesService = new google.maps.places.PlacesService(this.googleMap);
			// Create the direction service
			this.directionsService = new google.maps.DirectionsService;
			this.directionsRenderer = new google.maps.DirectionsRenderer({
				map: this.googleMap,
			});
		},
		methods: {
			removePlace(place) {
				if (confirm('Are you sure?')) {
					let index = this.place.places.findIndex(el => el.place_id === place.place_id);
					this.$delete(this.place.places, index);
					this.getDirectionRoutes(this.directionsService, this.place)
						.then(response => {
							if (response.routes && response.routes[0].legs) {
								this.addLegOnSelectedPlaces(this.place, response.routes[0].legs);
							}
							this.directionsRenderer.setDirections(response);
						});
					this.dataChanged = true;
				}
			},
			loadMore() {
				if (this.hasNextPage) {
					this.pagination.nextPage();
				}
			},
			updatePlaceData() {
				if (this.place.place_text.length < 3) {
					this.$root.$emit('show-notification', {
						title: 'Error!',
						message: 'Please enter at least three characters.',
						type: 'error',
					});
					return;
				}
				if (!this.place.base_address_latitude || !this.place.base_address_longitude) {
					this.$root.$emit('show-notification', {
						title: 'Error!',
						message: 'Please set base address first.',
						type: 'error',
					});
					return;
				}

				let query = {
					location: {
						lat: this.place.base_address_latitude,
						lng: this.place.base_address_longitude
					},
					radius: this.radius_meters,
					query: this.place.place_text
				};
				this.$store.commit('SET_LOADING_STATUS', true);

				this.textSearch(this.placesService, query)
					.then(response => {
						this.pagination = response.pagination;
						this.hasNextPage = this.pagination.hasNextPage;

						let location = new google.maps.LatLng(
							this.place.base_address_latitude,
							this.place.base_address_longitude
						);
						this.createMarkers(this.googleMap, location, response.results, this.places, this.markers);
						this.$store.commit('SET_LOADING_STATUS', false);
					})
					.catch(error => {
						this.$store.commit('SET_LOADING_STATUS', false);
						console.log(error);
					});
			},
			clearPlaceData() {
				this.places = [];
				this.hasNextPage = false;
				this.clearMarkers(this.markers);
			},
			selectPlace(place) {
				let _place = place, addresses = this.place.places;
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
				this.place.places = addresses;

				this.directionsRenderer.setDirections({routes: []});
				this.getDirectionRoutes(this.directionsService, this.place)
					.then(response => {
						if (response.routes && response.routes[0].legs) {
							this.addLegOnSelectedPlaces(this.place, response.routes[0].legs);
						}
						this.directionsRenderer.setDirections(response);
					});
				this.dataChanged = true;
			},
			openIntervalModal(place) {
				this.showIntervalModal = true;
				this.activePlace = place;
				if (place.interval_hour) {
					this.intervalHours = place.interval_hour;
				}
				if (place.interval_minute) {
					this.intervalMinutes = place.interval_minute;
				}
			},
			closeIntervalModal() {
				this.showIntervalModal = false;
				this.activePlace = {};
				this.intervalHours = '';
				this.intervalMinutes = '';
				setTimeout(() => {
					document.querySelector('body').classList.add('has-shapla-modal');
				}, 10);
			},
			closeDirectionModal() {
				this.showDirectionModal = false;
				setTimeout(() => {
					document.querySelector('body').classList.add('has-shapla-modal');
				}, 10);
			},
			confirmDirection(start, end) {
				let url = `http://maps.google.com/maps?saddr=${encodeURI(start)}&daddr=${encodeURI(end)}`;
				window.open(url);
			},
			confirmInterval() {
				let place = this.activePlace, addresses = this.place.places;
				let index = addresses.findIndex(el => el.place_id === place.place_id);
				place['interval_hour'] = this.intervalHours.length ? parseInt(this.intervalHours) : 0;
				place['interval_minute'] = this.intervalMinutes.length ? parseInt(this.intervalMinutes) : 0;

				let interval_seconds = (place['interval_hour'] * 60 * 60 * 1000) + (place['interval_minute'] * 60 * 1000);
				if (place.reach_time) {
					place['leave_time'] = (place.reach_time + interval_seconds);
				}

				addresses[index] = place;
				this.dataChanged = true;
				this.reCalculateArrivalAndDepartureTime(addresses);
				this.closeIntervalModal();
			},
			close() {
				this.$emit('close');
			},
			updateData() {
				this.$store.dispatch('updateMapRecord', this.place)
					.then(() => {
						this.dataChanged = false;
						this.$store.dispatch('refreshMapList');
						this.close();
						this.$root.$emit('show-notification', {
							title: 'Success!',
							message: 'Data has been updated successfully.',
							type: 'success',
						});
					})
					.catch(error => {
						console.log(error);
					});
			}
		}
	}
</script>

<style lang="scss">
	.places-box__action-left {
		position: absolute;
		left: 0;
		border: 0;
		bottom: 0;

		svg {
			fill: currentColor;
			width: 20px;
			height: 20px;
		}
	}
</style>
