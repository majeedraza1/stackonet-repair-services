<template>
	<box class="new-design mdl-shadow--4dp">
		<div class="new-design__inside">
			<slot>
				<div class="new-design__content" v-if="hasPlace">
					<div class="new-design__title" v-if="place.name" v-text="place.name"></div>
					<div class="new-design__description" v-if="place.formatted_address"
						 v-html="place.formatted_address"></div>
				</div>
			</slot>
			<columns multiline mobile>
				<column :mobile="4" :tablet="4" v-if="hasPlace">
					<box class="small-box__distance">
						<div>
							<icon medium>
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
									 viewBox="0 0 32 32">
									<use xlink:href="#icon-svg-location"></use>
								</svg>
							</icon>
						</div>
						<div class="no-wrap">Distance</div>
						<div class="no-wrap" v-if="place.leg.distance" v-text="place.leg.distance.text"></div>
					</box>
				</column>
				<column :mobile="4" :tablet="4" v-if="hasPlace">
					<box class="small-box__time">
						<div>
							<icon medium>
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
									<use xlink:href="#icon-svg-clock"></use>
								</svg>
							</icon>
						</div>
						<span>Time</span>
						<span class="no-wrap" v-if="place.leg.duration" v-text="place.leg.duration.text"></span>
					</box>
				</column>
				<column :mobile="4" :tablet="4" v-if="hasPlace">
					<box class="small-box__interval">
						<div>
							<div>
								<icon medium>
									<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
										<use xlink:href="#icon-svg-hour-glass"></use>
									</svg>
								</icon>
							</div>
						</div>
						<div>Interval</div>
						<div>
							<template v-if="place.interval_hour">
								{{`${place.interval_hour} h`}}
							</template>
							<template v-if="place.interval_minute">
								{{`${place.interval_minute} m`}}
							</template>
						</div>
					</box>
				</column>
				<column :mobile="6" :tablet="6" v-if="hasPlace">
					<box class="small-box-second__ETA">
						<div>
							<icon medium>
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
									<use xlink:href="#icon-svg-calendar"></use>
								</svg>
							</icon>
						</div>
						<div>ETA</div>
						<div class="new-design__date">
							<span v-html="formatDate(place.reach_time)"></span>;
							<span v-html="formatTime(place.reach_time)"></span>
						</div>
					</box>
				</column>
				<column :mobile="6" :tablet="6" v-if="hasPlace">
					<box class="small-box-second__ETD">
						<div>
							<icon medium>
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
									<use xlink:href="#icon-svg-calendar"></use>
								</svg>
							</icon>
						</div>
						<span>ETD</span>
						<span class="new-design__date">
							<span v-html="formatDate(place.leave_time)"></span>;
							<span v-html="formatTime(place.leave_time)"></span>
							</span>
					</box>
				</column>
			</columns>

			<div class="button--action-top-right  button--action">
				<mdl-button type="raised" color="primary">{{letter}}</mdl-button>
			</div>
			<div class=" button--action-bottom-right  button--action" v-if="addButton">
				<mdl-button type="raised" color="primary" @click="buttonClicked('add',place)">
					<i class="fa fa-plus"></i>
				</mdl-button>
			</div>
			<div class=" button--action-button-left button--action" v-if="removeButton">
				<mdl-button type="raised" color="primary" @click="buttonClicked('trash',place)">
					<i class="fa fa-trash"></i>
				</mdl-button>
			</div>
		</div>
	</box>
</template>

<script>
	import {columns, column} from 'shapla-columns';
	import Box from "../shapla/box/box";
	import Icon from "../shapla/icon/icon";
	import MdlButton from "../material-design-lite/button/mdlButton";
	import {MapMixin} from "../frontend/dashboard/pages/MapMixin";

	export default {
		name: "AddressBox2",
		components: {MdlButton, Icon, Box, columns, column},
		mixins: [MapMixin],
		props: {
			place: {
				type: Object, required: false, default: () => {
				}
			},
			active: {type: Boolean, required: false, default: false},
			addButton: {type: Boolean, default: true},
			removeButton: {type: Boolean, default: true},
			letter: {type: String}
		},
		computed: {
			hasPlace() {
				return !!(this.place && Object.keys(this.place).length);
			},
			hasDefaultSlot() {
				return !!this.$slots.default
			},
			interval_hour() {
				if (this.place.interval_hour && this.place.interval_hour.length) {
					return parseInt(this.place.interval_hour);
				}
				return 0;
			},
			interval_minute() {
				if (this.place.interval_minute && this.place.interval_minute.length) {
					return parseInt(this.place.interval_minute);
				}
				return 0;
			},
			interval_seconds() {
				return (this.interval_hour * 60 * 60 * 1000) + (this.interval_minute * 60 * 1000);
			},
			departure_time() {
				return (this.place.reach_time + this.interval_seconds);
			}
		},
		methods: {
			buttonClicked(action, place) {
				this.$emit('button:click', action, place);
			},
			selectPlace(place) {
				this.$emit('click', place);
			},
			metres_to_km(metres) {
				if (metres < 100) return Math.round(metres) + " metres away";
				if (metres < 1000) return (metres / 1000).toFixed(2) + " km away";
				return (metres / 1000).toFixed(1) + " km away";
			},
		}
	}
</script>

<style lang="scss">
	.empty-padding-50 {
		display: flex;
		height: 10px;
		width: 100%;
	}

	.no-wrap {
		white-space: nowrap;
	}

	.button--action {
		border-radius: 50%;
		position: absolute;
		padding: 4px;

		&-bottom-right {
			right: -16px;
			bottom: -16px;
		}

		&-button-left {
			left: -16px;
			bottom: -16px;

			.mdl-button {
				background-color: red;
			}
		}

		&-top-right {
			right: -16px;
			top: -16px;
		}

		.mdl-button {
			min-width: 40px !important;
			height: 40px !important;
			border-radius: 50%;
		}
	}

	.new-design {
		display: flex;
		height: 100%;
		position: relative;

		.shapla-box {
			position: relative;
			height: 100%;

			display: flex;
			justify-content: center;
			align-items: center;
			flex-direction: column;

			.shapla-icon {
				padding-top: 10px;
			}

			svg {
				fill: currentColor;
			}
		}


		&__box-container {
			background-color: #fff;
			border: none;
			padding: 23px;
		}

		&__content {
			margin: 0 2em 1em 0;
		}

		&__title {
			font-size: 18px;
			font-weight: 700;
			margin-bottom: 1rem;
		}

		&__description {
			font-size: 16px;
		}

		&__small-box-container {
			display: flex;
			justify-content: space-between;
		}

		.small-box {
			margin: 6px;
			display: flex;
			justify-content: center;
			flex-direction: column;
			text-align: center;
			border-radius: 4px;
			width: 100%;

			&__distance {
				background-color: #ff7f00;
				color: white;
			}

			&__time {
				background-color: #1d426c;
				color: white;
			}

			&__interval {
				background-color: #b90506;
				color: white;
			}
		}

		&__small-box-container2 {
			display: flex;
			justify-content: space-between;
			margin-bottom: 20px;
		}

		.small-box-second {
			margin: 6px;
			display: flex;
			justify-content: center;
			flex-direction: column;
			text-align: center;
			border-radius: 4px;
			width: 100%;

			&__ETA {
				background-color: #787677;
				color: white;
			}

			&__ETD {
				background-color: #009903;
				color: white;
			}
		}

		&__date {
			font-size: 12px;
		}
	}
</style>

