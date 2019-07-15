<template>
	<div class="google-address-box mdl-shadow--4dp" :class="{'is-active':active, 'has-right-slot':hasDefaultSlot}"
		 @click="selectPlace(place)">
		<slot name="left">
			<div class="google-address-box__left" v-if="hasPlace">
				<div class="google-address-box__name" v-if="place.name" v-text="place.name"></div>
				<div class="google-address-box__formatted_address" v-if="place.formatted_address"
					 v-html="place.formatted_address"></div>
				<template v-if="place.leg">
					<div class="google-address-box__formatted_distance" v-if="place.leg.distance">
						{{place.leg.distance.text}}
					</div>
					<div class="google-address-box__formatted_distance" v-if="place.leg.distance">
						{{place.leg.duration.text}}
					</div>
				</template>
				<template v-else>
					<div class="google-address-box__formatted_distance" v-if="place.distance"
						 v-html="metres_to_km(place.distance)"></div>
				</template>
				<div class="google-address-box__formatted_distance" v-if="place.reach_time">
					<span>ETA :&nbsp; </span>
					<span v-html="formatDate(place.reach_time)"></span>;
					<span v-html="formatTime(place.reach_time)"></span>
				</div>
				<template v-if="place.interval_hour || place.interval_minute">
					<div class="google-address-box__formatted_distance">
						Interval :
						<template v-if="place.interval_hour">
							{{parseInt(place.interval_hour)>1 ?`${place.interval_hour} hours`:`${place.interval_hour}
							hour`}}
						</template>
						<template v-if="place.interval_minute">
							{{parseInt(place.interval_minute)>1 ?`${place.interval_minute}
							mins`:`${place.interval_minute}
							min`}}
						</template>
					</div>
					<div class="google-address-box__formatted_distance" v-if="place.leave_time">
						<span>ETD :&nbsp; </span>
						<span v-html="formatDate(place.leave_time)"></span>;
						<span v-html="formatTime(place.leave_time)"></span>
					</div>
				</template>

			</div>
		</slot>
		<div class="google-address-box__right">
			<slot></slot>
		</div>
	</div>
</template>

<script>
	import {MapMixin} from "../frontend/dashboard/pages/MapMixin";

	export default {
		name: "AddressBox",
		mixins: [MapMixin],
		props: {
			place: {
				type: Object, required: false, default: () => {
				}
			},
			active: {type: Boolean, required: false, default: false},
		},
		computed: {
			hasPlace() {
				return !!Object.keys(this.place).length;
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

<style scoped lang="scss">
	.google-address-box {
		background: white;
		display: flex;
		margin: 10px 0;
		padding: 12px;
		position: relative;

		&__name {
			font-weight: bold;
		}

		&.is-active {
			background-color: #ddd;
			color: #323232;
		}

		&__formatted_distance {
			background: #faa644;
			display: inline-flex;
			padding: 0.5rem 1rem;
			margin-top: 1rem;
		}

		&.has-right-slot {
			.google-address-box__right {
				display: flex;
				flex-direction: column;
				min-width: 90px;
				padding-left: 10px;
			}
		}
	}
</style>
