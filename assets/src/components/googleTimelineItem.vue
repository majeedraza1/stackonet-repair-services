<template>
	<div class="timeline">

		<div class="timeline-item">

			<svg class="timeline-item-svg">
				<line stroke-linecap="round" x1="7" x2="7" y1="0" y2="41" class="timeline-item-svg-line"
					  :style="{display:firstItem?'none':'',stroke:lineColor}"></line>
				<line stroke-linecap="round" x1="7" x2="7" y1="41" y2="100%" class="timeline-item-svg-line"
					  :style="{display:lastItem?'none':'',stroke:lineColor}"></line>
			</svg>

			<div class="segment-divider"></div>

			<div class="timeline-item-icon place-icon" :style="`backgroundImage: url(${placeIcon})`"></div>

			<div class="place-history-moment-content timeline-item-content primary multi-line">
				<div class="timeline-item-title">
					<div class="custom-select" style="width:200px;">
						<select>
							<option :value="_address.place_id" v-for="_address in addresses">{{_address.name}}</option>
						</select>
					</div>
					<div class="duration-text">
						<!--<span v-if="Object.keys(address).length" class="segment-duration-part">{{address['formatted_address']}}</span>-->
						<span class="segment-duration-part">{{durationText}}</span>
					</div>
					<div class="">
						<button class="mdl-button mdl-js-button mdl-button--icon">
							<i class="material-icons">more_vert</i>
						</button>
					</div>
				</div>
				<div class="timeline-item-text">{{itemText}}</div>
			</div>
		</div>

		<div class="travel-segment" v-if="!lastItem">
			<hr class="moment-divider">
			<div class="activity-segment-outer">
				<div class="timeline-item place-history-moment-outer">
					<svg class="timeline-item-svg">
						<line x1="7" x2="7" y1="0" y2="100%" class="timeline-item-svg-line"
							  :style="{stroke:lineColor}"></line>
					</svg>
					<div class="place-history-moment-content timeline-item-content primary multi-line">
						<div class="timeline-item-title">
							<div class="edit-dialog-select moment-edit-control timeline-item-title-content">
								<div class="activity-icon" :style="`backgroundImage: url(${activityIcon})`"></div>
								<span class="activity-type">{{activityType}}</span>
								<div class="distance-text">{{activityDistanceText}}</div>
								<div class="duration-text">{{activityDurationText}}</div>
							</div>
						</div>
					</div>
					<hr class="moment-divider">
				</div>
			</div>
		</div><!-- .travel-segment -->

	</div>
</template>

<script>
    export default {
        name: "googleTimelineItem",
        props: {
            firstItem: {type: Boolean, default: false},
            lastItem: {type: Boolean, default: false},
            lineColor: {type: String, default: '#03A9F4'},
            itemText: {type: String, default: 'Electronic City, Bengaluru, Karnataka'},
            durationText: {type: String, default: '9:41 AM'},
            addresses: {
                type: Array, default: () => [
                    {
                        place_id: 'BhoomikaTower',
                        name: 'Bhoomika Tower',
                        formatted_address: '19th Main Road, KHB Colony, 6th Block, Koramangala, Bengaluru, Karnataka 560095'
                    }
                ]
            },
            placeIcon: {
                type: String,
                default: 'https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png'
            },
            activityIcon: {
                type: String,
                default: 'https://maps.gstatic.com/mapsactivities/icons/activity_icons/2x/ic_activity_walking_black_24dp.png'
            },
            activityType: {type: String, default: 'Walking'},
            activityDistanceText: {type: String, default: '- 1.4 km'},
            activityDurationText: {type: String, default: '10 mins'},
        },
        data() {
            return {
                address: {},
            }
        },
        mounted() {
            if (this.addresses.length) {
                this.address = this.addresses[0];
            }
        }
    }
</script>

<style scoped>

</style>
