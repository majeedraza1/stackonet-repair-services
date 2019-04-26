<template>
	<div>
		<h1 class="wp-heading-inline">Survey</h1>
		<div class="clear"></div>

		<wp-list-table
			:loading="loading"
			:rows="rows"
			:columns="columns"
			:actions="actions"
			:bulk-actions="bulkActions"
			index="id"
			action-column="brand_name"
			:show-search="true"
			search-key="phones"
			:total-items="100"
			:total-pages="10"
			:per-page="20"
			:current-page="1"
		></wp-list-table>
	</div>
</template>

<script>
	import wpListTable from '../../wp/wpListTable.vue'

	export default {
		name: "SurveyListTable",
		components: {wpListTable},
		data() {
			return {
				loading: false,
				status: '',
				rows: [],
				columns: [
					{key: 'device_status', label: 'Status'},
					{key: 'full_address', label: 'Address'},
					{key: 'latitude', label: 'Latitude'},
					{key: 'longitude', label: 'Longitude'},
					{key: 'created_by', label: 'Created By'},
					{key: 'created_at', label: 'Date'},
				],
			}
		},
		computed: {
			actions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				}

				return [
					{key: 'edit', label: 'Edit'},
					{key: 'note', label: 'Note'},
					{key: 'view', label: 'View'},
					{key: 'trash', label: 'Trash'}
				];
			},
			bulkActions() {
				if ('trash' === this.status) {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
		}
	}
</script>

<style scoped>

</style>
