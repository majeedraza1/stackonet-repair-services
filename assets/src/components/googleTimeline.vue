<template>
	<div class="wrapper">
		<div class="timeline-content">

			<div class="timeline" v-for="(item, index) in options">
				<google-timeline-item
					:item="item"
					:first-item="index === 0"
					:last-item="index === (options.length - 1)"
				></google-timeline-item>
			</div>


			<div class="" style="display: none">
				<div class="timeline-item place-history-moment-outer">
					<svg class="timeline-item-svg">
						<line stroke-linecap="round" x1="7" x2="7" y1="0" y2="41" jstcache="611"
							  class="timeline-item-svg-line" style="stroke:#03A9F4"
							  jsan="7.timeline-item-svg-line,4.style,0.stroke-linecap,0.x1,0.x2,0.y1,0.y2"></line>
						<line stroke-linecap="round" x1="7" x2="7" y1="41" y2="100%" jstcache="612"
							  class="timeline-item-svg-line" style="stroke:#01579B"
							  jsan="7.timeline-item-svg-line,4.style,0.stroke-linecap,0.x1,0.x2,0.y1,0.y2"></line>
					</svg>
					<div class="segment-divider"></div>
					<div class="timeline-item-icon place-icon"
						 style="background-image:url(https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png)"></div>
					<div class="place-history-moment-content timeline-item-content primary multi-line">
						<div class="timeline-item-title">
							<div class="custom-select" style="width:200px;">
								<select>
									<option value="0">Electronic city</option>
									<option value="1">Koramangla</option>
									<option value="2">forum</option>
								</select>
							</div>
							<div class="duration-text">
								<span class="segment-duration-part">9:41 AM</span>
							</div>
							<div class="">
								<button
									class="mdl-button mdl-js-button mdl-button--icon">
									<i class="material-icons">more_vert</i>
								</button>
								<ul class="mdl-menu mdl-menu--bottom-left mdl-js-menu mdl-js-ripple-effect"
									for="demo-menu-lower-left">
									<li class="mdl-menu__item">Some Action</li>
									<li class="mdl-menu__item mdl-menu__item--full-bleed-divider">Another Action</li>
									<li disabled class="mdl-menu__item">Disabled Action</li>
									<li class="mdl-menu__item">Yet Another Action</li>
								</ul>
							</div>
						</div>
						<div class="timeline-item-text">Electronic City, Bengaluru, Karnataka</div>
					</div>
				</div>
				<div class="travel-segment">
					<hr class="moment-divider">
					<div class="activity-segment-outer">
						<div class="timeline-item place-history-moment-outer">
							<svg class="timeline-item-svg">
								<line x1="7" x2="7" y1="0" y2="100%" jstcache="700" class="timeline-item-svg-line"
									  style="stroke:#01579B"
									  jsan="7.timeline-item-svg-line,4.style,0.x1,0.x2,0.y1,0.y2"></line>
							</svg>
							<div class="place-history-moment-content timeline-item-content primary multi-line">
								<div class="timeline-item-title">
									<div class="edit-dialog-select moment-edit-control timeline-item-title-content">
										<div class="activity-icon"
											 style="background-image: url(https://maps.gstatic.com/mapsactivities/icons/activity_icons/2x/ic_activity_walking_black_24dp.png);"></div>
										<span class="activity-type">Walking</span>
										<div class="distance-text"> - 1.4 km</div>
										<div class="duration-text">10 mins</div>
									</div>
								</div>
							</div>
							<hr class="moment-divider">
						</div>
					</div>
				</div>
			</div>
			<div class="" style="display: none">
				<div class="timeline-item place-history-moment-outer">
					<svg class="timeline-item-svg">
						<line x1="7" x2="7" y1="0" y2="100%" jstcache="700" class="timeline-item-svg-line"
							  style="stroke:#01579B" jsan="7.timeline-item-svg-line,4.style,0.x1,0.x2,0.y1,0.y2"></line>
					</svg>
					<div class="segment-divider"></div>
					<div class="timeline-item-icon place-icon"
						 style="background-image:url(https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png)"></div>
					<div class="place-history-moment-content timeline-item-content primary multi-line">
						<div class="timeline-item-title">
							<div class="custom-select" style="width:200px;">
								<select>
									<option value="0">Electronic city</option>
									<option value="1">Koramangla</option>
									<option value="2">forum</option>
								</select>
							</div>
							<div class="duration-text">
								<span class="segment-duration-part">9:41 AM</span>
							</div>
							<div class="">
								<button
									class="mdl-button mdl-js-button mdl-button--icon">
									<i class="material-icons">more_vert</i>
								</button>
								<ul class="mdl-menu mdl-menu--bottom-left mdl-js-menu mdl-js-ripple-effect"
									for="demo-menu-lower-left">
									<li class="mdl-menu__item">Some Action</li>
									<li class="mdl-menu__item mdl-menu__item--full-bleed-divider">Another Action</li>
									<li disabled class="mdl-menu__item">Disabled Action</li>
									<li class="mdl-menu__item">Yet Another Action</li>
								</ul>
							</div>
						</div>
						<div class="timeline-item-text">Electronic City, Bengaluru, Karnataka</div>
					</div>
				</div>
				<div class="travel-segment">
					<hr class="moment-divider">
					<div class="activity-segment-outer">
						<div class="timeline-item place-history-moment-outer">
							<svg class="timeline-item-svg">
								<line x1="7" x2="7" y1="0" y2="100%" jstcache="700" class="timeline-item-svg-line"
									  style="stroke:#01579B"
									  jsan="7.timeline-item-svg-line,4.style,0.x1,0.x2,0.y1,0.y2"></line>
							</svg>
							<div class="place-history-moment-content timeline-item-content primary multi-line">
								<div class="timeline-item-title">
									<div class="edit-dialog-select moment-edit-control timeline-item-title-content">
										<div class="activity-icon"
											 style="background-image: url(https://maps.gstatic.com/mapsactivities/icons/activity_icons/2x/ic_activity_walking_black_24dp.png);"></div>
										<span class="activity-type">Walking</span>
										<div class="distance-text"> - 1.4 km</div>
										<div class="duration-text">10 mins</div>
									</div>
								</div>
							</div>
							<hr class="moment-divider">
						</div>
					</div>
				</div>
			</div>
			<div class="" style="display: none">
				<div class="timeline-item">
					<svg class="timeline-item-svg">
						<line stroke-linecap="round" x1="7" x2="7" y1="0" y2="41" jstcache="611"
							  class="timeline-item-svg-line" style="stroke:#01579B"
							  jsan="7.timeline-item-svg-line,4.style,0.stroke-linecap,0.x1,0.x2,0.y1,0.y2"></line>
						<line stroke-linecap="round" x1="7" x2="7" y1="41" y2="100%" jstcache="612"
							  class="timeline-item-svg-line" style="stroke:#03A9F4"
							  jsan="7.timeline-item-svg-line,4.style,0.stroke-linecap,0.x1,0.x2,0.y1,0.y2"></line>
					</svg>
					<div class="segment-divider"></div>
					<div class="timeline-item-icon place-icon"
						 style="background-image:url(https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png)"></div>
					<div class="place-history-moment-content timeline-item-content primary multi-line">
						<div class="timeline-item-title">
							<div class="custom-select" style="width:200px;">
								<select>
									<option value="0">Electronic city</option>
									<option value="1">Koramangla</option>
									<option value="2">forum</option>
								</select>
							</div>
							<div class="duration-text">
								<span class="segment-duration-part">9:41 AM</span>
							</div>
							<div class="">
								<button id="demo-menu-lower-left"
										class="mdl-button mdl-js-button mdl-button--icon">
									<i class="material-icons">more_vert</i>
								</button>

								<ul class="mdl-menu mdl-menu--bottom-left mdl-js-menu mdl-js-ripple-effect"
									for="demo-menu-lower-left">
									<li class="mdl-menu__item">Some Action</li>
									<li class="mdl-menu__item mdl-menu__item--full-bleed-divider">Another Action</li>
									<li disabled class="mdl-menu__item">Disabled Action</li>
									<li class="mdl-menu__item">Yet Another Action</li>
								</ul>
							</div>
						</div>
						<div class="timeline-item-text">Electronic City, Bengaluru, Karnataka</div>
					</div>
				</div>
				<div class="travel-segment">
					<hr class="moment-divider">
					<div class="activity-segment-outer">
						<div class="timeline-item place-history-moment-outer">
							<svg class="timeline-item-svg">
								<line x1="7" x2="7" y1="0" y2="100%" jstcache="700" class="timeline-item-svg-line"
									  style="stroke:#03A9F4"
									  jsan="7.timeline-item-svg-line,4.style,0.x1,0.x2,0.y1,0.y2"></line>
							</svg>
							<div class="place-history-moment-content timeline-item-content primary multi-line">
								<div class="timeline-item-title">
									<div class="edit-dialog-select moment-edit-control timeline-item-title-content">
										<div class="activity-icon"
											 style="background-image: url(https://maps.gstatic.com/mapsactivities/icons/activity_icons/2x/ic_activity_walking_black_24dp.png);"></div>
										<span class="activity-type">Walking</span>
										<div class="distance-text"> - 1.4 km</div>
										<div class="duration-text">10 mins</div>
									</div>
								</div>
							</div>
							<hr class="moment-divider">
						</div>
					</div>
				</div>
			</div>
			<div class="" style="display: none">
				<div class="timeline-item">
					<svg class="timeline-item-svg">
						<line stroke-linecap="round" x1="7" x2="7" y1="0" y2="41" jstcache="611"
							  class="timeline-item-svg-line" style="stroke:#03A9F4"
							  jsan="7.timeline-item-svg-line,4.style,0.stroke-linecap,0.x1,0.x2,0.y1,0.y2"></line>
						<line stroke-linecap="round" x1="7" x2="7" y1="41" y2="100%" jstcache="612"
							  class="timeline-item-svg-line" style="display:none"
							  jsan="7.timeline-item-svg-line,0.stroke-linecap,0.x1,0.x2,0.y1,0.y2"></line>
					</svg>
					<div class="segment-divider"></div>
					<div class="timeline-item-icon place-icon"
						 style="background-image:url(https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png)"></div>
					<div class="place-history-moment-content timeline-item-content primary multi-line">
						<div class="timeline-item-title">
							<div class="custom-select" style="width:200px;">
								<select>
									<option value="0">Electronic city</option>
									<option value="1">Koramangla</option>
									<option value="2">forum</option>
								</select>
							</div>
							<div class="duration-text">
								<span class="segment-duration-part">9:41 AM</span>
							</div>
							<div class="">
								<button class="mdl-button mdl-js-button mdl-button--icon">
									<i class="material-icons">more_vert</i>
								</button>

								<ul class="mdl-menu mdl-menu--bottom-left mdl-js-menu mdl-js-ripple-effect"
									for="demo-menu-lower-left">
									<li class="mdl-menu__item">Some Action</li>
									<li class="mdl-menu__item mdl-menu__item--full-bleed-divider">Another Action</li>
									<li disabled class="mdl-menu__item">Disabled Action</li>
									<li class="mdl-menu__item">Yet Another Action</li>
								</ul>
							</div>
						</div>
						<div class="timeline-item-text">Electronic City, Bengaluru, Karnataka</div>
					</div>
				</div>

			</div>
		</div>
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	</div>

</template>

<script>


    import GoogleTimelineItem from "./googleTimelineItem";

    export default {
        name: 'googleTimeline',
        components: {GoogleTimelineItem},
        props: {
            items: {type: Array, default: () => []}
        },

        data() {
            return {
                options: [
                    {},
                    {},
                    {},
                    {},
                ]
            }
        }
    }


</script>

<style lang="scss">
	// @import "~material-design-lite/src/material-design-lite";

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
