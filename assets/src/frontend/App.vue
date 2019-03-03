<template>
	<div class="repair-services-container" :class="containerClasses">
		<div class="repair-services-content">
			<router-view></router-view>
		</div>
		<cart></cart>
		<div class="repair-services-loader" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>
	</div>
</template>

<script>
	import mdlSpinner from '../material-design-lite/spinner/mdlSpinner.vue';
	import cart from './views/cart.vue';
	import {mapState} from 'vuex';
	import {mapGetters} from 'vuex';

	export default {
		name: 'App',
		components: {mdlSpinner, cart},
		data() {
			return {}
		},
		computed: {
			...mapState(['loading', 'showCart', 'windowWidth', 'toggleCart']),
			...mapGetters(['isLargeScreen', 'isSmallScreen', 'isSmallScreenActive', 'isLargeScreenActive']),
			containerClasses() {
				return {
					'is-cart-active': this.isLargeScreenActive || this.isSmallScreenActive,
					'is-small-screen': this.isSmallScreen,
					'is-small-screen-active': this.isSmallScreenActive,
				}
			}
		},
		mounted() {
			let self = this;

			self.$store.commit('SET_SHOW_CART', false);
			let body = document.querySelector('body');
			if (self.showCart) {
				body.classList.add('page-repair-services-cart-active');
			} else {
				body.classList.remove('page-repair-services-cart-active');
			}

			self.$store.commit('SET_WINDOW_WIDTH', window.innerWidth);
			window.addEventListener('resize', () => {
				self.$store.commit('SET_WINDOW_WIDTH', window.innerWidth);
			});
			window.addEventListener('orientationchange', () => {
				self.$store.commit('SET_WINDOW_WIDTH', window.innerWidth);
			});

			let icon = document.querySelector('.my-cart-toggle-icon');
			if (icon) {
				icon.addEventListener('click', () => {
					self.$store.commit('SET_TOGGLE_CART', !self.toggleCart);
				});
			}
		}
	}
</script>

<style lang="scss">
	.repair-services-container {
		background: #eff2f5;
		position: relative;
		transition: all 300ms ease-in-out;

		&.is-cart-active:not(.is-small-screen) {
			margin-right: 355px;
		}
	}

	.repair-services-content {
	}

	.repair-services-loader {
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		display: none;
		align-items: center;
		justify-content: center;
		background: rgba(#fff, 0.5);

		&.is-active {
			display: flex;
		}
	}

	.page-repair-services {
		.content-container {
			background-color: #eff2f5;
		}
	}
</style>
