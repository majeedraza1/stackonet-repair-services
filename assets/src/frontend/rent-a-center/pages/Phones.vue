<template>
	<div class="rent-a-center-dashboard-container">
		<columns>
			<column :tablet="12">
				<div class="section-reports">
					<box class="shablabox-border">
						<div class="section-title">Phones</div>
						<div class="columns">
							<div class="column">
								Total Phones
							</div>
							<div class="column">
								Total Delivered
							</div>
							<div class="column">
								Processing Phones
							</div>
							<div class="column">
								phone not Repaired
							</div>
							<div class="column">
								<mdl-button>Add New Phones</mdl-button>
							</div>
						</div>
					</box>

				</div>
				<box class="shablabox-border">
					<div class="columns">
						<div class="column">
							Filter
						</div>
						<div class="column">
							<div class="select">
								<select>
									<option>Status:All</option>
									<option> options</option>
								</select>
							</div>
						</div>
						<div class="column">
							<div class="select">
								<select>
									<option>Date:Today</option>
									<option> options</option>
								</select>
							</div>
						</div>
						<div class="column">
							<div class="field is-grouped">
								<div class="control has-icons-left">
									<span class="icon is-small is-left"><i
											class="fa fa-search fa-xs searchIcon"></i></span>
									<input class="input" type="text" placeholder="Quick Search"/>
								</div>
							</div>
						</div>
					</div>
				</box>
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
								Added By
							</div>
							<div class="column">
								Phone Details
							</div>
							<div class="column">
								Phone Details
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
									Apple-iphone X-Speaker-Bad LCD
								</div>
								<div class="column is-inline-flex">
									<mdl-button>Preview</mdl-button>
									<mdl-button>Modify</mdl-button>
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
									Apple-iphone X-Speaker-Bad LCD
								</div>
								<div class="column is-inline-flex">
									<mdl-button>Preview</mdl-button>
									<mdl-button>Modify</mdl-button>
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
									Apple-iphone X-Speaker-Bad LCD
								</div>
								<div class="column is-inline-flex">
									<mdl-button>Preview</mdl-button>
									<mdl-button>Modify</mdl-button>
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
									Apple-iphone X-Speaker-Bad LCD
								</div>
								<div class="column is-inline-flex">
									<mdl-button>Preview</mdl-button>
									<mdl-button>Modify</mdl-button>
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
									Apple-iphone X-Speaker-Bad LCD
								</div>
								<div class="column is-inline-flex">
									<mdl-button>Preview</mdl-button>
									<mdl-button>Modify</mdl-button>
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
									Apple-iphone X-Speaker-Bad LCD
								</div>
								<div class="column is-inline-flex">
									<mdl-button>Preview</mdl-button>
									<mdl-button>Modify</mdl-button>
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
	import imageContainer from '../../../shapla/image/image'
	import box from '../../../shapla/box/box'
	import MdlButton from "../../../material-design-lite/button/mdlButton";

	export default {
		name: "Phones",
		components: {MdlButton, columns, column, imageContainer, box},
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
