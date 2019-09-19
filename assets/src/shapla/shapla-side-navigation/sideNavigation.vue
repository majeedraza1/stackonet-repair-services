<template>
	<div :class="navClasses" :style="navStyle">
		<slot name="header">
			<div class="shapla-sidenav__header" v-if="showHeader">
				<delete-icon @click="closeNav"></delete-icon>
			</div>
		</slot>
		<div class="shapla-sidenav__content">
			<slot></slot>
		</div>
	</div>
</template>

<script>
    import deleteIcon from "shapla-delete";

    export default {
        name: "sideNav",

        components: {deleteIcon},

        props: {
            active: {type: Boolean, default: true},
            showHeader: {type: Boolean, default: true},
            navWidth: {type: String, default: '250px'},
            position: {type: String, default: 'left'},
        },

        methods: {
            closeNav() {
                this.$emit('close');
            },
        },
        computed: {
            navClasses() {
                return {
                    'shapla-sidenav': true,
                    'shapla-sidenav--left': this.position === 'left',
                    'shapla-sidenav--right': this.position === 'right',
                    'is-active': this.active
                };
            },
            navStyle() {
                let navStyle = {
                    width: this.navWidth,
                };

                if (!this.active) {
                    if (this.position === 'right') {
                        navStyle['transform'] = `translateX(${this.navWidth})`;
                    } else {
                        navStyle['transform'] = `translateX(-${this.navWidth})`;
                    }
                }

                return navStyle;
            }
        }
    }
</script>

<style lang="scss">

	.shapla-sidenav {
		display: flex;
		flex-direction: column;
		flex-wrap: nowrap;

		height: 100vh;
		max-height: 100vh;

		position: absolute;
		top: 0;
		left: 0;

		box-sizing: border-box;
		background: #fafafa;

		transform: translateX(-400px);
		transform-style: preserve-3d;
		will-change: transform;

		transition: 0.5s;
		transition-duration: 200ms;
		transition-timing-function: cubic-bezier(.4, 0, .2, 1);

		overflow: visible;

		z-index: 1;

		&.is-active {
			border-right: 1px solid #e0e0e0;
			box-shadow: 0 2px 2px 0 rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .2), 0 1px 5px 0 rgba(0, 0, 0, .12);
			transform: translateX(0);
		}

		&--right {
			left: auto;
			right: 0;
		}

		&__content {
			height: 100%;
			overflow-x: hidden;
			overflow-y: auto;
			padding: 0 1rem;
		}

		.shapla-delete-icon {
			// position: absolute;
			right: 14px;
			top: 14px;
		}
	}
</style>
