<template>
	<div class="repair-services-container" :class="containerClasses">
		<div class="repair-services-content">
			<router-view></router-view>
		</div>
		<cart></cart>
		<spinner :active="loading"></spinner>
	</div>
</template>

<script>
    import cart from './cart';
    import {mapGetters, mapState} from 'vuex';
    import spinner from "shapla-spinner";

    export default {
        name: 'App',
        components: {spinner, cart},
        data() {
            return {}
        },
        computed: {
            ...mapState(['loading', 'showCart', 'windowWidth', 'toggleCart', 'devices']),
            ...mapGetters(['isLargeScreen', 'isSmallScreen', 'isSmallScreenActive', 'isLargeScreenActive', 'containerClasses'])
        },
        mounted() {
            let self = this, $ = window.jQuery;

            let uri = window.location.href.split('?');
            if (uri.length === 2) {
                let vars = uri[1].split('&');
                let getVars = {};
                vars.forEach(function (v) {
                    let tmp = v.split('=');
                    if (tmp.length === 2)
                        getVars[tmp[0]] = tmp[1];
                });

                if (getVars.device && getVars.model) {
                    let device = this.devices.find(device => {
                        return device.id === decodeURIComponent(getVars.device);
                    });

                    if (device) {
                        let model = device.device_models.find(model => {
                            return model.title === decodeURIComponent(getVars.model);
                        });

                        if (model) {
                            this.$store.commit('SET_DEVICE', device);
                            this.$store.commit('SET_DEVICES_MODELS', device.device_models);
                            this.$store.commit('SET_DEVICE_MODEL', model);
                            this.$store.commit('SET_DEVICES_COLORS', model.colors);
                            this.$router.push('/device-color');
                        }
                    }
                }
            }

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

            // Find group
            let group = $(this.$el).closest('.stackonet_repair_services_container');
            if (group) {
                group = group.data('group');
                if (group) {
                    group = group.split(',');
                    self.$store.commit('SET_GROUP', group);
                }
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
		margin: 15px;
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
