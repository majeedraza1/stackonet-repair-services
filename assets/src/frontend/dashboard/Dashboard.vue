<template>
	<div class="stackonet-dashboard">
		<div class="box">
			<div class="columns">
				<div class="column is-9" id="welcome-header"><strong>Welcome to phone repaires ASAP dashboard</strong>
				</div>
				<div class="column is-3" id="header-content-right">
					<i id="icon" class="fa fa-bell"></i>
					<figure class="image is-32x32">
						<img class="is-rounded"
							 src="https://previews.customer.envatousercontent.com/files/263349060/80x80_leatherman.png"
							 alt="">
					</figure>
					<div class="content">
						<div class="dropdown is-hoverable">
							<div class="dropdown-name"><span>John Smith</span></div>
							<div class="dropdown-menu" id="dropdown-menu" role="menu">
								<div class="dropdown-content">
									<div class="dropdown-item">
										<ul class="dropdown">
											<li>sim</li>
										</ul>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>

			</div>
		</div>

		<columns id="body-content">

			<column :tablet="3">
				<div class="user-info-box">
					<div class="user-info-box__avatar">
						<image-container>
							<img class="is-rounded"
								 :src="avatar_url"
								 alt="Image">
						</image-container>
					</div>
					<div class="user-info-box__content">
						<div class="user-info-box__greeting">
							<strong>Welcome</strong>
						</div>
						<div class="user-info-box__display_name">
							{{display_name}}
						</div>
					</div>
				</div>
			</column>

			<column :tablet="9">

				<columns multiline mobile>

					<column :mobile="6" :tablet="3" class="is-flex">
						<router-link class="stackonet-dashboard-box" tag="div" to="/ticket">
							<icon large><i class="fa fa-cog fa-2x"></i></icon>
							Support
						</router-link>
					</column>

					<column :mobile="6" :tablet="3" class="is-flex">
						<router-link class="stackonet-dashboard-box" tag="div" to="/survey">
							<icon large><i class="fa fa-cog fa-2x"></i></icon>
							Survey
						</router-link>
					</column>

					<column :mobile="6" :tablet="3" class="is-flex">
						<router-link class="stackonet-dashboard-box" tag="div" to="/spot-appointment">
							<icon large><i class="fa fa-cog fa-2x"></i></icon>
							Appointment
						</router-link>
					</column>

					<column :mobile="6" :tablet="3" class="is-flex">
						<router-link class="stackonet-dashboard-box" tag="div" to="/checkout-analysis">
							<icon large><i class="fa fa-cog fa-2x"></i></icon>
							Checkout Analysis
						</router-link>
					</column>

				</columns>

			</column>
		</columns>

		<div class="stackonet-dashboard-content">
			<router-view></router-view>
		</div>

	</div>

</template>

<script>
	import {mapGetters} from 'vuex';
	import Icon from "../../shapla/icon/icon";
	import Columns from "../../shapla/columns/columns";
	import Column from "../../shapla/columns/column";
	import ImageContainer from "../../shapla/image/image";

	export default {
		name: "Dashboard",
		components: {ImageContainer, Column, Columns, Icon},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
		},
		computed: {
			...mapGetters(['display_name', 'user_email', 'avatar_url']),
		}
	}
</script>
<style lang="scss">
	.user-info-box {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		height: 65px;

		&__avatar {
			width: 48px;
			height: 48px;
			margin-right: 10px;
		}
	}

	.stackonet-dashboard {

		&-content {
			padding: 30px;
		}

		.stackont-single-support-ticket-container {
			margin-top: 0;
			margin-bottom: 0;
		}

		.stackonet-survey-form {
			margin: auto;
			max-width: 600px;
		}

		&-box {
			background-image: linear-gradient(90deg, #f9a73b, #f58730);
			border: none;
			border-radius: 10px;
			box-shadow: 0 8px 15px rgba(#000, .1);
			cursor: pointer;
			color: #fff;
			display: flex;
			align-items: center;
			font-size: 16px;
			height: 65px;
			text-align: center;
			text-decoration: none;
			justify-content: center;
			padding: 1rem 1.5rem;
			width: 100%;
			transition: all 300ms ease-in-out;

			&:hover {
				background-image: linear-gradient(90deg, #f58730, #f9a73b);
			}
		}
	}


	#body-content {
		background-image: linear-gradient(90deg, #f58730, #f9a73b);
		padding: 15px;
		margin: auto
	}

	#icon {
		font-size: 20px;
		color: #f58730;
		padding: 7px 60px 0 0;
	}

	#header-content-right {
		display: flex;
		justify-content: flex-end;
	}

	.dropdown-name {
		margin-left: 20px;
		margin-top: 5px;
	}

	.content-name {
		padding-left: 80px;
		margin-top: -50px;
	}

	#welcome-header {
		display: flex;
		justify-content: center;
	}
</style>
