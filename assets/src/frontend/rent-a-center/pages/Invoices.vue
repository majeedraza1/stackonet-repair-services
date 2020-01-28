<template>
	<div class="rent-a-center-dashboard-container">
		<columns>
			<column :tablet="12">
				<div class="section-total-damaged-devices">
					<box class="shablabox-border" id="box-color">
						<div class="section-title">6,500 Orders Fund</div>
						<div class="columns">
							<div class="column">
								<div class="control">
									<label class="radio">
										<input type="radio" name="answer">
										Order
									</label>
								</div>
							</div>
							<div class="column">
								Date
							</div>
							<div class="column">
								Name
							</div>
							<div class="column">
								Store Name
							</div>
							<div class="column">
								Preview Invoices
							</div>
							<div class="column">
								Download Invoices
							</div>
							<div class="column">
								Total
							</div>
						</div>
						<box>
							<div class="columns">
								<div class="column">
									<div class="control">
										<label class="radio">
											<input type="radio" name="answer">
											#1234
										</label>
									</div>
								</div>
								<div class="column">
									10 April 2019<br>
									11:21 AM
								</div>
								<div class="column">
									Andrew Arce
								</div>
								<div class="column">
									Florida main center
								</div>
								<div class="column">
									<mdl-button>Modify</mdl-button>
								</div>
								<div class="column">
									<mdl-button>Preview</mdl-button>
								</div>
								<div class="column">
									$210.16
								</div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column">
									<div class="control">
										<label class="radio">
											<input type="radio" name="answer">
											#1234
										</label>
									</div>
								</div>
								<div class="column">
									10 April 2019<br>
									11:21 AM
								</div>
								<div class="column">
									Andrew Arce
								</div>
								<div class="column">
									Florida main center
								</div>
								<div class="column">
									<mdl-button>Modify</mdl-button>
								</div>
								<div class="column">
									<mdl-button>Preview</mdl-button>
								</div>
								<div class="column">
									$210.16
								</div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column">
									<div class="control">
										<label class="radio">
											<input type="radio" name="answer">
											#1234
										</label>
									</div>
								</div>
								<div class="column">
									10 April 2019<br>
									11:21 AM
								</div>
								<div class="column">
									Andrew Arce
								</div>
								<div class="column">
									Florida main center
								</div>
								<div class="column">
									<mdl-button>Modify</mdl-button>
								</div>
								<div class="column">
									<mdl-button>Preview</mdl-button>
								</div>
								<div class="column">
									$210.16
								</div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column">
									<div class="control">
										<label class="radio">
											<input type="radio" name="answer">
											#1234
										</label>
									</div>
								</div>
								<div class="column">
									10 April 2019<br>
									11:21 AM
								</div>
								<div class="column">
									Andrew Arce
								</div>
								<div class="column">
									Florida main center
								</div>
								<div class="column">
									<mdl-button>Modify</mdl-button>
								</div>
								<div class="column">
									<mdl-button>Preview</mdl-button>
								</div>
								<div class="column">
									$210.16
								</div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column">
									<div class="control">
										<label class="radio">
											<input type="radio" name="answer">
											#1234
										</label>
									</div>
								</div>
								<div class="column">
									10 April 2019<br>
									11:21 AM
								</div>
								<div class="column">
									Andrew Arce
								</div>
								<div class="column">
									Florida main center
								</div>
								<div class="column">
									<mdl-button>Modify</mdl-button>
								</div>
								<div class="column">
									<mdl-button>Preview</mdl-button>
								</div>
								<div class="column">
									$210.16
								</div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column">
									<div class="control">
										<label class="radio">
											<input type="radio" name="answer">
											#1234
										</label>
									</div>
								</div>
								<div class="column">
									10 April 2019<br>
									11:21 AM
								</div>
								<div class="column">
									Andrew Arce
								</div>
								<div class="column">
									Florida main center
								</div>
								<div class="column">
									<mdl-button>Modify</mdl-button>
								</div>
								<div class="column">
									<mdl-button>Preview</mdl-button>
								</div>
								<div class="column">
									$210.16
								</div>
							</div>
						</box>


						<div class="vl"></div>
					</box>
				</div>
			</column>

		</columns>

	</div>
</template>

<script>
	import {columns, column} from 'shapla-columns'
	import icon from '../../../shapla/icon/icon'
	import imageContainer from '../../../shapla/image/image'
	import box from '../../../shapla/box/box'
	import MdlButton from "../../../material-design-lite/button/mdlButton";


	export default {
		name: "Invoices",
		components: {MdlButton, columns, column, icon, imageContainer, box},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SNACKBAR', {
				message: 'Check if working.'
			});
		}
	}
</script>

<style lang="scss">

	.vl {
		border-left: 6px solid grey;
		height: 500px;
		float: right;
		margin-top: -500px;
		margin-right: -50px;


	}

	#shapla-box-container {
		border: solid rgba(0, 0, 0, 0.12);
		padding-right: 8.25rem;
		padding-bottom: 8.25rem;
		box-shadow: grey;
	}

	.rent-a-center-dashboard-container {
		.mdl-button {
			background-image: linear-gradient(90deg, #f9a73b, #f58730);
			border-radius: 5px;
		}

		.select:not(.is-multiple):not(.is-loading)::after {
			border-color: black;
		}

		.control {
			margin-left: 30px;
			font-size: medium;
		}

		.shapla-box {
			margin-bottom: 10px;
		}

		.store-managers-list {
			display: flex;
			justify-content: center;

			&__item {
				display: block;
				width: 42px;

				&:not(:first-child) {
					margin-left: -10px;
				}

				img {
					border: 2px solid white;
				}
			}

			&__icon {
				background: #F1F1F1;
				border-radius: 100px;
				cursor: pointer;
			}
		}

		.section-reports,
		.section-latest-status,
		.section-store-managers {
			margin-bottom: 1.5rem;
		}

		.section-title {
			margin-bottom: 1em;
			font-weight: bold;
			font-size: 16px;
		}
	}

	.recent-activities-list {
		&__item {
			display: flex;

			&:not(:last-child) {
				margin-bottom: 1rem;
				padding-bottom: 1rem;
				border-bottom: 1px solid rgba(#000, .2);
			}
		}

		&__avatar {
			padding-right: 8px;
			width: 48px;
		}

		&__content {
			font-size: 16px;
			line-height: 1.5;
		}

		&__time {
			color: rgba(#000, .65);
			font-size: .75em;
		}

		&__title {
			font-size: 0.875em;
			font-weight: bold;
		}

		&__description {
			font-size: 0.875em;
		}
	}
</style>
