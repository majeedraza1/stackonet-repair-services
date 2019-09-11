<template>
	<div>
		<div style="margin-top:20px;display: none">
			<mdl-button type="raised" color="primary" @click="openAddModal = true">Add New</mdl-button>
		</div>
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
		<modal :active="openAddModal" @close="closeAddModal" type="box">
			<div class="shapla-box">
				<animated-input label="Username" v-model="newItem.object_id"
								:disabled="status==='unregistered'"></animated-input>
				<animated-input label="Display Name" v-model="newItem.object_name"></animated-input>
				<div>
					<div style="text-align: left">Avatar</div>
					<featured-image
						:image="newItem.avatar"
						@input="updateNewItemImage"
						:images="images"
					></featured-image>
				</div>
				<big-button :fullwidth="true" text="Create" :disabled="!canAddNew" @click="createObject"></big-button>
			</div>
		</modal>
		<modal :active="openEditModal" @close="closeEditModal" type="box">
			<template v-if="Object.keys(activeItem).length">
				<div class="shapla-box">
					<animated-input label="Username" v-model="activeItem.object_id"></animated-input>
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
    import MdlButton from "../../material-design-lite/button/mdlButton";

    export default {
        name: "TrackingUsers",
        components: {
            MdlButton,
            FeaturedImage,
            DropzoneUploader,
            BigButton,
            AnimatedInput,
            WpStatusList,
            mdlTable,
            modal
        },
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
                activeItem: {},
                newItem: {
                    object_id: '',
                    object_name: '',
                    object_icon: 0,
                    avatar: {
                        image_id: 0
                    },
                },
                openAddModal: false,
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
                if ('unregistered' === this.status) {
                    return [{key: 'create', label: 'Create'}];
                }
                return [
                    {key: 'edit', label: 'Edit'},
                    {key: 'trash', label: 'Trash'},
                ];
            },
            canAddNew() {
                if (this.newItem.object_id.length < 3) return false;
                if (this.newItem.object_name.length < 3) return false;
                return !!this.newItem.avatar.image_id;
            }
        },
        mounted() {
            this.getTrackableObjects();
            this.getLogos();
        },
        methods: {
            closeAddModal() {
                this.openAddModal = false;
                this.newItem = {
                    object_id: '',
                    object_name: '',
                    object_icon: 0,
                }
            },
            closeEditModal() {
                this.openEditModal = false;
                this.activeItem = {};
            },
            createObject() {
                this.$store.commit('SET_LOADING_STATUS', true);
                let data = {
                    object_id: this.newItem.object_id,
                    object_name: this.newItem.object_name,
                };
                if (this.newItem.avatar.image_id) {
                    data['object_icon'] = this.newItem.avatar.image_id;
                }
                this.create_item(PhoneRepairs.rest_root + '/trackable-objects', data).then(() => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    this.closeAddModal();
                    this.getTrackableObjects();
                }).catch(error => {
                    this.$store.commit('SET_LOADING_STATUS', false);
                    console.log(error);
                })
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
            updateNewItemImage(image) {
                this.newItem.avatar = image;
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
                if ('create' === action) {
                    this.newItem = {
                        object_id: item.object_id,
                        object_name: item.object_name,
                        object_icon: item.object_icon,
                        avatar: item.avatar,
                    };
                    this.openAddModal = true;
                }
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
