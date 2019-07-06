<template>
	<div>
		<columns>
			<column :tablet="4">

				<search-box
					v-model="place_text"
					@submit="updatePlaceData"
					@clear="clearPlaceData"
				></search-box>

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
					</div>
					<div class="places-box__more" v-if="hasNextPage">
						<mdl-button type="raised" color="primary" style="width: 100%;" @click="loadMore">Load More
						</mdl-button>
					</div>
				</div>
			</column>
			<column :tablet="8">
				<div id="map"></div>
				<div class="selected-places">
					<div style="display: none;">
						<mdl-button type="raised" @click="showDateTime = !showDateTime">
							{{!showDateTime ? 'Show Time':'Hide Time'}}
						</mdl-button>
					</div>
					<draggable v-model="selectedPlaces" class="shapla-columns is-multiline">
						<column :tablet="6" v-for="(_place, index) in selectedPlaces" :key="index">
							<div class="places-box__item mdl-shadow--4dp">
								<div class="places-box__name">{{_place.name}}</div>
								<div class="places-box__formatted_address">{{_place.formatted_address}}</div>
							</div>
						</column>
					</draggable>
				</div>
			</column>
		</columns>
	</div>
</template>

<script>
	import {columns, column} from 'shapla-columns';
	import draggable from 'vuedraggable'
	import Icon from "../../../shapla/icon/icon";
	import DeleteIcon from "../../../shapla/delete/deleteIcon";
	import SearchBox from "../../../components/SearchBox";
	import MdlButton from "../../../material-design-lite/button/mdlButton";

	export default {
		name: "Map",
		components: {MdlButton, SearchBox, DeleteIcon, Icon, columns, column, draggable},
		data: () => {
			return {
				googleMap: '',
				placesService: '',
				selectedPlaces: [],
				places: [],
				markers: [],
				place_text: '',
				location: '',
				dataLoaded: false,
				pagination: null,
				hasNextPage: false,
				showDateTime: false,
			}
		},
		mounted() {
			let self = this;
			this.$store.commit('SET_LOADING_STATUS', false);

			// Create the map.
			self.location = new google.maps.LatLng(12.9374716, 77.6172011);
			self.googleMap = new google.maps.Map(this.$el.querySelector('#map'), {
				center: self.location,
				zoom: 17,
			});
			// Create the places service.
			this.placesService = new google.maps.places.PlacesService(self.googleMap);
		},
		methods: {
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
				let self = this, request = {
					location: self.location,
					radius: 500,
					query: self.place_text
				};
				self.$store.commit('SET_LOADING_STATUS', true);

				// Perform a nearby search.
				self.places = [];
				self.placesService.textSearch(
					request,
					function (results, status, pagination) {
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
	.places-box {
		background: #f1f1f1;
		border: 1px solid #f1f1f1;
		height: 70vh;
		margin-top: 1rem;
		overflow: auto;
		padding: 0 1rem;

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
