<template>
	<div class="stackonet-dashboard-map-list">
		<mdl-table
			:rows="places"
			:columns="columns"
			:actions="actions"
			@action:click="onActionClick"
		>
			<template slot="row-action" slot-scope="data">
				<span>| <a href="#" @click.prevent="onActionClick('assign_agents', data.row)">Assign Agents</a></span>
				<span v-if="data.row.support_ticket_id">
					| <a href="#" @click.prevent="onActionClick('view_ticket', data.row)">View Ticket</a>
				</span>
				<span v-else>
					| <a href="#" @click.prevent="onActionClick('create_ticket', data.row)">Create Ticket</a>
				</span>
			</template>
			<div slot="assigned_agents" slot-scope="data" class="column-assigned_agents">
				<span v-for="_agent in data.row.assigned_agents">{{_agent.display_name}}</span>
			</div>
		</mdl-table>
			<map-modal :active="showViewModal" :place="activePlace" @close="closeViewModal" :mode="modalMode"></map-modal>
		<modal :active="showAgentsModal" @close="closeAgentsModal" title="Support Agents">
			<template v-for="_agent in support_agents">
				<mdl-checkbox v-model="activePlace.assigned_users" :value="_agent.id">{{_agent.display_name}}
				</mdl-checkbox>
			</template>
			<template slot="foot">
				<mdl-button @click="updateSupportAgents">Update Agents</mdl-button>
			</template>
		</modal>
	</div>
</template>

<script>
	import {mapState, mapGetters} from 'vuex';
	import axios from 'axios';
	import modal from 'shapla-modal';
	import MdlTable from "../../../material-design-lite/data-table/mdlTable";
	import MapModal from "./MapModal";
	import MdlCheckbox from "../../../material-design-lite/checkbox/mdlCheckbox";
	import MdlButton from "../../../material-design-lite/button/mdlButton";

	export default {
		name: "MapListTable",
		components: {MdlButton, MdlCheckbox, MapModal, MdlTable, modal},
		data() {
			return {
				showViewModal: false,
				showAgentsModal: false,
				modalMode: 'view',
				activePlace: {},
				items: [],
				columns: [
					{key: 'title', label: 'Title'},
					{key: 'place_text', label: 'Search Text'},
					{key: 'base_datetime', label: 'Travel Date'},
					{key: 'travel_mode', label: 'Travel Mode'},
					{key: 'assigned_agents', label: 'Assigned Users'},
					{key: 'formatted_base_address', label: 'Base Address'},
				],
			}
		},
		computed: {
			...mapState(['places']),
			...mapGetters(['support_agents']),
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
			closeAgentsModal() {
				this.activePlace = {};
				this.showAgentsModal = false;
			},
			closeViewModal() {
				this.activePlace = {};
				this.showViewModal = false;
				this.modalMode = 'view';
			},
			updateSupportAgents() {
				axios
					.put(window.Stackonet.rest_root + '/map/' + this.activePlace.id + '/agent', {
						assigned_users: this.activePlace.assigned_users
					})
					.then(response => {
						this.$store.dispatch('refreshMapList');
						this.$root.$emit('show-notification', {
							message: 'Support agent has been updated successfully.',
							type: 'success',
							title: 'Success!',
						})
					})
					.catch(error => {
						console.log(error);
					});
				this.closeAgentsModal();
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

				if ('assign_agents' === action) {
					this.activePlace = item;
					this.showAgentsModal = true;
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

		.column-assigned_agents {
			display: flex;
			flex-direction: column;
			white-space: nowrap;
		}
	}
</style>
