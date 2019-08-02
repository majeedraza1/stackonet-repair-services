<template>
	<div class="stackonet-dashboard-tracker">
		<div id="google-map"></div>
		<div class="button--go-back">

		</div>
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
			>
				<div class="card__actions">
					<mdl-button type="raised" color="primary" @click="goBack">Go Back</mdl-button>
				</div>
			</map-object-card>
		</div>
	</div>
</template>

<script>
    import axios from 'axios';
    import MapObjectCard from "./MapObjectCard";
    import {TrackerMixin} from "./TrackerMixin";
    import MdlButton from "../../../material-design-lite/button/mdlButton";

    export default {
        name: "SingleObjectTracker",
        mixins: [TrackerMixin],
        components: {MdlButton, MapObjectCard},
        data() {
            return {
                googleMap: {},
                object: {},
                marker: {},
                current_timestamp: 0,
                idle_time: 0,
                snappedPoints: [],
                snappedPolyline: {},
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
            this.getObject(this.$route.params.object_id).then(data => {
                this.current_timestamp = data.utc_timestamp;
                this.idle_time = data.idle_time;
                this.object = data.object;
                this.snappedPoints = data.snappedPoints;
                this.$store.commit('SET_TITLE', `Activity: ${this.object.object_name}`);
                let location = new google.maps.LatLng(this.object.last_log.latitude, this.object.last_log.longitude);
                this.googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
                    center: location,
                    zoom: 17,
                });

                this.marker = new google.maps.Marker({
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

                this.snappedPolyline = this.get_polyline(this.snappedPoints);
                this.snappedPolyline.setMap(this.googleMap);

            }).catch(error => console.error(error));

            const db = firebase.database();
            db.ref('Employees').on('value', snapshot => {
                let employees = Object.values(snapshot.val());
                this.logToDatabase(employees).then(() => {
                    this.getObject(this.$route.params.object_id).then(data => {
                        this.current_timestamp = data.utc_timestamp;
                        this.idle_time = data.idle_time;
                        this.object = data.object;
                        this.snappedPoints = data.snappedPoints;
                        // Clear poly lines and add new poly line
                        let location = new google.maps.LatLng(this.object.last_log.latitude, this.object.last_log.longitude);
                        this.marker.setPosition(location);
                        this.snappedPolyline.setMap(null);
                        this.snappedPolyline = this.get_polyline(this.snappedPoints);
                        this.snappedPolyline.setMap(this.googleMap);
                    }).catch(error => console.error(error));
                });
            });
        },
        methods: {
            goBack() {
                this.$router.push({name: 'tracker',})
            },
            get_coordinates(logs) {
                return logs.map(log => {
                    return {
                        lat: log.location.latitude,
                        lng: log.location.longitude
                    }
                });
            },
            get_polyline(snappedPoints) {
                return new google.maps.Polyline({
                    path: this.get_coordinates(snappedPoints),
                    geodesic: true,
                    strokeColor: '#f78739',
                    strokeOpacity: 1.0,
                    strokeWeight: 3
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
	.button--go-back {
		height: 40px;
		left: 205px;
		position: absolute;
		top: 10px;
	}
</style>
