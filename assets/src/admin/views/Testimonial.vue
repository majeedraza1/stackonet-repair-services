<template>
	<div class="client-testimonials">
		<h1 class="wp-heading-inline">Testimonials</h1>
		<div class="clear"></div>
		<list-table
				:columns="columns"
				:rows="testimonials"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="name"
				@action:click="onActionClick"
				@bulk:click="onBulkAction"
		></list-table>
	</div>
</template>

<script>
	import ListTable from '../../components/ListTable';
	import {mapState} from 'vuex';

	export default {
		name: "Testimonial",
		components: {ListTable},
		data() {
			return {
				rows: [],
				columns: [
					{key: 'name', label: 'Client Name'},
					{key: 'email', label: 'Email'},
					{key: 'phone', label: 'Phone'},
				],
				actions: [{key: 'edit', label: 'Edit'}, {key: 'delete', label: 'Delete'}],
				bulkActions: [],
				counts: {},
			}
		},
		computed: {
			...mapState(['loading', 'testimonials'])
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.testimonials.length) {
				this.$store.dispatch('fetch_testimonials');
			}
		},
		methods: {
			onActionClick(action, row) {
				if ('edit' === action) {
				} else if ('trash' === action) {
					if (confirm('Are you sure to move this item to trash?')) {
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore this item?')) {
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete this item permanently?')) {
					}
				}
			},
			onBulkAction(action, items) {
				if ('trash' === action) {
					if (confirm('Are you sure to trash all selected items?')) {
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete all selected items permanently?')) {
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore all selected items?')) {
					}
				}
			},
		}
	}
</script>

<style scoped>

</style>
