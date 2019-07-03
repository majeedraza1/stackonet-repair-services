<template>
	<div class="stackonet-dashboard-container">
		<div
			class="demo-layout stackonet-dashboard mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">

			<header class="demo-header mdl-layout__header">
				<div class="mdl-layout__header-row">
					<span class="mdl-layout-title">{{title}}</span>
					<div class="mdl-layout-spacer"></div>
					<mdl-button type="icon" id="menu-1">
						<icon small><i class="fa fa-ellipsis-v" aria-hidden="true"></i></icon>
					</mdl-button>
					<ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="menu-1">
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
							<button id="menu-2" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
								<icon>
									<i class="fa fa-caret-down" aria-hidden="true"></i>
								</icon>
								<span class="visuallyhidden">Accounts</span>
							</button>
							<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="menu-2">
								<li class="mdl-menu__item">Log out</li>
							</ul>
						</div>
					</div>
				</header>

				<nav class="demo-navigation mdl-navigation">
					<template v-for="_menuItem in menu_items">
						<template v-if="_menuItem.type && _menuItem.type === 'link'">
							<a
								:href="_menuItem.url"
								:target="_menuItem.target"
								class="mdl-navigation__link">{{_menuItem.label}}</a>
						</template>
						<template v-else>
							<router-link
								class="mdl-navigation__link"
								tag="div"
								:to="_menuItem.router"
								active-class="is-active">
								{{_menuItem.label}}
							</router-link>
						</template>
					</template>
				</nav>
			</div>

			<main class="mdl-layout__content">
				<div class="demo-content">
					<router-view></router-view>
				</div>
			</main>
		</div>
		<div class="stackonet-dashboard-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
		<mdl-snackbar></mdl-snackbar>
	</div>
</template>

<script>
	import {mapState, mapGetters} from 'vuex';
	import {MaterialLayout} from '../../material-design-lite/layout/MaterialLayout'
	import {MaterialMenu} from '../../material-design-lite/menu/MaterialMenu'
	import MdlButton from "../../material-design-lite/button/mdlButton";
	import MdlSpinner from "../../material-design-lite/spinner/mdlSpinner";
	import MdlSnackbar from "../../material-design-lite/snackbar/mdlSnackbar";
	import Icon from "../../shapla/icon/icon";
	import ImageContainer from "../../shapla/image/image";

	export default {
		name: "App",
		components: {MdlSnackbar, MdlSpinner, ImageContainer, MdlButton, Icon},
		mounted() {
			let el = this.$el;
			new MaterialLayout(el.querySelector('.stackonet-dashboard'));
			new MaterialMenu(el.querySelector('[for="menu-1"]'));
			new MaterialMenu(el.querySelector('[for="menu-2"]'));
		},
		computed: {
			...mapState(['loading', 'title']),
			...mapGetters(['display_name', 'user_email', 'avatar_url', 'logout_url', 'home_url']),
			menu_items() {
				return StackonetDashboard.menuItems;
			},
		}
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/layout/layout";
	@import "../../material-design-lite/menu/menu";
	@import "dashboard";

	body.has-shapla-modal {
		.mdl-layout__drawer,
		.mdl-layout__header {
			z-index: -1;
		}

		#wpadminbar {
			display: none;
		}
	}

	.stackonet-dashboard-container {
		position: fixed;
		display: flex;
		height: 100%;
		width: 100%;

		.stackonet-dashboard-loader.is-active {
			display: flex;
			z-index: 100000;
			width: 100%;
			height: 100%;
			position: fixed;
			background: rgba(#fff, 0.6);
			top: 0;
			left: 0;
			justify-content: center;
			align-items: center;
		}

		.shapla-modal-card {
			// max-height: calc(100vh - 220px);
		}
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
