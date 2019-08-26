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
							<i aria-hidden="true" class="fa fa-ellipsis-v"></i>
						</button>
					</div>
				</div>
				<div class="timeline-item-text">{{itemText}}</div>
			</div>
		</div><!-- .timeline-item -->

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

<style scoped lang="scss">
	.timeline-item {
		line-height: 20px;
		padding: 20px 16px;
		position: relative;
		font-size: 12px;
		font-weight: 400;
		color: rgba(0, 0, 0, 0.54);

	}

	.timeline-item-svg {
		height: 100%;
		left: 57px;
		position: absolute;
		top: 0;
		width: 14px;
		z-index: 1;
	}

	svg:not(:root) {
		overflow: hidden;
	}

	.segment-divider {
		background-color: rgba(255, 255, 255, 0.54);
		border-radius: 5px;
		height: 10px;
		left: 59px;
		margin-top: 16px;
		position: absolute;
		width: 10px;
		z-index: 1;
	}

	.timeline-item-svg-line {
		stroke-width: 14px;
	}

	.timeline-item-icon {
		background-repeat: no-repeat;
		position: absolute;
		z-index: 1;
	}

	.timeline-item-icon.place-icon {
		border-radius: 16px;
		left: 16px;
	}

	.timeline-item-icon.place-icon, .timeline-item-icon.default-timeline-item-icon {
		background-position: 50%;
		background-size: 30px;
		height: 30px;
		top: 25px;
		width: 30px;
		transition: box-shadow .218s;
	}

	.timeline-item-content.primary {
		margin-left: 80px;
		position: relative;
		text-align: left;
	}

	.timeline-item-content.multi-line {
		padding-top: 0;
	}

	.timeline-item-title {
		margin: 0;
		align-items: center;
		display: flex;
	}

	.duration-text {
		border-bottom: 1px solid transparent;
		border-radius: 2px;
		color: rgba(0, 0, 0, 0.54);
		cursor: pointer;
		margin: 0 8px;
		outline: none;
		padding: 4px 8px;
		transition: background-color .2s;
		opacity: 0.8;
	}

	.segment-duration-part {
		white-space: nowrap;
	}

	.timeline-item .timeline-item-text {
		padding-top: 5px;
		text-align: left;
	}

	.travel-segment {
		position: relative;
	}

	.moment-divider {
		height: 1px;
		left: 0;
		margin: 0;
		opacity: 0;
		padding: 0;
		position: absolute;
		top: 0;
		width: 100%;
	}

	.add-a-place {
		bottom: 0;
		height: 100%;
		left: 0;
		position: absolute;
		width: 57px;
		z-index: 1;
		transition: all .2s cubic-bezier(0.4, 0.0, 1, 1);
	}

	.add-a-place-text {
		top: 40px;
		display: inline;
		left: 82px;
		margin: -12px 0;
		opacity: 0;
		height: 24px;
		line-height: 24px;
		padding: 0 8px;
		position: absolute;
		visibility: hidden;
		white-space: nowrap;
		z-index: 1;
	}

	.place-history-moment-outer .moment-edit-control {
		margin-left: -12px;
		min-height: 36px;
		min-width: 192px;
		padding: 2px 12px;
		transition: background-color .2s cubic-bezier(0.4, 0.0, 1, 1);
	}

	.activity-segment-outer .moment-edit-control {
		border: 1px solid transparent;
	}

	.timeline-item-title-content {
		font-weight: 500;
		margin-right: auto;
		vertical-align: middle;
		align-items: center;
		display: flex;
	}

	.activity-segment-outer .timeline-item-title-content .activity-icon {
		margin-right: 16px;
	}

	.activity-icon {
		background-size: 24px;
		height: 24px;
		opacity: .54;
		width: 24px;
	}

	.distance-text {
		color: rgba(0, 0, 0, 0.38);
		padding: 0 5px;
		white-space: nowrap;
	}
</style>
