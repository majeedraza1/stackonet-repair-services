<template>
	<div class="rent-a-center-dashboard-container">
		<columns>
			<column :tablet="3">
				<div class="user">
					<div class="user__avatar"></div>
					<div class="user__display_name"></div>
					<div class="user__role"></div>
				</div>

				<div class="action-menu">
					<columns mobile multiline centered>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-bar-chart"></i></icon>
								<span>Dashboard</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-cart-plus"></i></icon>
								<span>Orders</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-address-book-o"></i></icon>
								<span>Store Addresses</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-user-circle-o"></i></icon>
								<span>Account Details</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-mobile"></i></icon>
								<span>Phones</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-truck"></i></icon>
								<span>Track Status</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-envelope-o"></i></icon>
								<span>Messages</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-bell-o"></i></icon>
								<span>Notification</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-file-text-o"></i></icon>
								<span>Invoice</span>
							</div>
						</column>
						<column :mobile="6" :tablet="6">
							<div class="action-menu__item">
								<icon large><i class="fa fa-2x fa-sign-out"></i></icon>
								<span>Logout</span>
							</div>
						</column>
					</columns>
				</div>
			</column>

			<column :tablet="6">
				Middle Content
			</column>
			<column :tablet="3">
				Center Content
			</column>
		</columns>
	</div>
</template>

<script>
	import columns from '../../../shapla/columns/columns'
	import column from '../../../shapla/columns/column'
	import icon from '../../../shapla/icon/icon'
	import imageContainer from '../../../shapla/image/image'

	export default {
		name: "Dashboard",
		components: {columns, column, icon, imageContainer},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SNACKBAR', {
				message: 'Check if working.'
			});
		}
	}
</script>

<style lang="scss">
	.action-menu {
		&__item {
			align-items: center;
			border: 1px solid rgba(#000, 0.2);
			border-radius: 4px;
			display: flex;
			flex-direction: column;
			height: 100%;
			justify-content: center;
			padding: 5px;
			text-align: center;
			width: 100%;
		}
	}
</style>
