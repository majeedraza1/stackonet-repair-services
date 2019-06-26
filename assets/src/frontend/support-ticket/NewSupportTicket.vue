<template>
	<div class="stackont-support-ticket-container stackont-support-ticket-container--new">

		<div class="display-flex justify-space-between">
			<div class="flex-item">
				<mdl-button type="raised" color="default" @click="ticketList">
					<icon><i class="fa fa-list" aria-hidden="true"></i></icon>
					Ticket List
				</mdl-button>
			</div>
		</div>

		<columns multiline>

			<column :tablet="6">
				<div class="form-field">
					<label for="customer_name">Name</label>
					<input type="text" id="customer_name" v-model="customer_name">
				</div>
			</column>

			<column :tablet="6">
				<div class="form-field">
					<label for="email_address">Email Address</label>
					<input type="text" id="email_address" v-model="customer_email">
				</div>
			</column>

			<column :tablet="12">
				<div class="form-field">
					<label for="ticket_subject">Subject</label>
					<input type="text" id="ticket_subject" v-model="ticket_subject">
				</div>
			</column>

			<column :tablet="12">
				<div class="form-field">
					<label for="ticket_description">Description</label>
					<editor id="ticket_description" :init="mce" v-model="ticket_content"></editor>
				</div>
			</column>

			<column :tablet="12">
				<div class="form-field">
					<label>Attachment</label>
					<p>
						<button @click="openLogoModal = true">Add Attachment</button>
					</p>
					<columns multiline>
						<column :tablet="4" v-for="_image in images" :key="_image.id">
							<div class="mdl-box mdl-shadow--2dp">
								<image-container square>
									<img :src="_image.thumbnail.src" alt="">
								</image-container>
							</div>
						</column>
					</columns>
					<media-modal
						title="Upload image"
						:active="openLogoModal"
						:images="attachments"
						:image="images"
						:options="dropzoneOptions"
						@upload="dropzoneSuccess"
						@selected="chooseImage"
						@close="openLogoModal = false"
					/>
				</div>
			</column>

			<column :tablet="6">
				<div class="form-field">
					<label for="ticket_category">Category *</label>
					<select id="ticket_category" v-model="ticket_category">
						<option v-for="_category in categories" :value="_category.term_id"
								v-html="_category.name"></option>
					</select>
				</div>
			</column>

			<column :tablet="6">
				<div class="form-field">
					<label for="ticket_priority">Priority *</label>
					<select id="ticket_priority" v-model="ticket_priority">
						<option v-for="_priority in priorities" :value="_priority.term_id"
								v-html="_priority.name"></option>
					</select>
				</div>
			</column>

			<column :tablet="12">
				<div class="form-field">
					<mdl-button type="raised" color="primary" :disabled="!canSubmit" @click="submitTicket">Submit
						Ticket
					</mdl-button>
				</div>
			</column>
		</columns>

	</div>
</template>

<script>
	import {mapGetters} from 'vuex';
	import axios from 'axios'
	import Editor from '@tinymce/tinymce-vue'
	import {columns, column} from "shapla-columns";
	import MdlButton from "../../material-design-lite/button/mdlButton";
	import Icon from "../../shapla/icon/icon";
	import MediaModal from "../components/MediaModal";
	import ImageContainer from "../../shapla/image/image";

	export default {
		name: "NewSupportTicket",
		components: {ImageContainer, MediaModal, columns, column, Icon, MdlButton, Editor},
		data() {
			return {
				customer_name: '',
				customer_email: '',
				ticket_subject: '',
				ticket_content: '',
				ticket_category: '',
				ticket_priority: '',
				openLogoModal: false,
				attachments: [],
				images: [],
			}
		},
		computed: {
			...mapGetters(['categories', 'priorities', 'statuses', 'support_agents', 'display_name', 'user_email']),
			mce() {
				return {
					branding: false,
					plugins: 'lists link paste wpemoji',
					toolbar: 'undo redo bold italic underline strikethrough bullist numlist link unlink table inserttable',
					min_height: 150,
					inline: false,
					menubar: false,
					statusbar: true
				}
			},
			dropzoneOptions() {
				return {
					url: window.PhoneRepairs.rest_root + '/logo',
					maxFilesize: 5,
					headers: {
						"X-WP-Nonce": window.PhoneRepairs.rest_nonce
					}
				}
			},
			isValidEmail() {
				return !!(this.customer_email.length && this.validateEmail(this.customer_email));
			},
			canSubmit() {
				return !!(this.customer_name.length > 2 && this.isValidEmail &&
					this.ticket_subject.length > 3 && this.ticket_content.length > 5);
			},
			ticket_attachments() {
				if (this.images.length < 1) return [];

				return this.images.map(image => image.image_id);
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_TITLE', 'New Support Ticket');
			this.customer_name = this.display_name;
			this.customer_email = this.user_email;
			this.getImages();
		},
		methods: {
			dropzoneSuccess(file, response) {
				this.attachments.unshift(response.data);
				this.images.push(response.data);
				this.openLogoModal = false;
			},
			chooseImage(attachment) {
				let index = this.images.indexOf(attachment);
				if (index === -1) {
					this.images.push(attachment);
				} else {
					this.images.splice(index, 1);
				}
			},
			getImages() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.get(PhoneRepairs.rest_root + '/logo')
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.attachments = response.data.data;
					})
					.catch((error) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						alert('Some thing went wrong. Please try again.');
					});
			},
			validateEmail(email) {
				let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(String(email).toLowerCase());
			},
			ticketList() {
				this.$router.push({name: 'SupportTicketList'});
			},
			submitTicket() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket', {
						customer_name: self.customer_name,
						customer_email: self.customer_email,
						ticket_subject: self.ticket_subject,
						ticket_content: self.ticket_content,
						ticket_category: self.ticket_category,
						ticket_priority: self.ticket_priority,
						ticket_attachments: self.ticket_attachments,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						let id = response.data.data.ticket_id;
						this.$router.push({name: 'SingleSupportTicket', params: {id: id}});
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			}
		}
	}
</script>

<style lang="scss">
	.stackont-support-ticket-container {
		&--new {
			max-width: 800px;
			margin-left: auto;
			margin-right: auto;
		}

		.form-field {
			display: block;

			label {
				display: block;
			}

			input, select {
				display: block;
				width: 100%;
			}
		}
	}
</style>
