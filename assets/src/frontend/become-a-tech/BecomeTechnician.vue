<template>
	<div class="stackonet-become-a-technician-form">

		<div class="form-field">
			<animated-input v-model="first_name" label="First Name"/>
		</div>

		<div class="form-field">
			<animated-input v-model="last_name" label="Last Name"/>
		</div>

		<div class="form-field">
			<animated-input v-model="email" label="Email Address" type="email" autocomplete="email"/>
		</div>

		<div class="form-field">
			<animated-input v-model="phone" label="Phone Number"/>
		</div>

		<div class="form-field">
			<label>Resume</label>
			<template v-if="!resume_id">
				<dropzone-uploader :options="dropzoneOptions" @upload="uploaded"></dropzone-uploader>
			</template>
			<template v-if="resume_id">
				<columns multiline>
					<column :tablet="4">
						<div class="mdl-box mdl-shadow--2dp">
							<image-container square>
								<img :src="attachment.thumbnail.src" alt="">
							</image-container>
						</div>
					</column>
				</columns>
			</template>
		</div>

		<div class="form-field">
			<big-button :disabled="!canSubmit" @click="handleSubmit">Submit</big-button>
		</div>

		<spinner :active="loading"></spinner>

		<modal :active="open_thank_you_model" type="box" @close="closeThankYouModel">
			<div class="mdl-box mdl-shadow--2dp">
				<h3>Data has been submitted successfully.</h3>
				<mdl-button @click="closeThankYouModel">Close</mdl-button>
			</div>
		</modal>

	</div>
</template>

<script>
	import axios from 'axios';
	import {columns, column} from 'shapla-columns';
	import modal from 'shapla-modal';
	import spinner from "shapla-spinner";
	import AnimatedInput from '../../components/AnimatedInput';
	import DropzoneUploader from '../components/DropzoneUploader';
	import BigButton from '../../components/BigButton';
	import imageContainer from '../../shapla/image/image';
	import mdlButton from '../../material-design-lite/button/mdlButton';

	export default {
		name: "BecomeTechnician",
		components: {
			DropzoneUploader,
			AnimatedInput,
			BigButton,
			columns,
			column,
			imageContainer,
			spinner,
			modal,
			mdlButton
		},
		data() {
			return {
				loading: false,
				open_thank_you_model: false,
				first_name: '',
				last_name: '',
				phone: '',
				email: '',
				resume_id: '',
				attachment: {},
				attachments: [],
			}
		},
		computed: {
			dropzoneOptions() {
				return {
					url: window.PhoneRepairs.rest_root + '/resume',
					maxFilesize: 5,
					headers: {
						"X-WP-Nonce": window.PhoneRepairs.rest_nonce
					}
				}
			},
			canSubmit() {
				return !!(this.last_name && this.email && this.phone && this.resume_id);
			}
		},
		mounted() {
			this.refreshAttachmentsFromLocalStorage();
			if (this.attachments.length) {
				this.attachment = this.attachments[0];
				this.resume_id = this.attachment.image_id;
				this.setAttachments(this.attachment);
			}
		},
		methods: {
			closeThankYouModel() {
				this.open_thank_you_model = false;
				window.location.reload();
			},
			uploaded(file, response) {
				this.attachment = response.data;
				this.resume_id = this.attachment.image_id;
				this.setAttachments(this.attachment);
			},
			setAttachments(attachment) {
				let attachments = this.attachments;
				attachments.push(attachment);

				// Store in local storage
				if (typeof (Storage) !== "undefined" && attachments.length) {
					localStorage.setItem('_phone_repairs_asap_attachments', JSON.stringify(attachments));
				}
			},
			refreshAttachmentsFromLocalStorage() {
				if (typeof (Storage) !== "undefined") {
					let attachments = localStorage.getItem('_phone_repairs_asap_attachments');

					if (attachments) {
						this.attachments = JSON.parse(attachments);
					}
				}
			},
			clearLocalStorage() {
				if (typeof (Storage) !== "undefined") {
					localStorage.removeItem('_phone_repairs_asap_attachments');
				}
			},
			handleSubmit() {
				let self = this,
					data = {
						first_name: self.first_name,
						last_name: self.last_name,
						email: self.email,
						phone: self.phone,
						resume_id: self.resume_id,
					};

				self.loading = true;
				axios
					.post(PhoneRepairs.rest_root + '/technician', data,
						{
							headers: {'X-WP-Nonce': window.PhoneRepairs.rest_nonce},
						})
					.then((response) => {
						self.loading = false;
						self.open_thank_you_model = true;
						self.clearLocalStorage();
					})
					.catch((error) => {
						self.loading = false;
						alert('Some thing went wrong. Please try again.');
					});
			}
		}
	}
</script>

<style lang="scss">
	.stackonet-become-a-technician-form {
		max-width: 700px;
		margin-left: auto;
		margin-right: auto;

		.loading-container {
			&.is-active {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				display: flex;
				justify-content: center;
				align-items: center;
				background: rgba(#000, 0.5);
				z-index: 10;
			}
		}
	}
</style>
