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
			<span class="card__status-text">{{status_text}}</span>
		</div>

		<div class="card__body" style="display: none">

			<div class="stop-times">
				<div class="stop-time">21:30:00</div>
				<div class="stop-time">21:58:00</div>
				<div class="stop-time">22:28:00</div>
			</div>

			<div class="stop-guide">
				<svg width="16px" height="96px" viewBox="0 0 16 96">
					<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g id="ic_stops_two">
							<g id="top">
								<g id="bg" fill="#FF4081">
									<path
										d="M16,8 C16,3.581722 12.418278,0 8,0 C3.581722,0 0,3.581722 0,8 L0,28 L16,28 L16,8 Z"
										id="fill"></path>
								</g>
								<ellipse id="fill_white" fill="#FFFFFF" cx="8" cy="8" rx="5" ry="5"></ellipse>
							</g>
							<g id="middle" transform="translate(0.000000, 28.000000)">
								<g id="bg" fill="#FF4081">
									<path
										d="M12,20 L16,20 L16,1.77635684e-14 L0,1.77635684e-14 L0,20 L4,20 C4,17.790861 5.790861,16 8,16 C10.209139,16 12,17.790861 12,20 L16,20 L16,40 L0,40 L0,20 L4,20 C4,22.209139 5.790861,24 8,24 C10.209139,24 12,22.209139 12,20 Z"
										id="fill"></path>
								</g>
								<circle id="fill_opacity" fill="#FFFFFF" cx="8" cy="20" r="4"></circle>
							</g>
							<g id="bottom" transform="translate(0.000000, 68.000000)">
								<g id="bg"
								   transform="translate(8.000000, 14.000000) scale(1, -1) translate(-8.000000, -14.000000) "
								   fill="#FF4081">
									<path
										d="M16,8 C16,3.581722 12.418278,0 8,0 C3.581722,0 0,3.581722 0,8 L0,28 L16,28 L16,8 Z M8,13 C10.7614237,13 13,10.7614237 13,8 C13,5.23857625 10.7614237,3 8,3 C5.23857625,3 3,5.23857625 3,8 C3,10.7614237 5.23857625,13 8,13 Z"
										id="fill"></path>
								</g>
								<ellipse id="fill_opacity" fill="#FFFFFF" cx="8" cy="20" rx="5" ry="5"></ellipse>
							</g>
						</g>
					</g>
				</svg>
			</div>

			<div class="stop-names">
				<div class="stop-name">Google I/O</div>
				<div class="stop-name">Millbrae BART Station</div>
				<div class="stop-name">The Westin St. Francis SF</div>
			</div>
		</div>
	</div>
</template>

<script>
    import ImageContainer from "../../../shapla/image/image";

    export default {
        name: "MapObjectCard",
        components: {ImageContainer},
        props: {
            lat_lng: {
                type: Object, default: () => {
                }
            },
            online: {type: Boolean, default: false},
            logoUrl: {type: String, default: ''},
            name: {type: String, default: ''},
            headerBackground: {type: String, default: '#f7fafc'},
            headerText: {type: String, default: '#000'},
        },
        data() {
            return {
                formatted_address: '',
            }
        },
        computed: {
            status_text() {
                if (!this.online) return 'Off Line';
                return 'Online';
            }
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
        }
    }
</script>

<style lang="scss" scoped>
	.card {
		background-color: #ffffff;
		border-radius: 8px;
		box-shadow: 0 5px 10px #888888;
		margin: 2rem 0;
		overflow: hidden;
		position: relative;
		min-width: 350px;
		max-width: 400px;

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

		&__status-text {
			display: flex;
			background: #4CAF50;
			border-radius: 4px;
			color: rgba(#000, .85);
			padding: 5px;
			line-height: 1;
			margin: 0;
			position: absolute;
			top: 5px;
			right: 5px;
			text-align: center;
			justify-content: center;
		}

		&__body {
			position: relative;
			display: flex;
			flex-direction: row;
		}
	}

	.stop-times {
		width: 80px;
		margin-right: 16px;
		margin-top: 24px;
		margin-bottom: 24px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		align-items: flex-end;
	}

	.stop-time {
		font-size: 18px;
		color: rgba(0, 0, 0, 0.6);
	}

	.stop-guide {
		width: 16px;
		margin-top: 24px;
		margin-bottom: 24px;
	}

	.stop-names {
		margin-left: 16px;
		margin-top: 24px;
		margin-bottom: 24px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		align-items: flex-start;
	}

	.stop-name {
		font-size: 18px;
		color: rgba(0, 0, 0, 0.9);
	}

	.stop-name {
		font-size: 18px;
		color: rgba(0, 0, 0, 0.9);
	}
</style>
