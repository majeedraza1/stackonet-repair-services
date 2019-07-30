<template>
	<div class="stackonet-dashboard-tracker">
		<columns>
			<column>
				<div id="google-map"></div>
			</column>
		</columns>
	</div>
</template>

<script>
    import {columns, column} from 'shapla-columns';
    import {CrudMixin} from "../../../components/CrudMixin";

    let mapStyles = require('./map-style.json');

    export default {
        name: "tracker",
        components: {columns, column},
        mixins: [CrudMixin],
        data() {
            return {
                employees: [],
                current_timestamp: '',
                latitude: 0,
                longitude: 0,
                location: '',
                googleMap: '',
                markers: [],
            }
        },
        computed: {},
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
                let markers = this.calculateMarkers(this.items, this.employees);
                console.log(this.employees, markers);
                this.clearMarkers();
                this.updateMapMarkers(markers);
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
            }
        }
    }
</script>

<style lang="scss" scoped>
	.stackonet-dashboard-tracker {
		#google-map {
			height: 80vh;
		}
	}
</style>
