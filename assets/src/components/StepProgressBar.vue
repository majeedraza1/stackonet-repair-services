<template>
	<div class="stackonet-step-progress-bar-container">
		<div class="checkout-bar stackonet-step-progress-bar">
			<span class="stackonet-step-progress-bar-selected" :style="{width: selectedBarWidth}"></span>
			<div class="stackonet-step-progress-bar__item" :class="{'is-active':item.active}"
				 :style="{width: itemWidth}" v-for=" item in steps">

				<div class="dropdown is-hoverable">
					<div class="dropdown-trigger stackonet-step-progress-bar__circle">
						<div class="stackonet-step-progress-bar__checkmark">
							<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
								 viewBox="0 0 24 24">
								<path d="M9 16.172l10.594-10.594 1.406 1.406-12 12-5.578-5.578 1.406-1.406z"></path>
							</svg>
						</div>
					</div>
					<div class="dropdown-menu" v-if="item.active">
						<div class="dropdown-content">
							<div class="dropdown-item">
								<strong>Date:</strong> {{item.date}}
							</div>
							<div class="dropdown-item">
								<strong>Time:</strong> {{item.time}}
							</div>

							<template v-if="item.label === 'Time'">
								<div class="dropdown-item" v-if="item.value">
									<strong>Date:</strong> {{item.value.date}}
								</div>
								<div class="dropdown-item" v-if="item.value">
									<strong>Time Range:</strong> {{item.value.time_range}}
								</div>
							</template>
							<template v-else-if="item.label === 'User Details'">
								<div class="dropdown-item" v-if="item.value">
									<strong>First Name:</strong> {{item.value.first_name}}
								</div>
								<div class="dropdown-item" v-if="item.value">
									<strong>Last Name:</strong> {{item.value.last_name}}
								</div>
								<div class="dropdown-item" v-if="item.value">
									<strong>Email:</strong> {{item.value.email}}
								</div>
								<div class="dropdown-item" v-if="item.value">
									<strong>Phone:</strong> {{item.value.phone}}
								</div>
							</template>
							<template v-else-if="item.label === 'Issues'">
								<div class="dropdown-item">
									<strong>Issues:</strong>
									<template v-for="(_issue, index) in item.value">
										<template v-if="index !== 0">,</template>
										{{_issue.title}}
									</template>
								</div>
							</template>
							<template v-else>
								<div class="dropdown-item" v-if="item.value">
									<strong>{{item.label}}:</strong> {{item.value}}
								</div>
							</template>
						</div>
					</div>
				</div>

				<div class="stackonet-step-progress-bar__label">{{item.label}}</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "StepProgressBar",
		props: {
			steps: {type: Array, required: true, default: () => []},
			selected: {type: Number, required: false, default: 0},
		},
		computed: {
			selectedBarWidth() {
				return this.selected + '%';
			},
			itemWidth() {
				return Math.round(100 / this.steps.length) + '%';
			},
		}
	}
</script>

<style lang="scss">

	.stackonet-step-progress-bar-container {
		display: flex;
		margin-bottom: 30px !important;
		margin-top: 20px;
		position: relative;

		.dropdown-menu {
			top: -20px;
		}

		.dropdown-content {
			box-shadow: none;
		}
	}

	.stackonet-step-progress-bar {
		box-shadow: inset 2px 2px 2px 0 rgba(0, 0, 0, 0.2);
		background-size: 35px 35px;
		background-color: #eeeeee;
		border-radius: 15px;
		height: 10px;
		margin: 0 auto;
		padding: 0;
		position: absolute;
		width: 100%;
		display: flex;
		justify-content: space-between;
		// z-index: 1;

		&-selected {
			background-size: 35px 35px;
			background-color: #f58730;
			background-image: linear-gradient(-45deg, rgba(255, 255, 255, 0.2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.2) 75%, transparent 75%, transparent);
			border-radius: 15px;
			content: " ";
			height: 10px;
			left: 0;
			position: absolute;
			width: 5%;
		}
	}

	.stackonet-step-progress-bar__item {
		font-size: 12px;
		font-weight: 200;
		position: relative;
		display: inline-flex;
		margin: 50px 0 0;
		padding: 0;
		text-align: center;
		width: 5%;

		&:not(.is-active) {
			.stackonet-step-progress-bar__checkmark {
				visibility: hidden;
			}
		}
	}

	.stackonet-step-progress-bar__circle {
		box-shadow: inset 2px 2px 2px 0 rgba(0, 0, 0, 0.2);
		background: #ddd;
		border: 2px solid #FFF;
		color: #fff;
		border-radius: 50%;
		left: 20px;
		height: 35px;
		top: -61px;
		position: absolute;
		width: 35px;
		// z-index: 999;
		display: flex;
		justify-content: center;
		align-items: center;

		.is-active & {
			background-color: #f58730;
		}

		svg {
			fill: #fff;
		}
	}

	.stackonet-step-progress-bar__checkmark {
		color: #333333;
		width: 24px;
		height: 24px;
	}

	.stackonet-step-progress-bar__label {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	@media screen and (max-width: 599px) {
		.stackonet-step-progress-bar__circle {
			height: 24px;
			top: -57px;
			width: 24px;

			svg {
				height: 16px;
				width: 16px;
			}
		}

		.stackonet-step-progress-bar__checkmark {
			display: flex;
			justify-content: center;
			align-items: center;
		}
	}
</style>
