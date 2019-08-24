<template>
	<div class="stackonet-dashboard-tracker">
		<div class="stackonet-dashboard-tracker__nav">
			<side-nav :active="sideNavActive" @close="sideNavActive = false" :show-header="false" nav-width="450px">
				<template slot="header">
					<div class="shapla-sidenav__header">
						<mdl-button type="raised" color="primary" @click="goBack">Go Back</mdl-button>
					</div>
				</template>
				<div class="button-toggle-sidenav" @click="sideNavActive = !sideNavActive">
					<template v-if="sideNavActive">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24">
							<path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/>
							<path v-if="sideNavActive" fill="none" d="M0 0h24v24H0V0z"/>
						</svg>
					</template>
					<template v-else>
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24">
							<path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
							<path fill="none" d="M0 0h24v24H0V0z"/>
						</svg>
					</template>
				</div>
				<google-timeline :items="timelineItems"></google-timeline>
			</side-nav>
		</div>
		<div id="google-map"></div>
	</div>
</template>

<script>
    import GoogleTimeline from "../../../components/googleTimeline";
    import {TrackerMixin} from "./TrackerMixin";
    import sideNav from "../../../shapla/shapla-side-navigation/sideNavigation";
    import MdlButton from "../../../material-design-lite/button/mdlButton";

    export default {
        name: "TrackableObjectTimeline",
        mixins: [TrackerMixin],
        components: {MdlButton, sideNav, GoogleTimeline},
        data() {
            return {
                sideNavActive: true,
                useSnapToRoads: false,
                googleMap: {},
                object: {},
                marker: {},
                current_timestamp: 0,
                idle_time: 0,
                snappedPoints: [],
                snappedPolyline: {},
                log_date: '',
                min_max_date: {},
                employees: null,
                polylines: [],
                mapPolyline: [],
                timelineItems: [
                    {
                        placeIcon: 'https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png',
                    }
                ],
            }
        },
        watch: {
            log_date(newValue) {
                this.getObject(this.$route.params.object_id, newValue, this.useSnapToRoads).then(data => {
                    this.refreshData(data);
                    let location = new google.maps.LatLng(data.object.last_log.latitude, data.object.last_log.longitude);
                    this.googleMap.setCenter(location);
                }).catch(error => console.error(error));
            }
        },
        beforeDestroy() {
            clearInterval(this.employees)
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_TITLE', 'Activity');

            this.googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
                center: new google.maps.LatLng(0, 0),
                zoom: 17,
            });

            this.getObject(this.$route.params.object_id, this.log_date, this.useSnapToRoads).then(data => {
                this.refreshData(data);
                this.addMarker(data);
                let location = new google.maps.LatLng(data.object.last_log.latitude, data.object.last_log.longitude);
                this.googleMap.setCenter(location);

                this.$store.commit('SET_TITLE', `Activity: ${data.object.object_name}`);
            }).catch(error => {
                console.error(error);
            });

            // employees
            this.employees = setInterval(() => {
                this.getObject(this.$route.params.object_id, this.log_date, this.useSnapToRoads).then(data => {
                    this.refreshData(data);
                }).catch(error => console.error(error));
            }, 5000);
        },
        methods: {
            refreshData(data) {
                this.current_timestamp = data.utc_timestamp;
                this.idle_time = data.idle_time;
                this.object = data.object;
                this.snappedPoints = data.snappedPoints;
                this.min_max_date = data.min_max_date;
                this.polylines = data.polyline;
                let location = new google.maps.LatLng(this.object.last_log.latitude, this.object.last_log.longitude);
                if (Object.keys(this.marker).length) {
                    this.marker.setPosition(location);
                }

                if (this.useSnapToRoads) {
                    this.update_polyline(this.snappedPoints);
                } else {
                    this.update_polyline(this.polylines);
                }
            },
            addMarker(data) {
                this.marker = new google.maps.Marker({
                    map: this.googleMap,
                    icon: {
                        url: data.object.icon,
                        size: new google.maps.Size(48, 48),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(0, 32),
                        scaledSize: new google.maps.Size(25, 25)
                    },
                    title: data.object.object_name,
                    position: new google.maps.LatLng(data.object.last_log.latitude, data.object.last_log.longitude)
                });
            },
            lineType(type) {
                this.useSnapToRoads = ('optimised' === type);
                if (this.useSnapToRoads) {
                    this.update_polyline(this.snappedPoints);
                } else {
                    this.update_polyline(this.polylines);
                }
            },
            formatDate(dateString) {
                let date = new Date(dateString);
                let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                let day = date.getDate();
                let monthIndex = date.getMonth();
                let year = date.getFullYear();

                // Jul 10, 2019
                return `${monthNames[monthIndex]} ${day}, ${year}`;
            },
            goBack() {
                this.$router.push({name: 'tracker'})
            },
            get_coordinates(snappedPoints) {
                return snappedPoints.map(log => {
                    return {lat: log.location.latitude, lng: log.location.longitude}
                });
            },
            get_coordinates_from_logs(logs) {
                return logs.map(log => {
                    return {lat: log.latitude, lng: log.longitude}
                });
            },
            get_polyline(snappedPoints, strokeColor = '#f78739') {
                let path = this.get_coordinates(snappedPoints);
                if (!this.useSnapToRoads) {
                    path = this.get_coordinates_from_logs(this.object.logs);
                }
                return new google.maps.Polyline({
                    path: path,
                    geodesic: true,
                    strokeColor: strokeColor,
                    strokeOpacity: 1.0,
                    strokeWeight: 3
                })
            },
            clear_polyline() {
                if (this.mapPolyline.length) {
                    this.mapPolyline.forEach(el => {
                        el.setMap(null);
                    });
                    this.mapPolyline = [];
                }
            },
            update_polyline(polylines) {
                this.clear_polyline();
                let totalPolyLines = polylines.length,
                    lastLog = {};

                if (totalPolyLines < 1) return;

                for (let i = 0; i < totalPolyLines; i++) {

                    if (polylines[i].logs !== undefined) {
                        let path = polylines[i].logs.map(log => {
                            return {lat: log.latitude, lng: log.longitude}
                        });

                        if (Object.keys(lastLog).length) {
                            path.unshift(lastLog);
                        }

                        lastLog = path[path.length - 1];

                        let polyline = new google.maps.Polyline({
                            path: path,
                            geodesic: true,
                            strokeColor: polylines[i].colorCode,
                            strokeOpacity: 1.0,
                            strokeWeight: 3
                        });
                        polyline.setMap(this.googleMap);
                        this.mapPolyline.push(polyline);
                    }
                }
            }
        }
    }
</script>

<style lang="scss">
	.stackonet-dashboard-tracker {
		&__nav {
			position: relative;

			.shapla-sidenav {
				overflow: visible;
			}

			.shapla-sidenav__header {
				padding: 1rem;
			}

			.button-toggle-sidenav {
				background-color: white;
				z-index: 9999999;
				width: 48px;
				height: 48px;
				position: absolute;
				top: 60px;
				right: -48px;
			}
		}
	}
</style>
