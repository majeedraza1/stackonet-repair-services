<template>

</template>

<script>
	export default {
		name: "Dashboard",
		mounted() {
			this.$router.push(this.first_item);
		},
		computed: {
			menu_items() {
				return StackonetDashboard.menuItems;
			},
			first_item() {
				return this.menu_items[0] && this.menu_items[0].router ? this.menu_items[0].router : '/';
			}
		}
	}
</script>

<style lang="scss">

</style>
