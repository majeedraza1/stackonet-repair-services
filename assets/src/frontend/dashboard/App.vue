<template>
	<div class="stackonet-dashboard-container">

		<dashboard-layout :title="title" :activate-side-nav="activateSideNav" :user-display-name="display_name"
						  :avatar-url="avatar_url" @open:sidenav="activateSideNav = true"
						  @close:sidenav="activateSideNav = false">

			<router-view></router-view>

			<template v-slot:navbar-end>
				<dropdown :hoverable="false" :right="true">
					<template v-slot:trigger>
						<div class="shapla-icon shapla-icon-button">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
								<path d="M0 0h24v24H0z" fill="none"/>
								<path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"
									  fill="currentColor"/>
							</svg>
						</div>
					</template>
					<a class="dropdown-item" :href="home_url">Home</a>
					<a class="dropdown-item" :href="logout_url">Log out</a>
				</dropdown>
			</template>

			<template v-slot:sidenav-menu>
				<ul class="sidenav-list">
					<li class="sidenav-list__item" v-for="_menuItem in menu_items">
						<a class="sidenav-list__link" :class="{'is-active':$route.name === _menuItem.routerName}"
						   href="#" @click.prevent="handleMenuItemClick(_menuItem)">{{_menuItem.label}}</a>
					</li>
				</ul>
			</template>
		</dashboard-layout>
		<spinner :active="loading"></spinner>
		<notification ref="notify"></notification>
		<confirm-dialog/>
		<svg-icon></svg-icon>
	</div>
</template>

<script>
	import {mapState, mapGetters} from 'vuex';
	import notification from 'shapla-notifications';
	import spinner from "shapla-spinner";
	import {ConfirmDialog} from "shapla-confirm-dialog";
	import dashboardLayout from 'shapla-dashboard-layout';
	import dropdown from 'shapla-dropdown';
	import Icon from "../../shapla/icon/icon";
	import SvgIcon from "../../svg-icon";

	export default {
		name: "App",
		components: {dashboardLayout, dropdown, SvgIcon, ConfirmDialog, spinner, Icon, notification},
		data() {
			return {
				activateSideNav: false,
			}
		},
		computed: {
			...mapState(['loading', 'title']),
			...mapGetters(['display_name', 'user_email', 'avatar_url', 'logout_url', 'home_url']),
			menu_items() {
				return StackonetDashboard.menuItems;
			},
		},
		methods: {
			handleMenuItemClick(menuItem) {
				this.activateSideNav = false;
				if (menuItem.type && menuItem.type === 'link') {
					window.open(menuItem.url, menuItem.target);
				} else if (this.$route.name !== menuItem.routerName) {
					this.$router.push({name: menuItem.routerName});
				}
			}
		}
	}
</script>

<style lang="scss">
	.admin-bar {
		.shapla-dashboard {
			top: 32px;
		}
	}
</style>
