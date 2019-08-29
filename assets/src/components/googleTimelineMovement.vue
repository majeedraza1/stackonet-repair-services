<template>
	<div class="timeline">
		<div class="travel-segment">
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
								<div class="distance-text"> - {{activityDistanceText}}</div>
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
        name: "googleTimelineMovement",
        props: {
            lineColor: {
                type: String,
                default: '#03A9F4'
            },
            activityIcon: {
                type: String,
                default: 'https://maps.gstatic.com/mapsactivities/icons/activity_icons/2x/ic_activity_moving_black_24dp.png'
            },
            activityType: {
                type: String,
                default: 'Walking'
            },
            activityDistanceText: {
                type: String,
                default: '- 1.4 km'
            },
            activityDurationText: {
                type: String,
                default: '10 mins'
            },
        }
    }
</script>

<style scoped>

</style>
