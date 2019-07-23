<template>
	<div class="stackont-single-support-ticket-container">

		<div class="stackont-single-support-ticket-actions-bar">
			<columns>
				<column :desktop="8">
					<div class="stackont-single-support-ticket-actions">
						<div class="left">
							<mdl-button type="raised" color="primary" @click="openNewTicket">
								<icon><i class="fa fa-plus" aria-hidden="true"></i></icon>
								New Ticket
							</mdl-button>
							<mdl-button type="raised" color="default" @click="ticketList">
								<icon><i class="fa fa-list" aria-hidden="true"></i></icon>
								Ticket List
							</mdl-button>
							<template v-if="!!Object.keys(map).length">
								<mdl-button type="raised" color="default" @click="showViewMapModal = true">
									<icon><i class="fa fa-map" aria-hidden="true"></i></icon>
									View Map
								</mdl-button>
							</template>
							<template v-if="item.created_via=== 'appointment'">
								<template v-if="order.order_edit_url">
									<a class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised"
									   target="_blank" :href="order.order_edit_url">View Order</a>
								</template>
								<mdl-button v-else type="raised" color="default" @click="createOrderFromAppointment">
									Create Order
								</mdl-button>
							</template>
						</div>
						<div class="right">
							<div class="stackont-support-ticket-nav">
								<div class="stackont-support-ticket-nav__left">
									<div v-if="navigation.pre && navigation.pre.id" @click="refreshRoute">
										<router-link
											:to="{name: 'SingleSupportTicket', params: {id: navigation.pre.id}}">
											<i class="fa fa-chevron-left" aria-hidden="true"></i>
										</router-link>
									</div>
									<div v-else class="disabled">
										<i class="fa fa-chevron-left" aria-hidden="true"></i>
									</div>
								</div>
								<div class="stackont-support-ticket-nav__right">
									<div v-if="navigation.next && navigation.next.id" @click="refreshRoute">
										<router-link
											:to="{name: 'SingleSupportTicket', params: {id: navigation.next.id}}">
											<i class="fa fa-chevron-right" aria-hidden="true"></i>
										</router-link>
									</div>
									<div v-else class="disabled">
										<i class="fa fa-chevron-right" aria-hidden="true"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</column>
			</columns>
		</div>

		<columns>
			<column :desktop="8">

				<div class="stackont-single-ticket-content">

					<div class="stackont-single-ticket__heading">
						<h4 class="stackont-single-ticket__title">[Ticket #{{item.id}}] {{item.ticket_subject}}</h4>
						<icon><i @click="openTitleModal" class="fa fa-pencil-square-o" aria-hidden="true"></i></icon>
					</div>

					<div>
						<editor :init="mce" v-model="content"></editor>
						<div v-show="content.length">
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
						</div>
						<div style="text-align: right;margin-top:10px;" v-show="content.length">
							<mdl-button type="raised" color="default" @click="addNote">Add Note</mdl-button>
							<mdl-button type="raised" color="primary" @click="submitReply">Submit Reply</mdl-button>
						</div>
					</div>

					<ticket-threads :threads="threads"></ticket-threads>
					<div class="shapla-thread shapla-thread--report" v-if="Object.keys(checkout_analysis).length">
						<step-progress-bar :steps="checkout_analysis.steps"
										   :selected="checkout_analysis.steps_percentage"></step-progress-bar>
					</div>
				</div>

			</column>

			<column :desktop="4">

				<div class="shapla-box shapla-widget-box">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Status</h5>
						<icon>
							<i @click="openStatusModal" class="fa fa-pencil-square-o" aria-hidden="true"></i>
						</icon>
					</div>
					<div class="shapla-widget-box__content" v-if="Object.keys(item).length">
						<list-item label="Status">{{item.status.name}}</list-item>
						<list-item label="Category">{{item.category.name}}</list-item>
						<list-item label="Priority">{{item.priority.name}}</list-item>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box" v-if="order.status">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Order Status</h5>
					</div>
					<div class="shapla-widget-box__content">
						<list-item label="Status">
							<select v-model="order.status" @change="enableStatusUpdate">
								<option v-for="(_status, key) in order_statuses" :value="key">{{_status}}</option>
							</select>
						</list-item>
						<template v-if="activeOrderStatus">
							<mdl-button @click="changeOrderStatus" :disabled="!activeOrderStatus" type="raised"
										style="width: 100%;">Change Order Status
							</mdl-button>
						</template>
						<div class="payment-status-container">
							<list-item label="Payment">
								<div class="payment-status">
									<div class="payment-status__circle"
										 :class="{
										 'is-complete':order.payment_status === 'complete',
										 'is-repairing':order.payment_status === 'repairing',
										 'is-processing':order.payment_status === 'processing'
										 }"></div>
									<div class="payment-status__separator"></div>
									<div class="payment-status__label">
										<template v-if="order.payment_status === 'repairing'">Repairing</template>
										<template v-if="order.payment_status === 'processing'">Processing</template>
										<template v-if="order.payment_status === 'complete'">Received</template>
									</div>
								</div>
							</list-item>
						</div>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box" v-show="order && order.order_total">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Payment Status</h5>
					</div>
					<div class="shapla-widget-box__content">
						<list-item label="Total"><span v-html="order.order_total"></span></list-item>
						<list-item label="Payment URL"><a target="_blank" :href="order.payment_url">View URL</a>
						</list-item>
						<div>
							<list-item label="Discount">
								<mdl-button @click="showDiscount = !showDiscount" type="raised">Add Discount
								</mdl-button>
							</list-item>
							<columns multiline v-if="showDiscount" class="mdl-shadow--8dp"
									 style="margin: 10px;padding: 10px;">
								<column :tablet="12"><input type="number" style="width: 100%" v-model="discountAmount">
								</column>
								<column :tablet="6">
									<mdl-button type="raised" @click="discountType = 'percentage'"
												:color="discountType === 'percentage'?'primary':'default'"
												style="width: 100%;">
										Percentage
									</mdl-button>
								</column>
								<column :tablet="6">
									<mdl-button type="raised" @click="discountType = 'fixed'"
												:color="discountType === 'fixed'?'primary':'default'"
												style="width: 100%;">Fixed
										Amount
									</mdl-button>
								</column>
								<column :tablet="12">
									<mdl-button type="raised" color="primary" @click="applyDiscount"
												:disabled="!(discountType.length && discountAmount)"
												style="width: 100%;">Apply Discount
									</mdl-button>
								</column>
							</columns>
						</div>
						<div style="margin-top:10px;">
							Send Payment Link
							<div style="margin-top: 10px;">
								<label><input type="radio" value="sms" v-model="paymentLinkMedia">SMS</label>
								<label><input type="radio" value="email" v-model="paymentLinkMedia">Email</label>
								<label><input type="radio" value="both" v-model="paymentLinkMedia">Both SMS &amp; Email</label>
							</div>

							<mdl-button v-if="paymentLinkMedia.length" type="raised" color="primary"
										@click="SendPaymentLink" style="width: 100%;margin-top: 10px;">Send
							</mdl-button>
						</div>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box" v-show="order && order.order_total">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Custom Payment</h5>
					</div>
					<div class="shapla-widget-box__content">
						<mdl-button @click="showPaymentAmount = !showPaymentAmount" type="raised" style="width: 100%;">
							Add Payment Amount
						</mdl-button>
						<columns multiline v-if="showPaymentAmount" class="mdl-shadow--8dp"
								 style="margin: 10px;padding: 10px;">
							<column :tablet="12">
								<input type="number" style="width: 100%" v-model="paymentAmount">
							</column>
							<column :tablet="12">
								<mdl-button type="raised" color="primary" @click="addCustomPayment"
											:disabled="!paymentAmount"
											style="width: 100%;">Save Payment Amount
								</mdl-button>
							</column>
						</columns>
						<div style="margin-top: 10px;">
							<accordion :title="`Payment Link ${index + 1} - (Amount: ${_item.amount})`" :key="index"
									   v-for="(_item, index) in custom_amount_items">
								<list-item label="Payment URL"><a target="_blank" :href="_item.payment_url">View URL</a>
								</list-item>
								<div>
									Send Payment Link
									<div style="margin-top: 10px;">
										<label><input type="radio" value="sms"
													  v-model="customPaymentLinkMedia">SMS</label>
										<label><input type="radio" value="email" v-model="customPaymentLinkMedia">Email</label>
										<label><input type="radio" value="both" v-model="customPaymentLinkMedia">Both
											SMS &amp; Email</label>
									</div>

									<mdl-button v-if="customPaymentLinkMedia.length" type="raised" color="primary"
												@click="sendCustomPaymentLink(_item)"
												style="width: 100%;margin-top: 10px;">Send
									</mdl-button>
								</div>
							</accordion>
						</div>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box" v-show="isOrderTicket">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Map</h5>
					</div>
					<div class="shapla-widget-box__content">
						<div id="google-map"></div>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Assign Agent(s)</h5>
						<icon>
							<i @click="openAssignAgentModal" class="fa fa-pencil-square-o" aria-hidden="true"></i>
						</icon>
					</div>
					<div class="shapla-widget-box__content">
						<span class="shapla-chip shapla-chip--contact" v-for="_agent in item.assigned_agents">
							<span class="shapla-chip__contact">
								<image-container>
									<img :src="_agent.avatar_url" width="32" height="32">
								</image-container>
							</span>
							<span class="shapla-chip__text">{{_agent.display_name}}</span>
						</span>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Raised By</h5>
					</div>
					<div class="shapla-widget-box__content">
						<span class="shapla-chip shapla-chip--contact">
							<span class="shapla-chip__contact">
								<image-container>
									<img :src="item.customer_url" width="32" height="32">
								</image-container>
							</span>
							<span class="shapla-chip__text">{{item.customer_name}}</span>
						</span>
					</div>
				</div>

				<div class="shapla-box shapla-widget-box">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">SMS Messages</h5>
					</div>
					<div class="shapla-widget-box__content">
						<p v-if="item.customer_phone">
							<label>
								<input type="radio" value="customer" @change="agentSmsChanged"
									   v-model="ticket_twilio_sms_enable_phone">
								<strong>Customer Phone: </strong> {{item.customer_phone}}
							</label>
						</p>
						<p>
							<label>
								<input type="radio" value="custom" @change="agentSmsChanged"
									   v-model="ticket_twilio_sms_enable_phone">
								<strong>Custom Phone: </strong>
								<input type="text" v-model="ticket_twilio_sms_custom_phone"/>
							</label>
						</p>
						<p>
							<label>
								<input type="radio" v-model="ticket_twilio_sms_enable_phone" value="agents"
									   @change="agentSmsChanged">
								<strong>Assign Agent(s)</strong>
							</label>

							<icon v-if="ticket_twilio_sms_enable_phone === 'agents'">
								<i @click="openTwilioAssignAgentModal" aria-hidden="true"
								   class="fa fa-pencil-square-o"></i>
							</icon>
							<br>
							<template v-for="_agent in support_agents">

							<span class="shapla-chip shapla-chip--contact"
								  v-if="twilio_support_agents_ids.indexOf(_agent.id) !== -1">
							<span class="shapla-chip__contact">
								<image-container>
									<img :src="_agent.avatar_url" width="32" height="32">
								</image-container>
							</span>
							<span class="shapla-chip__text">{{_agent.display_name}}</span>
						</span>
							</template>
						</p>
						<p><textarea type="text" name="ticket_twilio_sms" id="ticket_twilio_sms"
									 v-model="ticket_twilio_sms_content"
									 class="input-text" style="width: 100%;" rows="4" value=""></textarea></p>
						<p>
							<mdl-button type="raised" :disabled="!canSendSms" @click="sendSms">Send SMS</mdl-button>
							<span id="wc_twilio_sms_order_message_char_count"
								  style="color: green; float: right; font-size: 16px;">{{ticket_twilio_sms_content.length}}</span>
						</p>
					</div>
				</div>

			</column>
		</columns>

		<modal :active="activeThreadModal" title="Edit this Thread" @close="closeThreadEditor">
			<editor :init="mce" v-model="activeThreadContent"></editor>
			<template slot="foot">
				<mdl-button @click="updateThread">Save</mdl-button>
			</template>
		</modal>

		<modal :active="activeStatusModal" title="Change Ticket Status" @close="activeStatusModal = false">
			<list-item label="Status">
				<select v-model="ticket_status">
					<option :value="_category.term_id" v-for="_category in statuses">{{_category.name}}</option>
				</select>
			</list-item>
			<list-item label="Category">
				<select v-model="ticket_category">
					<option :value="_category.term_id" v-for="_category in categories">{{_category.name}}</option>
				</select>
			</list-item>
			<list-item label="Priority">
				<select v-model="ticket_priority">
					<option :value="_category.term_id" v-for="_category in priorities">{{_category.name}}</option>
				</select>
			</list-item>
			<template slot="foot">
				<mdl-button @click="updateTicketStatus">Save</mdl-button>
			</template>
		</modal>

		<modal :active="activeAgentModal" title="Change Assign Agent(s)" @close="activeAgentModal = false">
			<template v-for="_agent in support_agents">
				<div class="support_agents-chip">
					<div class="shapla-chip shapla-chip--contact" @click="updateAgent(_agent)"
						 :class="{'is-active':support_agents_ids.indexOf(_agent.id) !== -1}">
						<div class="shapla-chip__contact">
							<image-container>
								<img :src="_agent.avatar_url" width="32" height="32">
							</image-container>
						</div>
						<span class="shapla-chip__text">{{_agent.display_name}} - {{_agent.role_label}}</span>
					</div>
				</div>
			</template>
			<template slot="foot">
				<mdl-button @click="updateAssignAgents">Save</mdl-button>
			</template>
		</modal>
		<modal :active="activeTitleModal" title="Change Ticket Subject" @close="activeTitleModal = false">
			<textarea v-model="ticket_subject" style="width: 100%;"></textarea>
			<template slot="foot">
				<mdl-button @click="updateSubject">Save</mdl-button>
			</template>
		</modal>

		<modal :active="activeTwilioAgentModal" title="Choose Assign Agent(s)" @close="activeTwilioAgentModal = false">
			<template v-for="_agent in support_agents">
				<div class="support_agents-chip">
					<div class="shapla-chip shapla-chip--contact" @click="updateTwilioAgent(_agent)"
						 :class="{'is-active':twilio_support_agents_ids.indexOf(_agent.id) !== -1}">
						<div class="shapla-chip__contact">
							<image-container>
								<img :src="_agent.avatar_url" width="32" height="32">
							</image-container>
						</div>
						<span class="shapla-chip__text">
							{{_agent.display_name}} - {{_agent.role_label}}
							<span v-if="_agent.phone">(Phone: {{_agent.phone}})</span>
						</span>
					</div>
				</div>
			</template>
			<template slot="foot">
				<mdl-button @click="activeTwilioAgentModal = false">Confirm</mdl-button>
			</template>
		</modal>

		<map-modal :place="map" :active="showViewMapModal" mode="view" @close="showViewMapModal = false"></map-modal>

	</div>
