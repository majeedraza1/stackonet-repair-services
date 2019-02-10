<template>
	<div class="select-time-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper">
				<span class="step-nav-title">What's your preferred timing?</span>
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
									<div class="select-time-day-item" :class="{'day-active': tempDate === _date }">
										<div class="select-time-weekday">Today</div>
									</div>
								</div>
							</template>
							<template v-else>
								<div class="select-time-day-item-wrapper" @click="updateDate(_date)">
									<div class="select-time-day-item" :class="{'day-active': tempDate === _date }">
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

		<div class="select-time-time-picker-wrapper">
			<template v-for="times in timeRanges">
				<button class="time-content-box hoverable" :class="{'time-content-box-active': tempTime === times}"
						@click="setTimeRange(times)">
					<div v-text="times"></div>
				</button>
			</template>
		</div>
		<div class="select-time-continue-button-wrapper" @click="handleContinue">
			<div class="select-time-continue-button select-time-continue-button-active">Continue</div>
		</div>
		<p class="select-time-additional-text">Timing is subject to technician availability. We will<br>confirm timing
			by email and SMS.</p></div>
</template>

<script>
	export default {
		name: "timing",
		data() {
			return {
				dateRanges: [],
				timeRanges: [],
				tempDate: '',
				tempTime: '',
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.dateRanges = window.Stackonet.dateRanges;
			this.timeRanges = window.Stackonet.timeRanges;
			this.tempDate = this.dateRanges[0];
			this.tempTime = this.timeRanges[0];

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
				let date = new Date(time);
				return days[date.getDay()];
			},
			getDateNumber(time) {
				let date2 = new Date(time);
				let dateNumber = date2.getDate();

				return dateNumber.length === 1 ? '0' + dateNumber : dateNumber;
			},
			handleContinue() {
				this.$store.commit('SET_DATE', this.tempDate);
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
		max-width: 500px;
	}

	.select-time-day-item-wrapper {
		padding: 12px;
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
		width: 840px;
	}

	.time-content-box {
		float: left;
		box-sizing: border-box;
		height: 50px;
		width: 158px;
		margin: 5px;
		line-height: 46px;
		border-radius: 4px;
		background: #fff;
		transition: all .5s ease;
		text-align: center;
		cursor: pointer;
		border: 2px solid transparent;
		outline: none;

		&-active {
			background: #0161c7;
			color: #fff;
		}

		div {
			margin: 0;
			font-size: 16px;
			height: inherit;
		}
	}

	.select-time-continue-button {
		width: 494px;
		color: #a9aeb3;
		background-color: #e1e8ec;
		margin: 40px auto;
		border-radius: 5px;
		line-height: 64px;
		text-align: center;
		transition: all .5s ease;
		font-size: 18px;

		&-active {
			color: #0161c7;
			background-color: #12ffcd;
		}
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
</style>
