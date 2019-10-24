<template>
	<div class="stackonet-dashboard-tracker">
		<div class="stackonet-dashboard-tracker__nav">
			<side-nav :active="sideNavActive" @close="sideNavActive = false" :show-header="false" nav-width="400px">
				<template slot="header">
					<div class="shapla-sidenav__header">
						<mdl-button type="raised" color="primary" @click="goBack">Go Back</mdl-button>
						<div class="card__actions-log" style="position: relative;">
							<span class="card__actions-log-text">{{logDateDisplay}}</span>
							<a class="input-button" title="toggle" data-toggle style="width: 24px;height:24px">
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24"
									 height="24"
									 viewBox="0 0 32 32">
									<title>clock</title>
									<path
										d="M16 32c8.822 0 16-7.178 16-16s-7.178-16-16-16-16 7.178-16 16 7.178 16 16 16zM16 1c8.271 0 15 6.729 15 15s-6.729 15-15 15-15-6.729-15-15 6.729-15 15-15zM20.061 21.768c0.098 0.098 0.226 0.146 0.354 0.146s0.256-0.049 0.354-0.146c0.195-0.195 0.195-0.512 0-0.707l-4.769-4.768v-6.974c0-0.276-0.224-0.5-0.5-0.5s-0.5 0.224-0.5 0.5v7.181c0 0.133 0.053 0.26 0.146 0.354l4.915 4.914zM3 16c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM27 16c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM15 4c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM15 28c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM7 8c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM23 24c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM24 8c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1zM7 24c0 0.552 0.448 1 1 1s1-0.448 1-1c0-0.552-0.448-1-1-1s-1 0.448-1 1z"></path>
								</svg>
							</a>
							<flat-pickr
								style="visibility: hidden;width: 1px;height: 1px;position: absolute;top: 0;left: 0;"
								v-model="log_date"
								:config="flatpickrConfig"
								placeholder="Select date"/>
						</div>
						<mdl-button type="icon" color="accent" @click="sideNavActive = !sideNavActive"
									title="Hide side navigation">
							<i class="fa fa-angle-left" aria-hidden="true"></i>
						</mdl-button>
					</div>
				</template>
				<div class="button-toggle-sidenav" v-if="!sideNavActive" @click="sideNavActive = !sideNavActive">
					<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24">
						<path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
						<path fill="none" d="M0 0h24v24H0V0z"/>
					</svg>
				</div>
				<div class="timeline-container">
					<spinner :active="timelineLoading" position="absolute"></spinner>
					<div class="timeline" v-if="timelineItems.length > 0" v-for="(item, index) in timelineItems">
						<google-timeline-item
							v-if="item.type === 'place'"
							:item="item"
							:first-item="index === 0"
							:last-item="index === (timelineItems.length - 1)"
							@change="updateTimelineItem"
							@mouseenter="handleTimelineItemMouseEnter"
							@addAddress="addAddress"
							@deleteAddress="deleteAddress"
						></google-timeline-item>
						<google-timeline-movement
							v-if="item.type === 'movement'"
							:activity-icon="item.icon"
							:activity-type="item.activityType"
							:activity-distance-text="item.activityDistanceText"
							:activity-duration-text="item.activityDurationText"
						></google-timeline-movement>
					</div>
					<div v-if="timelineItems.length < 1" class="not-found">No timeline data found.</div>
				</div>
			</side-nav>
		</div>
		<div id="google-map"></div>
	</div>
</template>

