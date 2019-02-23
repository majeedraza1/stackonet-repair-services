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
		<mdl-modal :active="openModel" @close="openModel = false">
			<div class="mdl-box">
				<list-item label="Name">{{activeTestimonial.name}}</list-item>
				<list-item label="Email">{{activeTestimonial.email}}</list-item>
				<list-item label="Phone">{{activeTestimonial.phone}}</list-item>
				<template v-if="activeTestimonial.status">
					<list-item label="Status">{{activeTestimonial.status}}</list-item>
				</template>
				<template v-else>
					<list-item label="Status">Pending</list-item>
				</template>
				<list-item label="Description">{{activeTestimonial.description}}</list-item>
			</div>
			<div slot="foot">
				<mdl-button type="raised" color="primary" @click="accept(activeTestimonial)">Accept</mdl-button>
				<mdl-button type="raised" color="accent" @click="reject(activeTestimonial)">Reject</mdl-button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import ListTable from '../../components/ListTable';
	import ListItem from '../../components/ListItem';
	import mdlModal from '../../material-design-lite/modal/mdlModal';
	import mdlButton from '../../material-design-lite/button/mdlButton';
	import {mapState} from 'vuex';

	export default {
		name: "Testimonial",
		components: {ListTable, mdlModal, ListItem, mdlButton},
		data() {
			return {
				rows: [],
				columns: [
					{key: 'name', label: 'Client Name'},
					{key: 'email', label: 'Email'},
					{key: 'phone', label: 'Phone'},
					{key: 'rating', label: 'Rating'},
					{key: 'status', label: 'Status'},
				],
				actions: [{key: 'edit', label: 'Edit'}, {key: 'delete', label: 'Delete'}],
				bulkActions: [],
				counts: {},
				index: -1,
				activeTestimonial: {
					id: '',
					full_name: '',
					email: '',
					description: '',
					phone: '',
					rating: null,
					status: '',
				},
				openModel: false,
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
					this.activeTestimonial = row;
					this.index = this.testimonials.indexOf(row);
					this.openModel = true;
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
			accept(testimonial) {
				this.updateStatus(testimonial, 'accept');
			},
			reject(testimonial) {
				this.updateStatus(testimonial, 'reject');
			},
			updateStatus(testimonial, status) {
				let self = this, $ = window.jQuery;
				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: {
						action: 'accept_reject_testimonial',
						id: testimonial.id,
						status: status,
					},
					success: function (response) {
						self.openModel = false;
						testimonial.status = status;
						self.testimonials[self.index] = testimonial;
						self.activeTestimonial = {};
						self.index = -1;
					},
					error: function (data) {
					}
				});
			}
		}
	}
</script>

<style scoped>

</style>
