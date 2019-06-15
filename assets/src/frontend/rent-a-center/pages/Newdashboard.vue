<template>
	<div
		class="demo-layout stackonet-dashboard mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">

		<header class="demo-header mdl-layout__header">
			<div class="mdl-layout__header-row">
				<span class="mdl-layout-title">Dashboard</span>
				<div class="mdl-layout-spacer"></div>
				<mdl-button type="icon" id="hdrbtn">
					<icon small><i class="fa fa-ellipsis-v" aria-hidden="true"></i></icon>
				</mdl-button>
				<ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="hdrbtn">
					<li class="mdl-menu__item"><a :href="home_url">Home</a></li>
					<li class="mdl-menu__item"><a :href="logout_url">Log out</a></li>
				</ul>
			</div>
		</header>

		<div class="demo-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">

			<header class="demo-drawer-header">
				<div class="user-information-box">
					<div class="user-information-box__avatar">
						<image-container square>
							<img :src="avatar_url" class="is-rounded" :alt="display_name">
						</image-container>
					</div>
					<div class="demo-avatar-dropdown">
						<span>{{display_name}}</span>
						<div class="mdl-layout-spacer"></div>
						<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
							<icon>
								<i class="fa fa-caret-down" aria-hidden="true"></i>
							</icon>
							<span class="visuallyhidden">Accounts</span>
						</button>
						<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
							<li class="mdl-menu__item">Log out</li>
						</ul>
					</div>
				</div>
			</header>

			<nav class="demo-navigation mdl-navigation">
				<router-link class="mdl-navigation__link" tag="div" to="/ticket" active-class="is-active">Support
				</router-link>
				<router-link class="mdl-navigation__link" tag="div" to="/survey" active-class="is-active">Survey
				</router-link>
				<router-link class="mdl-navigation__link" tag="div" to="/spot-appointment" active-class="is-active">
					Appointment
				</router-link>
				<router-link class="mdl-navigation__link" tag="div" to="/carrier-stores" active-class="is-active">
					Carrier Stores
				</router-link>
				<router-link class="mdl-navigation__link" tag="div" to="/checkout-analysis" active-class="is-active">
					Checkout Analysis
				</router-link>
				<a href="https://analytics.google.com/analytics/web/" target="_blank" class="mdl-navigation__link">Google Analytics</a>
			</nav>
		</div>

		<main class="mdl-layout__content">
			<div class="demo-content">
				<router-view></router-view>
			</div>
		</main>
	</div>
</template>

<script>
	import {mapGetters} from 'vuex';
	import {MaterialLayout} from '../../../material-design-lite/layout/MaterialLayout'
	import {MaterialMenu} from '../../../material-design-lite/menu/MaterialMenu'
	import Icon from "../../../shapla/icon/icon";
	import MdlButton from "../../../material-design-lite/button/mdlButton";
	import ImageContainer from "../../../shapla/image/image";

	export default {
		name: "App",
		components: {ImageContainer, MdlButton, Icon},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			let dashboard = this.$el;
			new MaterialLayout(dashboard);
			new MaterialMenu(dashboard.querySelector('.mdl-menu'));
			new MaterialMenu(dashboard.querySelector('[for="accbtn"]'));
		},
		computed: {
			...mapGetters(['display_name', 'user_email', 'avatar_url', 'logout_url', 'home_url']),
		}
	}
</script>

<style lang="scss">
	@import "../../../material-design-lite/layout/layout";
	@import "../../../material-design-lite/menu/menu";
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
</style>