<script>
    import axios from 'axios';
    import spinner from 'shapla-spinner';
    import {TrackerMixin} from "./TrackerMixin";
    import {MapMixin} from "../dashboard/pages/MapMixin";
    import sideNav from "../../shapla/shapla-side-navigation/sideNavigation";
    import MdlButton from "../../material-design-lite/button/mdlButton";
    import FlatPickr from "vue-flatpickr-component/src/component";
    import GoogleTimelineItem from "./googleTimelineItem";
    import GoogleTimelineMovement from "./googleTimelineMovement";

    export default {
        name: "TrackableObjectTimeline",
        mixins: [TrackerMixin, MapMixin],
        components: {spinner, GoogleTimelineMovement, GoogleTimelineItem, MdlButton, sideNav, FlatPickr},
        data() {
            return {
                timelineLoading: false,
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
                timelines: null,
                polylines: [],
                mapPolyline: [],
                timelineItems: [],
                activeTimelineItem: {},
            }
        },
        watch: {
            log_date(newValue) {
                this.getObject(this.$route.params.object_id, newValue, this.useSnapToRoads).then(data => {
                    data.is_changed = true;
                    this.refreshData(data);
                    let location = new google.maps.LatLng(data.object.latitude, data.object.longitude);
                    this.googleMap.setCenter(location);
                }).catch(error => console.error(error));

                this.getTimeline();
            }
        },
        beforeDestroy() {
            clearInterval(this.employees);
            clearInterval(this.timelines);
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_TITLE', 'Activity');

            this.getTimeline();

            this.googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
                center: new google.maps.LatLng(0, 0),
                zoom: 17,
            });
            // Create the places service.
            this.placesService = new google.maps.places.PlacesService(this.googleMap);

            google.maps.event.addListener(this.googleMap, 'click', (event) => {
                this.placesService.getDetails({placeId: event.placeId}, (place, status) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK) {
                        if (Object.keys(this.activeTimelineItem).length) {
                            this.$modal.confirm({
                                message: `Do you want to add this address to list?<br><br><strong>${place.name}</strong><br>${place.formatted_address}`,
                                confirmButton: 'Yes',
                                cancelButton: 'No'
                            }).then(confirmed => {
                                if (confirmed) {
                                    let _place = {
                                        formatted_address: place.formatted_address,
                                        icon: place.icon,
                                        name: place.name,
                                        place_id: place.place_id,
                                    };
                                    this.updateTimelineItem(
                                        this.activeTimelineItem,
                                        _place,
                                        this.activeTimelineItem.address,
                                        true
                                    );
                                    this.activeTimelineItem = {};
                                }
                            });
                        } else {
                            alert('Choose timeline item first.');
                        }
                    }
                });
            });

            this.getTrackableObjects().then(response => {
                this.trackable_objects = response.items;
            }).catch(error => console.error(error));

            this.getObject(this.$route.params.object_id, this.log_date, this.useSnapToRoads).then(data => {
                this.refreshData(data);
                this.addMarker(data);
                let location = new google.maps.LatLng(data.object.latitude, data.object.longitude);
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

            this.timelines = setInterval(() => {
                this.getTimeline();
            }, 60000);
        },
        computed: {
            flatpickrConfig() {
                return {
                    dateFormat: 'Y-m-d',
                    enableTime: false,
                    wrap: true,
                    minDate: new Date(this.min_max_date.startDate),
                    maxDate: new Date(this.min_max_date.endDate),
                    defaultDate: new Date()
                };
            },
            logDateDisplay() {
                if (this.log_date.length < 1) return 'Today';

                let today = new Date(), choice = new Date(this.log_date);
                if (today.getMonth() === choice.getMonth() && today.getFullYear() === choice.getFullYear()) {
                    if (today.getDate() === choice.getDate()) {
                        return 'Today';
                    }
                    if ((today.getDate() - 1) === choice.getDate()) {
                        return 'Yesterday';
                    }
                }
                return this.formatDate(this.log_date);
            }
        },
        methods: {
            getTimeline() {
                this.timelineLoading = true;
                axios.get(PhoneRepairs.rest_root + '/trackable-objects/logs', {
                    params: {
                        object_id: this.$route.params.object_id,
                        log_date: this.log_date,
                        timeline: true,
                        polyline: false,
                        snap_to_roads: false,
                    }
                }).then(response => {
                    this.timelineItems = response.data.data.timeline;
                    this.timelineLoading = false;
                }).catch(error => {
                    this.timelineLoading = false;
                    console.log(error);
                })
            },
            refreshData(data) {
                if (!(data.is_changed || this.polylines.length < 1)) {
                    return;
                }
                this.current_timestamp = data.utc_timestamp;
                this.idle_time = data.idle_time;
                this.object = data.object;
                this.snappedPoints = data.snappedPoints;
                this.min_max_date = data.min_max_date;
                this.polylines = data.polyline;
                let location = new google.maps.LatLng(this.object.latitude, this.object.longitude);
                if (Object.keys(this.marker).length) {
                    this.marker.setPosition(location);
                }

                if (this.useSnapToRoads) {
                    this.update_polyline(this.snappedPoints);
                } else {
                    this.update_polyline(this.polylines);
                }
            },
            updateTimelineItem(item, newPlace, oldPlace, mapPlace = false) {
                let data = {
                    object_id: this.$route.params.object_id,
                    log_date: this.log_date,
                    timeline: true,
                    polyline: false,
                    snap_to_roads: false,
                    latitude: item.latitude,
                    longitude: item.longitude,
                    utc_timestamp: item.utc_timestamp,
                    new_place: newPlace,
                    old_place: oldPlace,
                    picked_from_map: mapPlace,
                };
                axios.post(PhoneRepairs.rest_root + '/trackable-objects/logs', data).then(response => {
                    console.log(response.data.data);
                    this.timelineItems = response.data.data;
                }).catch(error => {
                    console.log(error);
                });
            },
            handleTimelineItemMouseEnter(item) {
                let location = new google.maps.LatLng(item.latitude, item.longitude);
                this.googleMap.setCenter(location);
            },
            addAddress(item) {
                this.activeTimelineItem = item;
                this.googleMap.setZoom(20);
            },
            deleteAddress(item) {
                alert('We are working on it.');
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
                    position: new google.maps.LatLng(data.object.latitude, data.object.longitude)
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
		.timeline-container {
			position: relative;
		}

		.not-found {
			display: block;
			text-align: center;
			margin-top: 2rem;
			margin-bottom: 2rem;
		}

		&__nav {
			position: relative;

			.shapla-sidenav {
				overflow: visible;
				height: calc(100vh - 64px);

				.admin-bar & {
					height: calc(100vh - 96px);
				}
			}

			.shapla-sidenav__header {
				padding: 1rem;
				border-bottom: 1px solid #f5f5f5;
				display: flex;
				justify-content: space-between;
				align-items: center;
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
