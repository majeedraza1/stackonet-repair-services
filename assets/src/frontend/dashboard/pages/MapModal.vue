<template>
	<div v-if="has_place">
		<modal :active="active" @close="close" content-size="full" :title="title">
			<columns multiline>
				<column :tablet="12">
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
									</address-box>
								</column>
							</columns>
						</div>
					</div>
				</column>
			</columns>
			<template slot="foot">
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
	</div>
</template>

<script>
	import modal from 'shapla-modal';
	import {columns, column} from 'shapla-columns';
	import {MapMixin} from "./MapMixin";
	import AddressBox from "../../../components/AddressBox";
	import GMapAutocomplete from "../../components/gMapAutocomplete";
	import MdlButton from "../../../material-design-lite/button/mdlButton";

	let mapStyles = require('./map-style.json');

	export default {
		name: "MapModal",
		mixins: [MapMixin],
		components: {
			MdlButton,
			GMapAutocomplete,
			AddressBox,
			modal,
			columns,
			column
		},

		props: {
			active: {type: Boolean, default: false},
			place: {type: Object, required: true}
		},

		data() {
			return {
				showIntervalModal: false,
				dataChanged: false,
				activePlace: {},
				intervalHours: '',
				intervalMinutes: '',
				location: '',
				googleMap: '',
				placesService: '',
				directionsService: '',
				directionsRenderer: '',
			}
		},

		watch: {
			place(newValue) {
				if (!!Object.keys(newValue).length) {
					this.updateMapRoute(
						this.directionsService,
						this.directionsRenderer,
						{lat: newValue.base_address_latitude, lng: newValue.base_address_longitude},
						newValue.places
					);
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

<style scoped>

</style>
