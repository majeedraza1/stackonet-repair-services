<template>
	<div class="stackonet-dashboard-map-list">
		<mdl-table
			:rows="places"
			:columns="columns"
			:actions="actions"
			@action:click="onActionClick"
		>
			<template slot="row-action" slot-scope="data">
				<span v-if="data.row.support_ticket_id">
					|
					<a href="#" @click.prevent="onActionClick('view_ticket', data.row)">View Ticket</a>
				</span>
				<span v-else>
					|
					<a href="#" @click.prevent="onActionClick('create_ticket', data.row)">Create Ticket</a>
				</span>
			</template>
		</mdl-table>
		<map-modal :active="showViewModal" :place="activePlace" @close="closeViewModal" :mode="modalMode"></map-modal>
	</div>
</template>

<script>
	import {mapState} from 'vuex';
	import axios from 'axios';
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
					// {key: 'create_ticket', label: 'Create Ticket'},
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

				if ('create_ticket' === action) {
					this.create_ticket(item.id);
				}

				if ('view_ticket' === action) {
					this.$router.push({name: 'SingleSupportTicket', params: {id: item.support_ticket_id}});
				}
			},
			create_ticket(map_id) {
				axios
					.post(window.Stackonet.rest_root + '/map/' + map_id + '/support-ticket')
					.then(response => {
						this.$store.dispatch('refreshMapList');
						this.$root.$emit('show-notification', {
							message: 'Support ticket has been created successfully.',
							type: 'success',
							title: 'Success!',
						})
					})
					.catch(error => {
						console.log(error);
					});
			}
		}
	}
</script>

<style lang="scss">
	.stackonet-dashboard-map-list {
		@media screen and (min-width: 800px) {
			th.column-primary,
			td.column-primary {
				width: 220px;
			}
		}
	}
</style>
