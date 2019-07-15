<template>
	<div>
		<mdl-table
			:rows="places"
			:columns="columns"
			:actions="actions"
		></mdl-table>
	</div>
</template>

<script>
	import axios from 'axios';
	import {mapState} from 'vuex';
	import MdlTable from "../../../material-design-lite/data-table/mdlTable";

	export default {
		name: "MapListTable",
		components: {MdlTable},
		data() {
			return {
				items: [],
				columns: [
					{key: 'title', label: 'Title'},
					{key: 'place_text', label: 'Search Text'},
					{key: 'base_datetime', label: 'Travel Date'},
					{key: 'travel_mode', label: 'Travel Mode'},
					{key: 'formatted_base_address', label: 'Base Address'},
				],
			}
		},
		computed: {
			...mapState(['places']),
			actions() {
				return [
					{key: 'view', label: 'View'},
				];
			}
		},
		mounted() {
			this.$store.dispatch('refreshMapList')
		},
		methods: {}
	}
</script>

<style scoped>

</style>
