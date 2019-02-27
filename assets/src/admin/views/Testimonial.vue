<template>
	<div class="client-testimonials">
		<h1 class="wp-heading-inline">Testimonials</h1>
		<div class="clear"></div>
		<wp-list-table
				:loading="loading"
				:columns="columns"
				:rows="testimonials"
				:actions="actions"
				:bulk-actions="bulkActions"
				action-column="name"
				:current-page="currentPage"
				:per-page="perPage"
				:total-items="totalItems"
				:statuses="statuses"
				:show-search="false"
				@action:click="onActionClick"
				@bulk:apply="onBulkAction"
				@status:change="changeStatus"
				@pagination="paginate"
		></wp-list-table>
		<mdl-modal :active="openModel" @close="openModel = false" title="Testimonial">
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
			<div>
				<h4>Client Image</h4>
				<background-image v-model="client_image"></background-image>
			</div>
			<div slot="foot">
				<mdl-button type="raised" color="primary" @click="updateStatus(activeTestimonial, 'accept')">Accept
				</mdl-button>
				<mdl-button type="raised" color="accent" @click="updateStatus(activeTestimonial, 'reject')">Reject
				</mdl-button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import wpListTable from '../../wp/wpListTable';
	import ListItem from '../../components/ListItem';
	import BackgroundImage from '../../components/BackgroundImage';
	import mdlModal from '../../material-design-lite/modal/mdlModal';
	import mdlButton from '../../material-design-lite/button/mdlButton';
	import {mapState} from 'vuex';

	export default {
		name: "Testimonial",
		components: {wpListTable, mdlModal, ListItem, mdlButton, BackgroundImage},
		data() {
			return {
				client_image: {},
				default_statuses: [
					{key: 'all', label: 'All', count: 0, active: true},
					{key: 'accept', label: 'Accepted', count: 0, active: false},
					{key: 'reject', label: 'Rejected', count: 0, active: false},
					{key: 'pending', label: 'Pending', count: 0, active: false},
					{key: 'trash', label: 'Trash', count: 0, active: false},
				],
				activeStatus: 'all',
				columns: [
					{key: 'name', label: 'Client Name'},
					{key: 'email', label: 'Email'},
					{key: 'phone', label: 'Phone'},
					{key: 'rating', label: 'Rating'},
					{key: 'status', label: 'Status'},
				],
				currentPage: 1,
				perPage: 20,
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
			...mapState(['loading', 'testimonials', 'testimonialsCounts']),
			statuses() {
				let _status = [], self = this;
				this.default_statuses.forEach(status => {
					status.count = self.testimonialsCounts[status.key];
					_status.push(status);
				});

				return _status;
			},
			activeStatus_() {
				let active = {};
				this.statuses.forEach(status => {
					if (status.active === true) {
						active = status;
					}
				});

				return active;
			},
			totalItems() {
				return this.testimonialsCounts[this.activeStatus_.key];
			},
			actions() {
				if (this.activeStatus === 'trash') {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'edit', label: 'Edit'}, {key: 'trash', label: 'Trash'}]
				}
			},
			bulkActions() {
				if (this.activeStatus === 'trash') {
					return [{key: 'restore', label: 'Restore'}, {key: 'delete', label: 'Delete Permanently'}];
				} else {
					return [{key: 'trash', label: 'Move to Trash'}];
				}
			},
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.testimonials.length) {
				this.get_items();
			}
		},
		methods: {
			get_items() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				window.jQuery.ajax({
					method: 'GET',
					url: stackonetSettings.root + '/testimonials',
					data: {
						per_page: self.perPage,
						page: self.currentPage,
						status: self.activeStatus,
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_TESTIMONIALS', response.data.items);
							self.$store.commit('SET_TESTIMONIALS_COUNTS', response.data.counts);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			changeStatus(status) {
				this.currentPage = 1;
				this.activeStatus = status.key;

				this.default_statuses.forEach(element => {
					element.active = false;
				});

				status.active = true;

				this.get_items();
			},
			paginate(page) {
				this.currentPage = page;
				this.get_items();
			},
			update_item(id, data) {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: "PUT",
					url: stackonetSettings.root + '/testimonials/' + id,
					data: data,
					success: function () {
						self.get_items();
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			trash_item(item) {
				this.update_item(item.id, {status: 'trash'});
			},
			restore_item(item) {
				this.update_item(item.id, {status: 'restore'});
			},
			delete_item(item) {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: "DELETE",
					url: stackonetSettings.root + '/testimonials/' + item.id,
					success: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.get_items();
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			onActionClick(action, row) {
				if ('edit' === action) {
					this.activeTestimonial = row;
					this.index = this.testimonials.indexOf(row);
					this.openModel = true;
				} else if ('trash' === action) {
					if (confirm('Are you sure to move this item to trash?')) {
						this.trash_item(row);
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore this item?')) {
						this.restore_item(row);
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete this item permanently?')) {
						this.delete_item(row);
					}
				}
			},
			trash_items(action, ids) {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: "DELETE",
					url: stackonetSettings.root + '/testimonials/batch/trash',
					data: {ids: ids},
					success: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.get_items();
						action = '-1';
						ids = [];
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			restore_items(action, ids) {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: "DELETE",
					url: stackonetSettings.root + '/testimonials/batch/restore',
					data: {ids: ids},
					success: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.get_items();
						action = '-1';
						ids = [];
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			delete_items(action, ids) {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: "DELETE",
					url: stackonetSettings.root + '/testimonials/batch/delete',
					data: {ids: ids},
					success: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.get_items();
						action = '-1';
						ids = [];
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			onBulkAction(action, items) {
				if ('trash' === action) {
					if (confirm('Are you sure to trash all selected items?')) {
						this.trash_items(action, items);
					}
				} else if ('delete' === action) {
					if (confirm('Are you sure to delete all selected items permanently?')) {
						this.delete_items(action, items);
					}
				} else if ('restore' === action) {
					if (confirm('Are you sure to restore all selected items?')) {
						this.restore_items(action, items);
					}
				}
			},
			updateStatus(testimonial, status) {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: "POST",
					url: ajaxurl,
					data: {
						action: 'accept_reject_testimonial',
						id: testimonial.id,
						status: status,
					},
					success: function () {
						self.openModel = false;
						testimonial.status = status;
						self.testimonials[self.index] = testimonial;
						self.activeTestimonial = {};
						self.index = -1;
						self.get_items();
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			}
		}
	}
</script>

<style scoped>

</style>
