<template>
	<div>
		<wp-status-list :statuses="statuses" @change="handleStatusChange"></wp-status-list>
		<mdl-table
			:rows="items"
			:columns="columns"
			:actions="actions"
			action-column="object_name"
			@action:click="onActionClick"

		>
			<template slot="icon" slot-scope="item">
				<img :src="item.row.icon" alt="" width="48" height="48">
			</template>
			<template slot="online" slot-scope="item">
				<span class="status--online" v-if="item.row.online">Online</span>
				<span class="status--offline" v-else>Offline</span>
			</template>
		</mdl-table>
		<modal :active="openEditModal" @close="closeEditModal" type="box">
			<template v-if="Object.keys(activeItem).length">
				<div class="shapla-box">
					<animated-input label="Object Id" v-model="activeItem.object_id"></animated-input>
					<animated-input label="Display Name" v-model="activeItem.object_name"></animated-input>
					<div>
						<div style="text-align: left">Avatar</div>
						<featured-image
							:image="activeItem.avatar"
							@input="updateFeaturedImage"
							:images="images"
						></featured-image>
					</div>
					<big-button :fullwidth="true" text="Update" @click="updateObject"></big-button>
				</div>
			</template>
		</modal>
	</div>
</template>

<script>
    import modal from 'shapla-modal'
    import {CrudMixin} from "../../components/CrudMixin";
    import mdlTable from "../../material-design-lite/data-table/mdlTable";
    import WpStatusList from "../../wp/wpStatusList";
    import AnimatedInput from "../../components/AnimatedInput";
    import BigButton from "../../components/BigButton";
    import DropzoneUploader from "../components/DropzoneUploader";
    import FeaturedImage from "../../components/FeaturedImage";

    export default {
        name: "TrackingUsers",
        components: {FeaturedImage, DropzoneUploader, BigButton, AnimatedInput, WpStatusList, mdlTable, modal},
        mixins: [CrudMixin],
        data() {
            return {
                items: [],
                status: 'all',
                statuses: [],
                columns: [
                    {key: 'object_name', label: 'Title'},
                    {key: 'icon', label: 'Icon'},
                    {key: 'online', label: 'Status'},
                    {key: 'formatted_address', label: 'Last location'},
                ],
                images: [],
                openEditModal: false,
                activeItem: {}
            }
        },
        computed: {
            actions() {
                if ('trash' === this.status) {
                    return [
                        {key: 'restore', label: 'Restore'},
                        {key: 'delete', label: 'Delete permanently'},
                    ];
                }
                return [
                    {key: 'edit', label: 'Edit'},
                    {key: 'trash', label: 'Trash'},
                ];
            }
        },
        mounted() {
            this.getTrackableObjects();
            this.getLogos();
        },
        methods: {
            closeEditModal() {
                this.openEditModal = false;
                this.activeItem = {};
            },
            updateObject() {
                this.$store.commit('SET_LOADING_STATUS', true);
                this.update_item(PhoneRepairs.rest_root + '/trackable-objects/' + this.activeItem.id, {
                    id: this.activeItem.id,
                    object_id: this.activeItem.object_id,
                    object_name: this.activeItem.object_name,
                    object_icon: this.activeItem.avatar.image_id,
                }).then(() => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    this.closeEditModal();
                    this.getTrackableObjects();
                }).catch(error => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    console.log(error);
                })
            },
            updateFeaturedImage(image) {
                this.activeItem.avatar = image;
            },
            getTrackableObject(object_id) {
                this.$store.commit('SET_LOADING_STATUS', true);
                this.get_item(PhoneRepairs.rest_root + '/trackable-objects/' + object_id).then(data => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    this.openEditModal = true;
                    this.activeItem = data.data.data;
                }).catch(error => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    console.log(error);
                })
            },
            getLogos() {
                this.get_item(PhoneRepairs.rest_root + '/logo').then(response => {
                    this.images = response.data.data;
                })
            },
            getTrackableObjects() {
                this.$store.commit('SET_LOADING_STATUS', true);
                this.get_items(PhoneRepairs.rest_root + '/trackable-objects', {
                    params: {
                        status: this.status,
                        page: this.currentPage
                    }
                }).then(data => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    this.statuses = data.statuses;
                }).catch(error => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    console.log(error);
                })
            },
            handleStatusChange(status) {
                this.status = status.key;
                this.currentPage = 1;
                this.getTrackableObjects();
            },
            onActionClick(action, item) {
                if ('edit' === action) {
                    this.getTrackableObject(item.id);
                }
                if ('trash' === action && window.confirm('Are you sure to trash this item?')) {
                    this.bulkTrashAction([item.id], 'trash');
                }
                if ('restore' === action && window.confirm('Are you sure to restore this item again?')) {
                    this.bulkTrashAction([item.id], 'restore');
                }
                if ('delete' === action && window.confirm('Are you sure to delete permanently?')) {
                    this.bulkTrashAction([item.id], 'delete');
                }
            },
            bulkTrashAction(ids, action) {
                this.action_batch_trash(PhoneRepairs.rest_root + '/trackable-objects/batch', ids, action).then(data => {
                    this.getTrackableObjects();
                }).catch(error => {
                    console.log(error);
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
	.status--online,
	.status--offline {
		border-radius: 4px;
		box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
		color: #fff;
		display: flex;
		padding: 5px;
		line-height: 1;
		justify-content: center;
	}

	.status--online {
		background-color: #f44336;
	}

	.status--offline {
		background-color: #323232;
	}
</style>
