<template>
	<div class="stackonet-dashboard-tracker">
		<div id="google-map"></div>
		<div class="stackonet-dashboard-tracker__vans">
			<template v-for="_item in card_items">
				<map-object-card
					@click="handleClick"
					:lat_lng="_item.lat_lng"
					:logo-url="_item.icon"
					:object_id="_item.object_id"
					:name="_item.name"
					:online="_item.online"
					:last-active-time="_item.last_activity"
					:moving="_item.moving"
					:current-time="current_timestamp"
					:idle-time="idle_time"
				/>
			</template>
		</div>
	</div>
</template>

<script>
    import {columns, column} from 'shapla-columns';
    import axios from 'axios';
    import {CrudMixin} from "../../../components/CrudMixin";
    import MapObjectCard from "./MapObjectCard";
    import {TrackerMixin} from "./TrackerMixin";

    let mapStyles = require('./map-style.json');

    export default {
        name: "tracker",
        components: {MapObjectCard, columns, column},
        mixins: [CrudMixin, TrackerMixin],
        data() {
            return {
                current_timestamp: 0,
                idle_time: 0,
                googleMap: {},
                markers: [],
                employees: null,
            }
        },
        computed: {
            card_items() {
                return this.items.map(item => {
                    return {
                        object_id: item['object_id'],
                        icon: item['icon'],
                        name: item['object_name'],
                        online: item['last_log']['online'],
                        current_timestamp: this.current_timestamp,
                        last_activity: item['last_log']['utc_timestamp'],
                        lat_lng: {
                            lat: item['last_log']['latitude'],
                            lng: item['last_log']['longitude']
                        }
                    }
                });
            }
        },
        beforeDestroy() {
            clearInterval(this.employees)
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_TITLE', 'Tracker');

            this.getObjects();

            // Create the map.
            this.googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
                center: new google.maps.LatLng(0, 0),
                zoom: 17,
                // styles: mapStyles
            });

            // employees
            this.employees = setInterval(() => {
                this.getObjects();
            }, 5000);
        },
        methods: {
            handleClick(data) {
                let latLng = new google.maps.LatLng(data.lat_lng.lat, data.lat_lng.lng);
                this.googleMap.setCenter(latLng);
                this.googleMap.setZoom(17);
            },
            getObjects() {
                this.getTrackableObjects().then(_data => {
                    this.items = _data.items;
                    this.current_timestamp = _data.utc_timestamp;
                    this.idle_time = _data.idle_time;
                    let markers = this.calculateMarkers(this.items);
                    this.clearMarkers();
                    this.updateMapMarkers(markers);
                }).catch(error => console.error(error));
            },
            calculateMarkers(objects) {
                if (objects.length < 1) return [];
                let markers = [];
                for (let i = 0; i < objects.length; i++) {
                    markers.push({
                        icon: objects[i].icon,
                        name: objects[i].object_name,
                        lat: objects[i].last_log.latitude,
                        lng: objects[i].last_log.longitude,
                    });
                }

                return markers;
            },
            clearMarkers() {
                for (let i = 0; i < this.markers.length; i++) {
                    this.markers[i].setMap(null);
                }
                this.markers = [];
            },
            updateMapMarkers(markers) {
                let bounds = new google.maps.LatLngBounds();
                markers.forEach(marker => {
                    let _marker = new google.maps.Marker({
                        map: this.googleMap,
                        icon: {
                            url: marker.icon,
                            size: new google.maps.Size(48, 48),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(0, 32),
                            scaledSize: new google.maps.Size(25, 25)
                        },
                        title: marker.name,
                        position: {lat: marker.lat, lng: marker.lng}
                    });
                    _marker.addListener('click', () => {
                        this.toggleBounce(_marker);
                    });

                    this.markers.push(_marker);

                    bounds.extend({lat: marker.lat, lng: marker.lng});
                });
                this.googleMap.fitBounds(bounds);
            },
            toggleBounce(marker) {
                if (marker.getAnimation() !== null) {
                    marker.setAnimation(null);
                } else {
                    marker.setAnimation(google.maps.Animation.BOUNCE);
                }
            }
        }
    }
</script>

<style lang="scss">
	.stackonet-dashboard-tracker {
		margin: -30px;

		#google-map {
			height: calc(100vh - 64px);

			.admin-bar & {
				height: calc(100vh - 96px);
			}
		}

		&__vans {
			position: fixed;
			top: 0;
			left: 0;
			z-index: 9999;
			// width: 300px;
			height: 100%;
			padding: 150px 1rem 50px;
			direction: rtl;
			overflow-y: auto;

			> * {
				direction: ltr;
			}
		}
	}
</style>
