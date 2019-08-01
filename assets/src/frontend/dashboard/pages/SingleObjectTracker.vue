<template>
	<div class="stackonet-dashboard-tracker">
		<div id="google-map"></div>
		<div class="stackonet-dashboard-tracker__vans" v-if="Object.keys(object).length">
			<map-object-card
				:lat_lng="{lat:object.last_log.latitude, lng:object.last_log.longitude}"
				:logo-url="object.icon"
				:object_id="object.object_id"
				:name="object.object_name"
				:online="object.last_log.online"
				:last-active-time="object.last_log.utc_timestamp"
				:idle-time="idle_time"
				:show-action="false"
			/>
		</div>
	</div>
</template>

<script>
    import axios from 'axios';
    import MapObjectCard from "./MapObjectCard";
    import {TrackerMixin} from "./TrackerMixin";

    export default {
        name: "SingleObjectTracker",
        mixins: [TrackerMixin],
        components: {MapObjectCard},
        data() {
            return {
                googleMap: {},
                object: {},
                current_timestamp: 0,
                idle_time: 0,
            }
        },
        computed: {
            coordinates() {
                let pathValues = this.object.logs.map(log => {
                    return `${log.latitude},${log.longitude}`
                });

                return pathValues.join('|');
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_TITLE', 'Tracker');
            this.getObject(this.$route.params.object_id)
                .then(data => {
                    this.current_timestamp = data.utc_timestamp;
                    this.idle_time = data.idle_time;
                    this.object = data.object;
                    this.$store.commit('SET_TITLE', `Activity: ${this.object.object_name}`);
                    let location = new google.maps.LatLng(this.object.last_log.latitude, this.object.last_log.longitude);
                    this.googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
                        center: location,
                        zoom: 17,
                    });
                    let _marker = new google.maps.Marker({
                        map: this.googleMap,
                        icon: {
                            url: this.object.icon,
                            size: new google.maps.Size(48, 48),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(0, 32),
                            scaledSize: new google.maps.Size(25, 25)
                        },
                        title: this.object.object_name,
                        position: location
                    });
                    this.runSnapToRoad(this.coordinates).then(data => console.log(data));

                    let logs = this.get_coordinates(this.object.logs);
                    var flightPath = new google.maps.Polyline({
                        map: this.googleMap,
                        path: logs,
                        geodesic: true,
                        strokeColor: '#f78739',
                        strokeOpacity: 1.0,
                        strokeWeight: 3
                    });
                })
                .catch(error => console.error(error));
        },
        methods: {
            getObject(object_id) {
                return new Promise((resolve, reject) => {
                    axios.get(PhoneRepairs.rest_root + '/trackable-objects/log', {params: {object_id}})
                        .then(response => {
                            resolve(response.data.data);
                        }).catch(error => reject(error))
                })
            },
            get_coordinates(logs) {
                return logs.map(log => {
                    return {
                        lat: log.latitude,
                        lng: log.longitude
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>
