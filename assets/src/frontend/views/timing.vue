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
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item day-active">
								<div class="select-time-weekday"> Today</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Mon</div>
									<div class="select-time-day-in-number"> 04</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Tue</div>
									<div class="select-time-day-in-number"> 05</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Wed</div>
									<div class="select-time-day-in-number"> 06</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Thu</div>
									<div class="select-time-day-in-number"> 07</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Fri</div>
									<div class="select-time-day-in-number"> 08</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Sat</div>
									<div class="select-time-day-in-number"> 09</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Sun</div>
									<div class="select-time-day-in-number"> 10</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Mon</div>
									<div class="select-time-day-in-number"> 11</div>
								</div>
							</div>
						</div>
						<div class="select-time-day-item-wrapper">
							<div class="select-time-day-item ">
								<div class="">
									<div class="select-time-weekday"> Tue</div>
									<div class="select-time-day-in-number"> 12</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="select-time-time-picker-wrapper">
			<template v-for="times in timeRanges">
				<button class="time-content-box hoverable" :class="{'time-content-box-active': timeRange === times}"
						@click="setTimeRange(times)">
					<div v-text="times"></div>
				</button>
			</template>
		</div>
		<div class="select-time-continue-button-wrapper">
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
				timeRanges: [
					'9am - 10am', '10am - 11am', '11am - 12pm', '12pm - 1pm', '1pm - 2pm', '2pm - 3pm', '3pm - 4pm',
					'4pm - 5pm', '5pm - 6pm', '6pm - 7pm', '7pm - 8pm', '8pm - 9pm', '9pm - 10pm',
				],
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_TIME_RANGE', '9am - 10am');
		},
		computed: {
			date() {
				return this.$store.state.date;
			},
			timeRange() {
				return this.$store.state.timeRange;
			},
		},
		methods: {
			setTimeRange(time) {
				this.$store.commit('SET_TIME_RANGE', time);
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
