<template>
	<div class="stackonet_support_ticket_form_container">

		<columns multiline>

			<column :desktop="6">
				<div class="form-field">
					<label for="customer_name">Name <abbr class="required" title="Required">*</abbr></label>
					<input type="text" id="customer_name" v-model="customer_name">
				</div>
			</column>

			<column :desktop="6">
				<div class="form-field">
					<label for="email_address">Email Address <abbr class="required" title="Required">*</abbr></label>
					<input type="email" id="email_address" v-model="customer_email">
				</div>
			</column>

			<column :desktop="6">
				<div class="form-field">
					<label for="phone_number">Phone Number <abbr class="required" title="Required">*</abbr></label>
					<input type="text" id="phone_number" v-model="phone_number">
				</div>
			</column>

			<column :desktop="6">
				<div class="form-field">
					<label for="department">Department <abbr class="required" title="Required">*</abbr></label>
					<select id="department" v-model="ticket_category">
						<option :value="_cat.id" v-for="_cat in categories">{{_cat.name}}</option>
					</select>
				</div>
			</column>

			<column :desktop="12">
				<div class="form-field">
					<label for="ticket_subject">Subject <abbr class="required" title="Required">*</abbr></label>
					<input type="text" id="ticket_subject" v-model="ticket_subject">
				</div>
			</column>

			<column :desktop="12">
				<div class="form-field">
					<label for="ticket_description">Description <abbr class="required" title="Required">*</abbr></label>
					<editor id="ticket_description" :init="mce" v-model="ticket_content"></editor>
				</div>
			</column>

			<column :desktop="12">
				<div class="form-field">
					<mdl-button type="raised" color="primary" :disabled="!canSubmit" @click="submitTicket">Submit
						Ticket
					</mdl-button>
				</div>
			</column>

			<column>
				All fields marked with <abbr class="required" title="Required">*</abbr> are required.
			</column>
		</columns>

		<modal :active="activeThankYouModal" type="box" @close="activeThankYouModal = false"
			   class="support-ticket-thankyou-modal">
			<div class="shapla-box">
				<p>Thank you for submitting! <br>We will get back to you soon. </p>
				<mdl-button @click="activeThankYouModal = false">Close</mdl-button>
			</div>
		</modal>

		<spinner :active="loading"></spinner>
	</div>
</template>

<script>
	import axios from 'axios'
	import Editor from '@tinymce/tinymce-vue'
	import {columns, column} from "shapla-columns";
	import modal from "shapla-modal";
	import spinner from "shapla-spinner";
	import MdlButton from "../../material-design-lite/button/mdlButton";

	export default {
		name: "SupportTicketForm",
		components: {spinner, modal, columns, column, MdlButton, Editor},
		mounted() {
			if (window.CustomerSupportTickets) {
				this.categories = CustomerSupportTickets.categories;
				this.customer_name = CustomerSupportTickets.user.display_name;
				this.customer_email = CustomerSupportTickets.user.user_email;
			}
		},
		data() {
			return {
				loading: false,
				activeThankYouModal: false,
				customer_name: '',
				customer_email: '',
				phone_number: '',
				ticket_category: '',
				ticket_subject: '',
				ticket_content: '',
				categories: [],
			}
		},
		computed: {
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
			isValidEmail() {
				return !!(this.customer_email.length && this.validateEmail(this.customer_email));
			},
			canSubmit() {
				return !!(this.customer_name.length > 2 && this.isValidEmail && this.phone_number &&
					this.ticket_category && this.ticket_subject.length > 3 && this.ticket_content.length > 5);
			}
		},
		methods: {
			validateEmail(email) {
				let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(String(email).toLowerCase());
			},
			submitTicket() {
				let self = this;
				self.loading = true;
				axios
					.post(PhoneRepairs.rest_root + '/customer-support-ticket', {
						customer_name: self.customer_name,
						customer_email: self.customer_email,
						phone_number: self.phone_number,
						ticket_category: self.ticket_category,
						ticket_subject: self.ticket_subject,
						ticket_content: self.ticket_content,
					})
					.then(response => {
						self.loading = false;
						self.activeThankYouModal = true;
						self.phone_number = '';
						self.ticket_category = '';
						self.ticket_subject = '';
						self.ticket_content = '';
					})
					.catch(error => {
						self.loading = false;
					});
			}
		}
	}
</script>

<style lang="scss">
	.stackonet_support_ticket_form_container {
		label {
			display: block;
		}

		input[type=text],
		input[type=email],
		select {
			width: 100%;
		}

		abbr.required {
			color: #f68638;
		}

		.support-ticket-thankyou-modal {
			.shapla-box {
				font-size: 20px;
				text-align: center;

				.mdl-button {
					margin-top: 20px;
				}
			}
		}

		.support-ticket-loader.is-active {
			display: flex;
			z-index: 100000;
			width: 100%;
			height: 100%;
			position: fixed;
			background: rgba(#fff, 0.6);
			top: 0;
			left: 0;
			justify-content: center;
			align-items: center;
		}
	}
</style>
