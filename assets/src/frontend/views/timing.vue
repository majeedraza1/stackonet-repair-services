<template>
	<div class="select-time-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper">
				<span class="step-nav-title">What time do you prefer?</span>
			</div>
		</div>
		<div class="info-box-wrapper">
			<div class="info-box-inner-wrapper">
				<span>We do our best to deliver amazing service at affordable prices to everyone. If we are unable to meet you at your desired time, we will deduct $5 and include a free tempered glass with the repair!</span>
				<span>We will confirm timing by email and SMS</span>
			</div>
		</div>
		<div>
			<div class="select-time-day-selector-container-desktop">
				<div class="select-time-day-selector-triangle"></div>
				<div class="select-time-day-selector-box">

					<div class="select-time-day-selector-wrapper">

						<template v-for="(_date, index) in dateRanges">
							<template v-if="index === 0">
								<div class="select-time-day-item-wrapper" @click="updateDate(_date)">
									<div class="select-time-day-item"
										 :class="{'day-active': tempDate.date === _date.date }">
										<div class="select-time-weekday">Today</div>
									</div>
								</div>
							</template>
							<template v-else>
								<div class="select-time-day-item-wrapper" @click="updateDate(_date)">
									<div class="select-time-day-item"
										 :class="{'day-active': tempDate.date === _date.date }">
										<div class="">
											<div class="select-time-weekday" v-html="getDayFromDate(_date)"></div>
											<div class="select-time-day-in-number" v-html="getDateNumber(_date)"></div>
										</div>
									</div>
								</div>
							</template>
						</template>
					</div>
				</div>
			</div>
		</div>

		<div v-if="tempDate.note" v-html="tempDate.note" class="holiday-note"></div>

		<div class="select-time-time-picker-wrapper">
			<template v-for="(times, dayName) in timeRanges">
				<template v-if="dayName === tempDate.day">
					<button v-for="time in times"
							:disabled="isHoliday"
							class="time-content-box hoverable"
							:class="{'time-content-box-active': tempTime === time}"
							@click="setTimeRange(time)">
						<div v-text="time"></div>
					</button>
				</template>
			</template>
		</div>
		<div class="select-time-continue-button-wrapper">
			<big-button @click="handleContinue" :disabled="!isButtonActive">Continue</big-button>
		</div>
		<p class="select-time-additional-text">
			Timing is subject to technician availability. We will<br>
			confirm timing by email and SMS.
		</p>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';

	export default {
		name: "timing",
		components: {BigButton},
		data() {
			return {
				dateRanges: [],
				timeRanges: [],
				tempDate: {
					date: '',
					day: '',
					holiday: false,
				},
				tempTime: '',
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);
			this.dateRanges = window.Stackonet.dateRanges;
			this.timeRanges = window.Stackonet.timeRanges;
			this.tempDate = this.dateRanges[0];
			this.tempTime = this.timeRanges[this.tempDate.day][0];

			// If no models, redirect one step back
			if (!this.hasIssues) {
				this.$router.push('/screen-cracked');
			}
		},
		computed: {
			issues() {
				return this.$store.state.issues;
			},
			hasIssues() {
				return !!(this.issues && this.issues.length);
			},
			date() {
				return this.$store.state.date;
			},
			timeRange() {
				return this.$store.state.timeRange;
			},
			isHoliday() {
				return !!(this.tempDate.holiday);
			},
			isButtonActive() {
				return !!(this.tempDate && this.tempDate.date && this.tempTime.length && !this.isHoliday);
			}
		},
		methods: {
			setTimeRange(time) {
				this.tempTime = time;
			},
			updateDate(date) {
				this.tempDate = date;
			},
			getDayFromDate(time) {
				let days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
				let date = new Date(time.date);
				return days[date.getDay()];
			},
			getDateNumber(time) {
				let date2 = new Date(time.date);
				let dateNumber = date2.getDate();

				return dateNumber.length === 1 ? '0' + dateNumber : dateNumber;
			},
			handleContinue() {
				this.$store.commit('SET_DATE', this.tempDate.date);
				this.$store.commit('SET_TIME_RANGE', this.tempTime);
				this.$router.push('/user-address');
			}
		}
	}
</script>

<style lang="scss">
	.select-time-day-selector-wrapper {
		display: flex;
		flex-wrap: wrap;
		margin-left: auto;
		margin-right: auto;
		max-width: 600px;
		justify-content: center;
	}

	.select-time-day-item-wrapper {
		padding: 15px;
	}

	.select-time-day-item {
		cursor: pointer;
		width: 75px;
		height: 75px;
		background: #fff;
		border-radius: 50%;
		overflow: hidden;
		display: flex;
		justify-content: center;
		align-items: center;
		transition: all .5s ease;

		&.day-active {
			background: #0161c7;
			color: #fff;
		}
	}

	.select-time-weekday {
		font-size: 10px;
		line-height: 12px;
		text-align: center;
	}

	.select-time-day-in-number {
		font-size: 22px;
		line-height: 22px;
		text-align: center;
	}

	.select-time-time-picker-wrapper {
		overflow: auto;
		margin: 50px auto 0;
		max-width: 840px;
		justify-content: center;
		display: flex;
		flex-wrap: wrap;
	}

	.time-content-box {
		float: left;
		box-sizing: border-box;
		width: 158px;
		margin: 15px;
		padding: 1em;
		border-radius: 4px;
		background: #fff;
		color: #444;
		transition: all .5s ease;
		text-align: center;
		cursor: pointer;
		border: 2px solid transparent;
		outline: none;

		&-active:not(:disabled) {
			background: #0161c7;
			color: #fff;
		}

		&:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}

		div {
			margin: 0;
			font-size: 16px;
			height: inherit;
		}
	}

	.select-time-continue-button-wrapper {
		margin: 40px auto;
	}

	.select-time-additional-text {
		color: #4a4a4a;
		font-size: 14px;
		font-weight: 300;
		line-height: 18px;
		text-align: center;
		padding: 0 10px;
		box-sizing: border-box;
	}

	.holiday-note {
		color: #3d4248;
		font-size: 22px;
		flex: 1 1;
		margin: 30px auto -20px;
		text-align: center;
	}
</style>
