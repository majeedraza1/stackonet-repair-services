<template>

	<div
			class="demo-layout stackonet-dashboard mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">

		<header class="demo-header mdl-layout__header">
			<div class="mdl-layout__header-row">
				<span class="mdl-layout-title">Dashboard</span>
				<div class="mdl-layout-spacer"></div>
				<mdl-button type="icon" id="hdrbtn">
					<icon-container small><i class="fa fa-ellipsis-v" aria-hidden="true"></i></icon-container>
				</mdl-button>
				<ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="hdrbtn">
					<li class="mdl-menu__item"><a :href="home_url">Home</a></li>
					<li class="mdl-menu__item"><a :href="logout_url">Log out</a></li>
				</ul>
			</div>
		</header>

		<div class="demo-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">

			<div class="user">
				<div class="user__avatar">
					<image-container square>
						<img class="is-rounded"
							 src="http://phonerepairsasap.test/wp-content/uploads/2017/03/1.jpg"
							 alt="">
					</image-container>
				</div>
				<div class="user__display_name">Majeed Raza</div>
				<div class="user__role">Lead Developer</div>
			</div>
			<div class="action-menu">
				<columns mobile multiline centered>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-bar-chart"></i></icon-container>
							<span>Dashboard</span>
						</router-link>
					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/RentcenterOrders" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-cart-plus"></i></icon-container>
							<span>Orders</span>
						</router-link>

					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/store-addresses" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-address-book-o"></i></icon-container>
							<span>Store Addresses</span>
						</router-link>
					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/AccountDetails" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-user-circle-o"></i></icon-container>
							<span>Account Details</span>
						</router-link>

					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/Phones" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-mobile"></i></icon-container>
							<span>Phones</span>
						</router-link>

					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/TrackStatus" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-truck"></i></icon-container>
							<span>Track Status</span>
						</router-link>

					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/chat-app" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-envelope-o"></i></icon-container>
							<span>Messages</span>
						</router-link>

					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/Notifications" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-bell-o"></i></icon-container>
							<span>Notification</span>
						</router-link>
					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/Invoices" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-file-text-o"></i></icon-container>
							<span>Invoices</span>
						</router-link>

					</column>
					<column :mobile="6" :tablet="6">
						<router-link tag="div" to="/Logout" class="action-menu__item">
							<icon-container large><i class="fa fa-2x fa-sign-out"></i></icon-container>
							<span>Logout</span>
						</router-link>

					</column>
				</columns>
			</div>
		</div>

		<main class="mdl-layout__content">
			<div class="demo-content">
				<router-view></router-view>
			</div>
		</main>
	</div>

</template>

<script>
	import {mapState} from 'vuex';
	import {column, columns} from "shapla-columns";
	import {MaterialLayout} from '../../material-design-lite/layout/MaterialLayout';
	import {MaterialMenu} from '../../material-design-lite/menu/MaterialMenu';
	import ImageContainer from "../../shapla/image/image";
	import iconContainer from 'shapla-icon-container'

	export default {
		name: "RentCenter",
		components: {iconContainer, ImageContainer, columns, column},
		computed: {
			...mapState(['loading', 'snackbar']),
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			let dashboard = this.$el;
			new MaterialLayout(dashboard);
			new MaterialMenu(dashboard.querySelector('.mdl-menu'));
			new MaterialMenu(dashboard.querySelector('[for="accbtn"]'));
		},
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/layout/layout";
	@import "../../material-design-lite/menu/menu";

	.single article.page .entry-content {
		width: auto;
		padding: 0;
	}

	.demo-avatar {
		width: 48px;
		height: 48px;
		border-radius: 24px;
	}

	.demo-layout .mdl-layout__header .mdl-layout__drawer-button {
		color: rgba(0, 0, 0, 0.54);
	}

	.mdl-layout__drawer .avatar {
		margin-bottom: 16px;
	}

	.demo-drawer {
		border: none;
	}

	/* iOS Safari specific workaround */
	.demo-drawer .mdl-menu__container {
		// z-index: -1;
	}

	.demo-drawer .demo-navigation {
		z-index: -2;
	}

	/* END iOS Safari specific workaround */
	.demo-drawer .mdl-menu .mdl-menu__item {
		display: flex;
		align-items: center;
	}

	.demo-drawer-header {
		box-sizing: border-box;
		display: flex;
		flex-direction: column;
		justify-content: flex-end;
		padding: 16px;
		height: 151px;
		background-image: linear-gradient(-90deg, #f9a73b, #f58730);
		color: rgb(255, 255, 255);
	}

	.demo-avatar-dropdown {
		display: flex;
		position: relative;
		flex-direction: row;
		align-items: center;
		width: 100%;
	}

	.demo-navigation {
		flex-grow: 1;
	}

	.demo-layout .demo-navigation .mdl-navigation__link {
		display: flex !important;
		flex-direction: row;
		align-items: center;
		color: rgba(#000, 0.85);
		font-weight: 500;
		padding: 16px;

		&:hover,
		&.is-active {
			background-image: linear-gradient(-90deg, #f9a73b, #f58730);
			color: #fff;
		}
	}

	.demo-header .mdl-layout__header-row {
		background-image: linear-gradient(-90deg, #f9a73b, #f58730);
	}

	.demo-content {
		padding: 30px;
	}

	.mdl-icon-burger {
		color: #ffffff;

		display: inline-block;
		font: normal normal normal 14px/1 FontAwesome;
		font-size: inherit;
		text-rendering: auto;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;

		&:before {
			content: "\f0c9";
		}
	}

	.user-information-box {
		&__avatar {
			width: 48px;
			height: 48px;
			margin: auto;
		}
	}

	.mdl-navigation__link {
		cursor: pointer;
	}

	.action-menu__item.router-link-active {
		background-image: linear-gradient(90deg, #f9a73b, #f58730);
	}
</style>
