<template>
	<div>
		<mdl-table
			:rows="places"
			:columns="columns"
			:actions="actions"
			@action:click="onActionClick"
		></mdl-table>
		<map-modal :active="showViewModal" :place="activePlace"  @close="closeViewModal" :mode="modalMode"></map-modal>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import modal from 'shapla-modal';
	import MdlTable from "../../../material-design-lite/data-table/mdlTable";
	import MapModal from "./MapModal";

	export default {
		name: "MapListTable",
		components: {MapModal, MdlTable, modal},
		data() {
			return {
				showViewModal: false,
				modalMode: 'view',
				activePlace: {},
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
					{key: 'edit', label: 'Edit'},
					{key: 'view', label: 'View'},
				];
			}
		},
		mounted() {
			this.$store.dispatch('refreshMapList');
		},
		methods: {
			closeViewModal() {
				this.activePlace = {};
				this.showViewModal = false;
				this.modalMode = 'view';
			},
			onActionClick(action, item) {
				if ('view' === action) {
					this.modalMode = 'view';
					this.activePlace = item;
					this.showViewModal = true;
				}
				if ('edit' === action) {
					this.modalMode = 'edit';
					this.activePlace = item;
					this.showViewModal = true;
				}
			}
		}
	}
</script>

<style scoped>

</style>