</template>

<script>
	import axios from 'axios';
	import {mapGetters} from 'vuex';
	import Editor from '@tinymce/tinymce-vue'
	import {columns, column} from 'shapla-columns'
	import modal from 'shapla-modal'
	import mdlButton from '../../material-design-lite/button/mdlButton'
	import ListItem from '../../components/ListItem'
	import ImageContainer from "../../shapla/image/image";
	import Icon from "../../shapla/icon/icon";
	import MediaModal from "../components/MediaModal";
	import MdlRadio from "../../material-design-lite/radio/mdlRadio";
	import Accordion from "../../components/Accordion";
	import TicketThreads from "./TicketThreads";
	import MapModal from "../dashboard/pages/MapModal";
	import StepProgressBar from "../../components/StepProgressBar";

	export default {
		name: "SingleSupportTicket",
		components: {
			StepProgressBar,
			MapModal,
			TicketThreads, Accordion, MdlRadio, MediaModal, Icon, ImageContainer,
			mdlButton, columns, column, ListItem, Editor, modal
		},
		data() {
			return {
				loading: false,
				activeStatusModal: false,
				activeAgentModal: false,
				activeThreadModal: false,
				activeTitleModal: false,
				activeOrderStatus: false,
				showDiscount: false,
				discountAmount: '',
				discountType: '',
				paymentLinkMedia: '',
				activeThread: {},
				activeTwilioAgentModal: false,
				ticket_twilio_sms_customer_phone: true,
				ticket_twilio_sms_enable_custom_phone: false,
				ticket_twilio_sms_enable_phone: '',
				ticket_twilio_sms_custom_phone: '',
				ticket_twilio_sms_content: '',
				twilio_support_agents_ids: [],
				order_statuses: [],
				activeThreadContent: '',
				ticket_subject: '',
				ticket_category: '',
				ticket_priority: '',
				ticket_status: '',
				support_agents_ids: [],
				threadType: '',
				id: 0,
				content: '',
				item: {},
				threads: [],
				map: {},
				checkout_analysis: {},
				showViewMapModal: false,
				navigation: {
					pre: {},
					next: {}
				},
				openLogoModal: false,
				attachments: [],
				images: [],
				order: {},
				showPaymentAmount: false,
				paymentAmount: '',
				customPaymentLinkMedia: '',
			}
		},
		watch: {
			order(newValue) {
				if (newValue && newValue.latitude_longitude) {
					this.refreshMap(newValue.latitude_longitude, newValue.address);
				}
			}
		},
		computed: {
			...mapGetters(['categories', 'priorities', 'statuses', 'support_agents']),
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
			ticket_attachments() {
				if (this.images.length < 1) return [];

				return this.images.map(image => image.image_id);
			},
			canSendSms() {
				return this.ticket_twilio_sms_content.length >= 5;
			},
			isOrderTicket() {
				return !!Object.keys(this.order).length;
			},
			custom_amount_items() {
				if (this.isOrderTicket) {
					return this.order.custom_amount_items;
				} else {
					return [];
				}
			}
		},
		mounted() {
			let id = this.$route.params.id;
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_TITLE', 'Support Ticket');
			if (SupportTickets.order_statuses) {
				this.order_statuses = SupportTickets.order_statuses;
			}
			if (id) {
				this.id = parseInt(id);
				this.getItem();
			}
			this.getImages()
		},
		methods: {
			addCustomPayment() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/order/' + this.order.id + '/custom-payment-amount', {
						ticket_id: this.id,
						amount: this.paymentAmount,
					})
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.getItem();
						this.showPaymentAmount = false;
						this.$root.$emit('show-notification', {
							message: 'New payment amount has been added.',
							type: 'success'
						});
					})
					.catch((error) => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			sendCustomPaymentLink(item) {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/order/' + this.order.id + '/custom-payment-sms', {
						ticket_id: item.support_id,
						order_id: item.order_id,
						amount: item.amount,
						hash: item.hash,
						media: this.customPaymentLinkMedia,
					})
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.$root.$emit('show-notification', {
							message: 'Email/SMS has been sent with custom payment link.',
							type: 'success'
						});
						this.getItem();
					})
					.catch((error) => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			refreshMap(position, title) {
				let googleMap = new google.maps.Map(this.$el.querySelector('#google-map'), {
					zoom: 15,
					center: position,
				});
				new google.maps.Marker({position: position, title: title}).setMap(googleMap);
			},
			applyDiscount() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.put(PhoneRepairs.rest_root + '/order/' + this.order.id + '/discount', {
						ticket_id: this.id,
						amount: this.discountAmount,
						discount_type: this.discountType,
					})
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.$root.$emit('show-notification', {
							message: 'Order status has been changed.',
							type: 'success'
						});
						this.getItem();
					})
					.catch((error) => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			SendPaymentLink() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/order/' + this.order.id + '/sms', {
						ticket_id: this.id,
						media: this.paymentLinkMedia,
					})
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.$root.$emit('show-notification', {
							message: 'Email/SMS has been sent.',
							type: 'success'
						});
						this.getItem();
					})
					.catch((error) => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			enableStatusUpdate() {
				this.activeOrderStatus = true;
			},
			changeOrderStatus() {
				if (confirm('Are you sure to change order status?')) {
					this.$store.commit('SET_LOADING_STATUS', true);
					axios
						.put(PhoneRepairs.rest_root + '/support-ticket/' + this.id + '/order/' + this.order.id, {status: this.order.status})
						.then((response) => {
							this.$store.commit('SET_LOADING_STATUS', false);
							this.$root.$emit('show-notification', {
								message: 'Order status has been changed.',
								type: 'success'
							});
							this.getItem();
						})
						.catch((error) => {
							console.log(error);
							this.$store.commit('SET_LOADING_STATUS', false);
						});
				}
			},
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
						console.log(error);
					});
			},
			refreshRoute() {
				let id = this.$route.params.id;
				this.id = parseInt(id);
				this.getItem();
			},
			sendSms() {
				if (this.ticket_twilio_sms_content.length < 5) {
					alert('Please add some content first.');
					return;
				}
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/' + self.id + '/sms', {
						content: self.ticket_twilio_sms_content,
						send_to_customer: self.ticket_twilio_sms_customer_phone,
						send_to_custom_number: self.ticket_twilio_sms_enable_custom_phone,
						custom_phone: self.ticket_twilio_sms_custom_phone,
						agents_ids: self.twilio_support_agents_ids,
						sms_for: self.ticket_twilio_sms_enable_phone,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.ticket_twilio_sms_content = '';
						self.ticket_twilio_sms_customer_phone = true;
						self.ticket_twilio_sms_enable_custom_phone = false;
						self.ticket_twilio_sms_custom_phone = '';
						self.ticket_twilio_sms_enable_phone = '';
						self.twilio_support_agents_ids = [];
						self.getItem();
						alert('SMS has been sent successfully.');
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						if (error.response.status === 422 && error.response.data) {
							alert(error.response.data.message);
						}
						if (error.response.status === 401 && error.response.data) {
							alert(error.response.data.message);
						}
					});
			},
			agentSmsChanged() {
				if (this.ticket_twilio_sms_enable_phone === 'agents') {
					this.ticket_twilio_sms_content = 'URL: ' + window.location.href;
				} else {
					this.ticket_twilio_sms_content = '';
				}
			},
			openTwilioAssignAgentModal() {
				this.activeTwilioAgentModal = true;
			},
			updateTwilioAgent(agent) {
				let index = this.twilio_support_agents_ids.indexOf(agent.id);
				if (-1 !== index) {
					this.twilio_support_agents_ids.splice(index, 1);
				} else {
					this.twilio_support_agents_ids.push(agent.id);
				}
			},
			openNewTicket() {
				this.$router.push({name: 'NewSupportTicket'});
			},
			addNote() {
				this.addThread('note', this.content);
			},
			submitReply() {
				if (confirm('Are you sure?')) {
					this.addThread('reply', this.content);
				}
			},
			openTitleModal() {
				this.ticket_subject = this.item.ticket_subject;
				this.activeTitleModal = true;
			},
			openStatusModal() {
				this.activeStatusModal = true;
				this.ticket_category = this.item.ticket_category;
				this.ticket_priority = this.item.ticket_priority;
				this.ticket_status = this.item.ticket_status;
			},
			openAssignAgentModal() {
				let ids = this.assigned_agents_ids();
				this.activeAgentModal = true;
				this.support_agents_ids = ids;
			},
			assigned_agents_ids() {
				if (this.item.assigned_agents.length < 1) {
					return [];
				}

				return this.item.assigned_agents.map(item => {
					return item.id;
				});
			},
			updateAgent(agent) {
				let index = this.support_agents_ids.indexOf(agent.id);
				if (-1 !== index) {
					this.support_agents_ids.splice(index, 1);
				} else {
					this.support_agents_ids.push(agent.id);
				}
			},
			updateAssignAgents() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/' + self.id + '/agent', {
						agents_ids: self.support_agents_ids,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.activeAgentModal = false;
						self.support_agents_ids = [];
						self.getItem();
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			openThreadEditor(thread) {
				this.activeThreadModal = true;
				this.activeThread = thread;
				this.activeThreadContent = thread.thread_content;
			},
			closeThreadEditor() {
				this.activeThreadModal = false;
				this.activeThread = {};
				this.activeThreadContent = '';
			},
			updateTicketStatus() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.put(PhoneRepairs.rest_root + '/support-ticket/' + self.id, {
						ticket_category: self.ticket_category,
						ticket_priority: self.ticket_priority,
						ticket_status: self.ticket_status,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.activeStatusModal = false;
						self.ticket_subject = '';
						self.ticket_status = '';
						self.ticket_priority = '';
						self.getItem();
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			updateSubject() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.put(PhoneRepairs.rest_root + '/support-ticket/' + self.id, {
						ticket_subject: self.ticket_subject,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.activeTitleModal = false;
						self.ticket_subject = '';
						self.getItem();
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			addThread(thread_type, thread_content) {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/' + self.id + '/thread/', {
						thread_type: thread_type,
						thread_content: thread_content,
						ticket_attachments: self.ticket_attachments,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.content = '';
						self.images = [];
						self.getItem();
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			updateThread() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.put(PhoneRepairs.rest_root + '/support-ticket/' + self.id + '/thread/' + self.activeThread.thread_id, {
						post_content: self.activeThreadContent,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.activeThreadModal = false;
						self.activeThread = {};
						self.activeThreadContent = '';
						self.getItem();
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			deleteThread(thread) {
				let self = this;
				if (confirm('Are you sure to delete this thread?')) {
					self.$store.commit('SET_LOADING_STATUS', true);
					axios
						.delete(PhoneRepairs.rest_root + '/support-ticket/' + self.id + '/thread/' + thread.thread_id)
						.then((response) => {
							self.$store.commit('SET_LOADING_STATUS', false);
							self.getItem();
						})
						.catch((error) => {
							self.$store.commit('SET_LOADING_STATUS', false);
						});
				}
			},
			threadClass(thread_type) {
				return [
					'shapla-thread',
					`shapla-thread--${thread_type}`
				]
			},
			ticketList() {
				this.$router.push({name: 'SupportTicketList'});
			},
			createOrderFromAppointment() {
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.post(PhoneRepairs.rest_root + '/support-ticket/' + this.id + '/order')
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						this.$root.$emit('show-notification', {
							message: 'New order has been created.',
							type: 'success'
						});
						this.getItem();
					})
					.catch((error) => {
						console.log(error);
						this.$store.commit('SET_LOADING_STATUS', false);
					});
			},
			getItem() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.get(PhoneRepairs.rest_root + '/support-ticket/' + self.id)
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.item = response.data.data.ticket;
						self.threads = response.data.data.threads;
						self.navigation = response.data.data.navigation;
						if (response.data.data.order) {
							self.order = response.data.data.order;
						}
						if (response.data.data.map) {
							self.map = response.data.data.map;
						}
						if (response.data.data.checkout_analysis) {
							self.checkout_analysis = response.data.data.checkout_analysis;
						}
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
					});
			}
		}
	}
