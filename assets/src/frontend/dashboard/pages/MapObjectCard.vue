<template>
	<div class="card light">

		<div class="card__header" :style="{backgroundColor: headerBackground}">
			<div class="card__logo">
				<template v-if="logoUrl.length">
					<image-container>
						<img class="card__image is-rounded" :src="logoUrl" alt="">
					</image-container>
				</template>
				<template v-else>
					<svg width="64px" height="64px" viewBox="0 0 64 64">
						<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<circle fill="#FFFFFF" cx="32" cy="32" r="32"></circle>
							<path
								d="M20.7894737,31.0526316 L43.5263158,31.0526316 L43.5263158,21.5789474 L20.7894737,21.5789474 L20.7894737,31.0526316 Z M40.6842105,42.4210526 C39.1115789,42.4210526 37.8421053,41.1515789 37.8421053,39.5789474 C37.8421053,38.0063158 39.1115789,36.7368421 40.6842105,36.7368421 C42.2568421,36.7368421 43.5263158,38.0063158 43.5263158,39.5789474 C43.5263158,41.1515789 42.2568421,42.4210526 40.6842105,42.4210526 L40.6842105,42.4210526 Z M23.6315789,42.4210526 C22.0589474,42.4210526 20.7894737,41.1515789 20.7894737,39.5789474 C20.7894737,38.0063158 22.0589474,36.7368421 23.6315789,36.7368421 C25.2042105,36.7368421 26.4736842,38.0063158 26.4736842,39.5789474 C26.4736842,41.1515789 25.2042105,42.4210526 23.6315789,42.4210526 L23.6315789,42.4210526 Z M17,40.5263158 C17,42.2025263 17.7389474,43.6905263 18.8947368,44.7326316 L18.8947368,48.1052632 C18.8947368,49.1473684 19.7473684,50 20.7894737,50 L22.6842105,50 C23.7364211,50 24.5789474,49.1473684 24.5789474,48.1052632 L24.5789474,46.2105263 L39.7368421,46.2105263 L39.7368421,48.1052632 C39.7368421,49.1473684 40.5793684,50 41.6315789,50 L43.5263158,50 C44.5684211,50 45.4210526,49.1473684 45.4210526,48.1052632 L45.4210526,44.7326316 C46.5768421,43.6905263 47.3157895,42.2025263 47.3157895,40.5263158 L47.3157895,21.5789474 C47.3157895,14.9473684 40.5326316,14 32.1578947,14 C23.7831579,14 17,14.9473684 17,21.5789474 L17,40.5263158 Z"
								fill="#FF4081"></path>
						</g>
					</svg>
				</template>
			</div>
			<div>
				<div class="card__title" v-html="name"></div>
				<div class="card__current-location" v-if="formatted_address.length" v-html="formatted_address"></div>
			</div>
		</div>

		<div class="card__status">
			<span class="card__online-status" :class="{'is-online':online,'is-offline':!online}">{{status_text}}</span>
			<span class="card__activity-status" v-if="online" :class="{'is-moving':isMoving,'is-idle':!isMoving}">{{activity_status_text}}</span>
		</div>

		<slot>
			<div class="card__actions" v-if="showAction">
				<mdl-button type="raised" @click="viewTimeline">View Timeline</mdl-button>
			</div>
		</slot>
	</div>
</template>

<script>
    import ImageContainer from "../../../shapla/image/image";
    import MdlButton from "../../../material-design-lite/button/mdlButton";

    export default {
        name: "MapObjectCard",
        components: {MdlButton, ImageContainer},
        props: {
            object_id: {type: String, default: ''},
            lat_lng: {
                type: Object, default: () => {
                }
            },
            online: {type: Boolean, default: false},
            showAction: {type: Boolean, default: true},
            logoUrl: {type: String, default: ''},
            name: {type: String, default: ''},
            headerBackground: {type: String, default: '#f7fafc'},
            headerText: {type: String, default: '#000'},
            currentTime: {type: Number, default: 0},
            lastActiveTime: {type: Number, default: 0},
            idleTime: {type: Number, default: 0},
        },
        data() {
            return {
                formatted_address: '',
            }
        },
        computed: {
            isMoving() {
                return ((this.lastActiveTime + this.idleTime) * 1000) >= (this.currentTime * 1000);
            },
            activity_status_text() {
                return this.isMoving ? 'Moving' : 'Idle';
            },
            status_text() {
                return this.online ? 'Online' : 'Off Line';
            },
        },
        watch: {
            lat_lng(newValue) {
                this.get_address(newValue);
            }
        },
        mounted() {
            if (this.lat_lng) {
                this.get_address(this.lat_lng);
            }
        },
        methods: {
            get_address(lat_lng) {
                this.geoCodeToAddress(lat_lng.lat, lat_lng.lng);
            },
            geoCodeToAddress(latitude, longitude) {
                let geocoder = new google.maps.Geocoder;
                geocoder.geocode({'location': {lat: latitude, lng: longitude}}, (results, status) => {
                    if (status === 'OK') {
                        if (results[0]) {
                            this.formatted_address = results[0].formatted_address;
                        }
                    }
                });
            },
            viewTimeline() {
                this.$router.push({
                    name: 'SingleObjectTracker',
                    params: {object_id: this.object_id}
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
	.card {
		background-color: #ffffff;
		border-radius: 8px;
		box-shadow: 0 5px 10px #888888;
		margin: 4rem 0;
		position: relative;
		min-width: 350px;
		max-width: 350px;

		&__header {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			padding: 1rem;
		}

		&__logo {
			margin-right: 1rem;
		}

		&__image {
			max-width: 64px;
			height: auto;
		}

		&__title {
			color: rgba(#000, 0.85);
			font-size: 2rem;
			font-weight: 500;
		}

		&__current-location {
			color: rgba(#000, 0.75);
			font-size: 1rem;
		}

		&__status {
			// position: relative;
		}

		&__online-status,
		&__activity-status {
			background-color: #f1f1f1;
			border-radius: 4px;
			box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
			color: #fff;
			display: flex;
			padding: 5px;
			line-height: 1;
			margin: 0;
			position: absolute;
			text-align: center;
			justify-content: center;
			height: 1.8em;
			width: 70px;
		}

		&__online-status {
			top: -.9em;
			right: -35px;

			&.is-offline {
				background-color: #000;
			}

			&.is-online {
				background-color: #f44336;
			}
		}

		&__activity-status {
			bottom: -.9em;
			right: -35px;

			&.is-idle {
				background-color: #f9a73b;
			}

			&.is-moving {
				background-color: #43a047;
			}
		}

		&__actions {
			padding: 1rem;
		}
	}
</style>
