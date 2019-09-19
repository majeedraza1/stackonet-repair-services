<template>
	<div class="timeline">

		<div class="timeline-item" @mouseenter="handleMouseEnter" @mouseleave="handleMouseOut">

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
					<div class="custom-select">
						<select @input="handleInput($event.target.value)">
							<option
								v-for="_address in item.addresses"
								:value="_address.place_id"
								:selected="address.place_id === _address.place_id"
							> {{_address.name}}
							</option>
						</select>
					</div>
					<div class="duration-text">
						<span class="segment-duration-part">{{item.activityDurationText}}</span>
					</div>
					<div class="">
						<button :id="`menu-item-actions-${this.id}`" class="mdl-button mdl-js-button mdl-button--icon">
							<i aria-hidden="true" class="fa fa-ellipsis-v"></i>
						</button>
					</div>
				</div>
				<div class="timeline-item-text">{{address.formatted_address}}</div>
			</div>
			<ul class="mdl-menu mdl-js-menu mdl-menu--bottom-right" :for="`menu-item-actions-${this.id}`">
				<li class="mdl-menu__item" @click="addAddressFromMap">
					Choose Address from Map
				</li>
				<li class="mdl-menu__item" @click="deleteTimelineItem">
					Delete this address
				</li>
			</ul>
		</div><!-- .timeline-item -->
	</div>
</template>

<script>
    import {MaterialMenu} from "../../material-design-lite/menu/MaterialMenu";

    export default {
        name: "googleTimelineItem",
        props: {
            item: {
                type: Object, default: () => {
                    return {
                        addresses: [],
                        activityDurationText: '',
                    }
                }
            },
            firstItem: {type: Boolean, default: false},
            lastItem: {type: Boolean, default: false},
            lineColor: {type: String, default: '#03A9F4'},
        },
        data() {
            return {
                id: null,
                address: {},
                active: false,
            }
        },
        computed: {
            placeIcon() {
                if (this.address.icon && this.address.icon.length) {
                    return this.address.icon;
                }

                return 'https://maps.gstatic.com/mapsactivities/icons/poi_icons/30_regular/generic_2x.png';
            }
        },
        mounted() {
            if (this.item.addresses.length) {
                this.address = this.item.addresses[0];
            }
            this.id = this._uid;
            setTimeout(() => {
                let menu = this.$el.querySelector('[for="menu-item-actions-' + this.id + '"]');
                new MaterialMenu(menu);
            }, 100);
        },
        watch: {
            item(newValue) {
                if (newValue.addresses.length) {
                    this.address = newValue.addresses[0];
                }
            }
        },
        methods: {
            handleInput(value) {
                let oldValue = this.address,
                    newValue = this.item.addresses.find(address => value === address.place_id);
                this.$emit('change', this.item, newValue, oldValue);
                this.address = newValue;
            },
            handleMouseEnter() {
                this.active = Date.now();
                setTimeout(() => {
                    if (Date.now() >= (this.active + 1000)) {
                        this.$emit('mouseenter', this.item);
                    }
                }, 1000);
            },
            handleMouseOut() {
                this.active = Date.now();
            },
            addAddressFromMap() {
                this.$emit('addAddress', this.item);
            },
            deleteTimelineItem() {
                this.$emit('deleteAddress', this.item);
            }
        }
    }
</script>

<style lang="scss">
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

	.timeline-item-icon.place-icon,
	.timeline-item-icon.default-timeline-item-icon {
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

	.custom-select {
		max-width: 175px;
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
