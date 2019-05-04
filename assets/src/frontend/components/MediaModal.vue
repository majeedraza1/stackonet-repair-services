<template>
	<div class="wp-frontend-media-modal">
		<modal :active="active" @close="closeModal" :title="title">
			<columns desktop>
				<column :desktop="6">
					<vue2-dropzone ref="wpFrontendMediaModal" id="dropzone" :options="options" :useCustomSlot="true"
								   @vdropzone-success="upload">
						<div class="dz-default dz-message">
							<div class="ap-toolkit-dropzone-icon">
								<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
									 viewBox="0 0 38.98 38.98">
									<path d="M19.38 24.81h.22l-.11-.1z"></path>
									<path
										d="M27.91 15.91h-.35v-.53a6.39 6.39 0 0 0-12.22-2.61 3.81 3.81 0 0 0-5.3 2.78 4.71 4.71 0 0 0 1.26 9.24h3.75l.06-.08L18 21.94a2.08 2.08 0 0 1 2.89 0l2.9 2.79.06.08h4a4.45 4.45 0 0 0 .06-8.9z"></path>
									<path
										d="M22.79 25.81L19.9 23a.58.58 0 0 0-.81 0l-2.9 2.79a.58.58 0 0 0 .41 1h1.47V30h2.86v-3.18h1.47a.58.58 0 0 0 .39-1.01z"></path>
								</svg>
							</div>
							<div class="ap-toolkit-dropzone-drag">Drag &amp; Drop or</div>
							<div class="ap-toolkit-dropzone-browse">
								Click here to browse your computer
							</div>
							<div class="ap-toolkit-dropzone-maxsize">
								Maximum upload limit: 5MB
							</div>
						</div>
					</vue2-dropzone>
				</column>
				<column :desktop="6">
					<div class="attachment-list mdl-list" v-if="images.length">
						<div class="mdl-list__item" :class="{'is-active':attachment === image}"
							 v-for="attachment in images" v-if="attachment.title" @click="chooseMedia(attachment)">
							<div class="mdl-list__item-primary-content">
								<img class="mdl-list__item-avatar" :src="attachment.attachment_url"
									 :alt="attachment.title">
								<span v-text="attachment.title"></span>
							</div>
							<div class="mdl-list__item-secondary-action" @click="deleteMedia(attachment)">
								<delete-icon></delete-icon>
							</div>
						</div>
					</div>
				</column>
			</columns>
			<div slot="foot">
				<button class="button" @click="closeModal">Close</button>
			</div>
		</modal>
	</div>
</template>

<script>
	import vue2Dropzone from 'vue2-dropzone'
	import modal from '../../shapla/modal/modal';
	import columns from '../../shapla/columns/columns';
	import column from '../../shapla/columns/column';
	import deleteIcon from '../../shapla/delete/deleteIcon';

	export default {
		name: "MediaModal",
		components: {vue2Dropzone, modal, deleteIcon, columns, column},
		props: {
			active: {type: Boolean, default: false},
			title: {type: String, default: "Edit Images"},
			images: {type: Array, default: () => []},
			image: {
				type: Object, default: () => {
				}
			},
			options: {
				type: Object, required: false, default: () => {
				}
			},
		},
		methods: {
			chooseMedia(attachment) {
				this.$emit('selected', attachment);
			},
			closeModal() {
				this.$emit('close');
			},
			deleteMedia(attachment) {
				if (confirm('Are you sure to delete this item?')) {
					this.$emit('delete', attachment);
				}
			},
			upload(file, response) {
				this.$emit('upload', file, response);
				this.$refs.wpFrontendMediaModal.removeFile(file);
			}
		}
	}
</script>

<style lang="scss">
	.wp-frontend-media-modal {

		.shapla-columns {
			align-items: flex-start !important;
		}

		.dz-message {
			margin: 0 !important;
		}

		.mdl-list {
			padding: 0;
		}

		.mdl-list__item {
			border: 1px solid rgba(#000, 0.2);
			margin-bottom: 1rem;
			cursor: pointer;

			&.is-active,
			&:hover {
				border-color: #fdd835;
			}

			&.is-active {
				border-width: 2px;
			}
		}

		@media screen and (min-width: 1000px) {
			.shapla-modal-content,
			.shapla-modal-card {
				width: 900px;
			}
		}
	}
</style>