</script>

<style lang="scss">
	.payment-status {
		display: flex;
		justify-content: flex-start;
		align-items: center;

		&-container {
			margin-top: 10px;
		}

		&__circle {
			width: 20px;
			height: 20px;
			display: inline-flex;
			border-radius: 32px;

			&.is-repairing {
				background: #ff7e30;
			}

			&.is-processing {
				background: #fff335;
			}

			&.is-complete {
				background: #20b054;
			}

			&.is-failed {
				background: #ee1e26;
			}
		}

		&__separator {
			padding: 0 5px;
		}
	}

	.stackont-single-support-ticket-container {

		.stackont-single-support-ticket-actions-bar {
			border-bottom: 1px solid rgba(#000, 0.1);
			padding-bottom: 1.5rem;
			margin-bottom: 1.5rem;
		}

		#google-map {
			height: 300px;
		}

		.stackont-single-support-ticket-actions {
			display: flex;
			justify-content: space-between;

			.left > *:not(:last-child) {
				margin-right: 5px;
			}

			> *:not(:last-child) {
				margin-right: 5px;
			}
		}

		.stackont-single-ticket__heading {
			display: flex;
			align-items: center;
			margin: 0 0 1.5rem 0;
			justify-content: space-between;

			h4, i {
				padding: 0;
				font-size: 20px;
				margin: 0 0 1em;
			}
		}

		.mdl-list-item {
			align-items: center;
			display: flex;
			justify-content: flex-start;

			&:not(:last-child) {
				margin-bottom: 5px;
				padding-bottom: 5px;
			}

			&-label {
				width: 80px;
			}

			&-separator {
				text-align: center;
				width: 20px;
			}
		}

		.support_agents-chip {
			padding: 10px;
			cursor: pointer;

			.shapla-chip.is-active {
				background: #f68638;
				color: #fff;
			}
		}
	}

	.support-ticket-log {

	}

	.shapla-widget-box {
		margin-bottom: 1.5rem;

		&__heading {
			align-items: center;
			border-bottom: 1px solid rgba(#000, 0.2);
			display: flex;
			justify-content: space-between;
			padding-bottom: 10px;
			margin-bottom: 10px;
		}

		&__title {
			margin: 0;
			padding: 0;
		}
	}

	.shapla-chip {
		height: 32px;
		line-height: 32px;
		border: 0;
		border-radius: 16px;
		background-color: #f1f1f1;
		display: inline-block;
		color: rgba(0, 0, 0, .87);
		margin: 2px 0;
		font-size: 0;
		white-space: nowrap;
		padding: 0 12px 0 0;

		&:not(:last-child) {
			margin-right: 10px;
		}

		&__contact {
			height: 32px;
			width: 32px;
			border-radius: 16px;
			margin-right: 8px;
			font-size: 18px;
			line-height: 32px;
			display: inline-block;
			vertical-align: middle;
			overflow: hidden;
			text-align: center;
		}

		&__text {
			font-size: 13px;
			vertical-align: middle;
			display: inline-block;
		}
	}

	.shapla-thread {
		&--log {
			background-color: rgba(#f68638, 0.2);
			border: 1px solid rgba(#000, 0.2);
			border-radius: 6px;
			margin: 1.5rem auto;
			padding: 1.5rem;
			text-align: center;
			max-width: 600px;
		}

		&--report {
			background-color: #fff;
		}

		&--note {
			background-color: #fffdf5;
		}

		&--reply {
			background-color: #f5fffd;
		}

		&--sms {
			background-color: rgba(#ff3860, .1);
		}

		&--sms,
		&--email,
		&--report,
		&--note,
		&--reply {
			border: 1px solid rgba(#000, 0.2);
			border-radius: 6px;
			display: flex;
			margin: 1.5rem 0;
			padding: 1.5rem;

			> * {
				flex-grow: 1;
			}

			.shapla-thread__avatar {
				width: 50px;
				margin-right: 10px;
			}

			.shapla-thread__actions {
				width: 62px;
			}

			.shapla-thread__customer_name {
				font-weight: bold;
			}

			.shapla-thread__content {
				width: calc(100% - 100px);

				table {
					margin-bottom: 0;
				}

				&-top {
					border-bottom: 1px solid rgba(#000, 0.2);
					display: flex;
					margin-bottom: 10px;
					padding-bottom: 10px;
				}

				&-align-left,
				&-align-right {
				}

				&-align-left {
					display: flex;
					flex-grow: 1;
				}
			}
		}

		&__time {
			display: block;
		}
	}

	.table--support-order {
		td {
			padding: 8px;
		}
	}

	.stackont-support-ticket-nav {
		display: flex;

		.mdl-button {
			display: inline-flex;
			align-self: center;
			justify-content: center;

			i {
				font-size: 16px;
			}
		}

		&__left,
		&__right {
			> * {
				display: inline-flex;
				padding: 5px;
				width: 32px;
				height: 32px;
				align-items: center;
				justify-content: center;
			}

			a {
				width: 100%;
				height: 100%;
				text-align: center;
				display: flex;
				justify-content: center;
				align-items: center;
			}

			.disabled {
				cursor: not-allowed;
				opacity: 0.5;
			}
		}

		&__left {
		}

		&__right {
		}
	}
</style>
