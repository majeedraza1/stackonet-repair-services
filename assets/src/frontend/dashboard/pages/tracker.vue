<template>
	<div class="stackonet-dashboard-tracker">
		<div id="google-map"></div>
		<div class="stackonet-dashboard-tracker__vans">
			<template v-for="_item in card_items">
				<map-object-card
					:lat_lng="_item.lat_lng"
					:logo-url="_item.icon"
					:name="_item.name"
					:online="_item.online"
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

    let mapStyles = require('./map-style.json');

    export default {
        name: "tracker",
        components: {MapObjectCard, columns, column},
        mixins: [CrudMixin],
        data() {
            return {
                employees: [],
                logs: [],
                current_timestamp: '',
                latitude: 0,
                longitude: 0,
                location: '',
                googleMap: '',
                markers: [],
            }
        },
        computed: {
            card_items() {
                let card_items = [], items = this.items, employees = this.employees;
                for (let i = 0; i < items.length; i++) {
                    let _data = {
                        object_id: items[i]['object_id'],
                        icon: items[i]['icon'],
                        name: items[i]['object_name'],
                    };
                    for (let j = 0; j < employees.length; j++) {
                        if (employees[j]['Employee_ID'] === items[i]['object_id']) {
                            _data['lat_lng'] = {lat: employees[j]['latitude'], lng: employees[j]['longitude']};
                            _data['online'] = -1 !== ['true', true, 1].indexOf(employees[j]['online']);
                        }
                    }
                    card_items.push(_data);
                }
                return card_items;
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_TITLE', 'Tracker');

            this.get_items(PhoneRepairs.rest_root + '/trackable-objects').catch(error => console.error(error));

            if (_stackontDashboard.current_timestamp) {
                this.current_timestamp = parseInt(_stackontDashboard.current_timestamp);
            }

            // Create the map.
            this.location = new google.maps.LatLng(this.latitude, this.longitude);
            this.googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
                center: this.location,
                zoom: 17,
                // styles: mapStyles
            });

            const db = firebase.database();

            db.ref('Employees').on('value', snapshot => {
                this.employees = Object.values(snapshot.val());
                this.logToDatabase(this.employees);
                let markers = this.calculateMarkers(this.items, this.employees);
                this.clearMarkers();
                this.updateMapMarkers(markers);
                this.getLogs();
            });
        },
        methods: {
            calculateMarkers(objects, activeObjects) {
                if (objects.length < 1) return [];
                let markers = [];
                for (let i = 0; i < objects.length; i++) {
                    for (let j = 0; j < activeObjects.length; j++) {
                        if (objects[i].object_id === activeObjects[j].Employee_ID && activeObjects[j].online === "true") {
                            markers.push({
                                icon: objects[i].icon,
                                name: objects[i].object_name,
                                lat: activeObjects[j].latitude,
                                lng: activeObjects[j].longitude,
                            });
                        }
                    }
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
            },
            getLogs() {
                axios.get(PhoneRepairs.rest_root + '/trackable-objects/log')
                    .then(response => {
                        this.logs = response.data.data.items;
                    });
            },
            logToDatabase(employees) {
                axios.post(PhoneRepairs.rest_root + '/trackable-objects/log', {objects: employees})
                    .catch(error => console.log(error));
            }
        }
    }
</script>

<style lang="scss" scoped>
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
		}
	}
</style>
