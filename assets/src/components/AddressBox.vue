<template>
	<div class="google-address-box mdl-shadow--4dp" :class="{'is-active':active, 'has-right-slot':hasDefaultSlot}"
		 @click="selectPlace(place)">
		<slot name="left">
			<div class="google-address-box__left" v-if="hasPlace">
				<div class="google-address-box__name" v-if="place.name" v-text="place.name"></div>
				<div class="google-address-box__formatted_address" v-if="place.formatted_address"
					 v-html="place.formatted_address"></div>
				<div class="google-address-box__formatted_distance" v-if="place.distance"
					 v-html="metres_to_km(place.distance)"></div>
			</div>
		</slot>
		<div class="google-address-box__right">
			<slot></slot>
		</div>
	</div>
</template>

<script>
	export default {
		name: "AddressBox",
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
