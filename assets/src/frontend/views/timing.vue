<template>
	<div class="select-time-wrapper">

		<section-title>What time do you prefer?</section-title>

		<section-info>
			<span>We do our best to deliver amazing service at affordable prices to everyone. If we are unable to meet you at your desired time, we will deduct $5 and include a free tempered glass with the repair!</span>
			<span>We will confirm timing by email and SMS</span>
		</section-info>

		<div class="select-time-day-selector-wrapper">

			<template v-for="(_date, index) in dateRanges">
				<template v-if="index === 0">
					<div class="shapla-date-time-box" @click="updateTodayDate(_date)">
						<div class="shapla-date-time-box__content"
							 :class="{'is-active': tempDate.date === _date.date }">
							<div class="shapla-date-time-box__day">Today</div>
						</div>
					</div>
				</template>
				<template v-else>
					<div class="shapla-date-time-box" @click="updateDate(_date)">
						<div class="shapla-date-time-box__content"
							 :class="{'is-active': tempDate.date === _date.date }">
							<div class="">
								<div class="shapla-date-time-box__day" v-html="getDayFromDate(_date)"></div>
								<div class="shapla-date-time-box__date" v-html="getDateNumber(_date)"></div>
							</div>
						</div>
					</div>
				</template>
			</template>
		</div>

		<div v-if="tempDate.note" v-html="tempDate.note" class="holiday-note"></div>

		<div class="select-time-time-picker-wrapper">
			<template v-if="isToday">
				<div class="shapla-device-box shapla-device-box--time" v-for="time in todayTimeRanges">
					<div class="shapla-device-box__content hoverable"
						 :class="{'is-active': tempTime === time}"
						 @click="setTimeRange(time)">
						<div v-text="time"></div>
					</div>
				</div>
			</template>
			<template v-for="(times, dayName) in timeRanges" v-else>
				<template v-if="dayName === tempDate.day">
					<div class="shapla-device-box shapla-device-box--time" v-for="time in times">
						<div :disabled="isHoliday"
							 class="shapla-device-box__content hoverable"
							 :class="{'is-active': tempTime === time}"
							 @click="setTimeRange(time)">
							<div v-text="time"></div>
						</div>
					</div>
				</template>
			</template>
		</div>

		<big-button @click="handleContinue" :disabled="!isButtonActive">Continue</big-button>

		<p class="select-time-extra-info">
			Timing is subject to technician availability. We will<br>
			confirm timing by email and SMS.
		</p>

		<section-help></section-help>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';
	import SectionTitle from '../components/SectionTitle'
	import SectionInfo from '../components/SectionInfo'
	import SectionHelp from '../components/SectionHelp'
	import {mapState} from 'vuex';

	export default {
		name: "timing",
		components: {BigButton, SectionTitle, SectionInfo, SectionHelp},
		data() {
			return {
				isToday: true,
				dateRanges: [],
				timeRanges: [],
				todayTimeRanges: [],
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
			this.todayTimeRanges = window.Stackonet.todayTimeRanges;
			this.tempDate = this.dateRanges[0];
			this.tempTime = this.todayTimeRanges[0];

			// If no models, redirect one step back
			if (!this.hasIssues) {
				this.$router.push('/screen-cracked');
			}

			this.$store.dispatch('updateCheckoutAnalysis', {
				step: 'requested_date_time',
				step_data: {device_issue: this.issues}
			});
		},
		computed: {
			...mapState(['issues', 'date', 'timeRange', 'checkoutAnalysisId']),
			hasIssues() {
				return !!(this.issues && this.issues.length);
			},
			isHoliday() {
				return !!(this.tempDate.holiday);
			},
			isButtonActive() {
				// return !!(this.tempDate && this.tempDate.date && this.tempTime.length && !this.isHoliday);
			}
		},
		methods: {
			setTimeRange(time) {
				this.tempTime = time;
			},
			updateTodayDate(date) {
				this.tempDate = date;
				this.tempTime = this.timeRanges[this.tempDate.day][0];
				this.isToday = true;
			},
			updateDate(date) {
				this.tempDate = date;
				this.tempTime = this.timeRanges[this.tempDate.day][0];
				this.isToday = false;
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
	.select-time-wrapper {
		max-width: 850px;
		margin-left: auto;
		margin-right: auto;
	}

	.select-time-day-selector-wrapper,
	.select-time-time-picker-wrapper {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		margin-left: auto;
		margin-right: auto;
	}

	.select-time-day-selector-wrapper {
		max-width: 600px;
	}

	.select-time-time-picker-wrapper {
		margin-top: 50px;
		max-width: 840px;
	}

	.shapla-device-box--time {
		.shapla-device-box__content {
			height: 50px;
			padding: 10px;
			text-align: center;

			&.is-active {
				background: #f58730;
				color: #fff;
			}
		}
	}

	.big-button-wrapper {
		margin: 40px auto;
	}

	.select-time-extra-info {
		color: #4a4a4a;
		font-size: 14px;
		font-weight: 300;
		line-height: 18px;
		text-align: center;
		padding: 0 10px;
		box-sizing: border-box;
	}

	.holiday-note {
		color: #424242;
		font-size: 22px;
		flex: 1 1;
		margin: 30px auto -20px;
		text-align: center;
	}
</style>
