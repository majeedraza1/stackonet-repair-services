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
</style>
