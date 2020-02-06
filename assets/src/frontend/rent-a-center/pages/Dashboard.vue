<template>
	<div class="rent-a-center-dashboard-container">
		<columns>

			<column :tablet="6">
				<div class="section-reports">
					<box class="shablabox-border">
						<div class="section-title">Reports</div>
					</box>
				</div>
				<div class="section-latest-status">
					<div class="section-title">Latest Status</div>
					<columns multiline mobile>
						<column :mobile="12" :tablet="6">
							<status-box label="Processing" number="72" icon-background="red" icon-color="white"
										border-color="red">
								<icon-container large><i class="fa fa-2x fa-sign-out"></i></icon-container>
							</status-box>
						</column>
						<column :mobile="12" :tablet="6">
							<status-box label="Repairing" number="72" icon-background="green" icon-color="white"
										border-color="green">
								<icon-container large><i class="fa fa-2x fa-address-book-o"></i></icon-container>
							</status-box>
						</column>
						<column :mobile="12" :tablet="6">
							<status-box label="Not Repaired" number="72" icon-background="#ff8240" icon-color="white"
										border-color="#ff8240">
								<icon-container large><i class="fa fa-2x fa-bar-chart"></i></icon-container>
							</status-box>
						</column>
						<column :mobile="12" :tablet="6">
							<status-box label="Processing" number="72" icon-background="red" icon-color="white"
										border-color="red">
								<icon-container large><i class="fa fa-2x fa-sign-out"></i></icon-container>
							</status-box>
						</column>
					</columns>
				</div>
				<div class="section-total-damaged-devices">
					<box class="shablabox-border" id="box-color">
						<div class="section-title">Total Damaged Devices</div>
						<div class="damaged-devices-list">
							<div class="damaged-devices-list__item" v-for="num in [1,2,3,4,5]">
								<div class="damaged-devices-list__sl">1st</div>
								<div class="damaged-devices-list__avatar">
									<image-container square>
										<img class="is-rounded"
											 src="http://phonerepairsasap.test/wp-content/uploads/2017/03/1.jpg"
											 alt="">
									</image-container>
								</div>
								<div class="damaged-devices-list__name">
									Sayful Islam
								</div>
								<div class="damaged-devices-list__store-name">
									Stackonet
								</div>
								<div class="damaged-devices-list__phones">
									31 Phones
								</div>
							</div>
						</div>
					</box>
				</div>
			</column>
			<column :tablet="3">
				<div class="section-store-managers">
					<box>
						<div class="section-title">Store managers</div>
						<div class="store-managers-list">
							<div class="store-managers-list__item" v-for="num in [1,2,3,4,5]">
								<image-container square>
									<img class="is-rounded"
										 src="http://phonerepairsasap.test/wp-content/uploads/2017/03/1.jpg"
										 alt="">
								</image-container>
							</div>
							<div class="store-managers-list__item" style="z-index: 10">
								<div class="store-managers-list__icon">
									<icon-container large><i class="fa fa-plus"></i></icon-container>
								</div>
							</div>
						</div>
					</box>
				</div>
				<div class="section-recent-activity">
					<box>
						<div class="section-title">Recent Activities</div>
						<div class="recent-activities-list">
							<div class="recent-activities-list__item" v-for="num in [1,2,3,4,5]">
								<div class="recent-activities-list__avatar">
									<image-container square>
										<img class="is-rounded"
											 src="http://phonerepairsasap.test/wp-content/uploads/2017/03/1.jpg"
											 alt="">
									</image-container>
								</div>
								<div class="recent-activities-list__content">
									<div class="recent-activities-list__time">1h ago</div>
									<div class="recent-activities-list__title">Sayful Islam</div>
									<div class="recent-activities-list__description">Added 3 phones</div>
								</div>
							</div>
						</div>
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
	import StatusBox from '../../components/StatusBox'
	import iconContainer from 'shapla-icon-container'

	export default {
		name: "Dashboard",
		components: {columns, column, imageContainer, StatusBox, box, iconContainer},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SNACKBAR', {
				message: 'Check if working.'
			});
		}
	}
</script>
<style lang="scss">
	.rent-a-center-dashboard-container {
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
		.section-total-damaged-devices,
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

	.damaged-devices-list {
	}
</style>
