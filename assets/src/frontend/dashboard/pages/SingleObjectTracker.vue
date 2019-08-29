<template>
	<div class="stackonet-dashboard-tracker">
		<div id="google-map"></div>
		<div class="stackonet-dashboard-tracker__vans" v-if="Object.keys(object).length">
			<map-object-card
				:lat_lng="{lat:object.last_log.latitude, lng:object.last_log.longitude}"
				:logo-url="object.icon"
				:object_id="object.object_id"
				:name="object.object_name"
				:online="object.online"
				:last-active-time="object.last_log.utc_timestamp"
				:moving="object.moving"
				:idle-time="idle_time"
				:show-action="false"
			>
				<div class="card__actions" style="display: flex;">
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
					<div>
						<mdl-button type="raised" color="primary" v-if="useSnapToRoads" @click="lineType('actual')">
							Actual
						</mdl-button>
						<mdl-button type="raised" color="primary" v-if="!useSnapToRoads" @click="lineType('optimised')">
							Optimised
						</mdl-button>
					</div>
				</div>
				<div v-if="polylines.length">
					<template v-for="_line in polylines" v-if="_line.logs">
						<div class="polyline-item">
							<div class="polyline-item__color" :style="{background:_line.colorCode}"></div>
							<div class="polyline-item__title">{{_line.title}}</div>
						</div>
					</template>
				</div>
			</map-object-card>
		</div>
	</div>
</template>

<script>
    import FlatPickr from "vue-flatpickr-component/src/component";
    import MapObjectCard from "./MapObjectCard";
    import {TrackerMixin} from "./TrackerMixin";
    import MdlButton from "../../../material-design-lite/button/mdlButton";

    export default {
        name: "SingleObjectTracker",
        mixins: [TrackerMixin],
        components: {MdlButton, MapObjectCard, FlatPickr},
        data() {
            return {
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
        beforeDestroy() {
            clearInterval(this.employees)
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_TITLE', 'Tracker');

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
	.button--go-back {
		height: 40px;
		left: 205px;
		position: absolute;
		top: 10px;
	}

	.card__actions-log {
		align-items: center;
		display: flex;
		height: 36px;
		justify-content: center;
		padding: 0 1rem;
		margin: 0 1rem;
		position: relative;

		a, & {
			background: #f58730;
			color: #ffffff;
		}

		&-text {
			margin-right: .5rem;
		}

		svg {
			fill: currentColor;
		}
	}

	.polyline-item {
		display: flex;
		padding: 1rem;
		justify-content: flex-start;
		align-items: center;

		&__color {
			width: 16px;
			height: 16px;
			display: inline-flex;
			border-radius: 8px;
			margin-right: 1rem;
		}

		&__title {
		}
	}
</style>
