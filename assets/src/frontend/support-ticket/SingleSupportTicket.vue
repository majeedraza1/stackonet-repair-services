<template>
	<div class="stackont-single-support-ticket-container">

		<div class="stackont-single-support-ticket-actions">
			<mdl-button type="raised" color="primary">+ New Ticket</mdl-button>
			<mdl-button type="raised" color="default" @click="ticketList">Ticket List</mdl-button>
		</div>

		<columns>
			<column :desktop="8">

				<div class="stackont-single-ticket-content">

					<div class="stackont-single-ticket__heading">
						<h4 class="stackont-single-ticket__title">[Ticket #{{item.id}}] {{item.ticket_subject}}</h4>
						<icon><i class="fa fa-pencil-square-o" aria-hidden="true"></i></icon>
					</div>

					<div>
						<editor :init="mce" v-model="content"></editor>
						<div style="text-align: right;margin-top:10px;" v-show="content.length">
							<mdl-button type="raised" color="default">Add Note</mdl-button>
							<mdl-button type="raised" color="primary">Submit Reply</mdl-button>
						</div>
					</div>

					<div class="shapla-thread-container">
						<template v-for="thread in threads">

							<template v-if="thread.thread_type === 'log'">
								<div class="shapla-thread shapla-thread--log">
									<div class="shapla-thread__content">
										<span v-html="thread.thread_content"></span>
										<small class="shapla-thread__time">reported {{thread.human_time}} ago</small>
									</div>
								</div>
							</template>

							<template v-else>
								<div :class="threadClass(thread.thread_type)">
									<div class="shapla-thread__avatar">
										<image-container>
											<img :src="thread.customer_avatar_url" width="32" height="32">
										</image-container>
									</div>
									<div class="shapla-thread__content">
										<div class="shapla-thread__content-top">
											<div style="display: flex;align-items: center;">
												<span
													class="shapla-thread__customer_name">{{thread.customer_name}}</span>
												<small class="shapla-thread__time">&nbsp;
													<template v-if="thread.thread_type === 'note'">added note</template>
													<template v-else-if="thread.thread_type === 'reply'">replied
													</template>
													<template v-else>reported</template>
													{{thread.human_time}} ago
												</small>
											</div>
											<span class="shapla-thread__customer_email">{{thread.customer_email}}</span>
										</div>
										<div v-html="thread.thread_content"></div>
										<div class="shapla-thread__content-attachment" v-if="thread.attachments.length">
											<strong>Attachments :</strong>
											<table>
												<tr v-for="attachment in thread.attachments">
													<td>
														<a target="_blank" :href="attachment.download_url">{{attachment.filename}}</a>
													</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="shapla-thread__actions">
										<icon medium><i @click="openThreadEditor(thread)" class="fa fa-pencil-square-o"
														aria-hidden="true"></i></icon>
										<icon medium><i @click="deleteThread(thread)" class="fa fa-trash-o"
														aria-hidden="true"></i></icon>
									</div>
								</div>
							</template>

						</template>
					</div>
				</div>

			</column>

			<column :desktop="4">

				<div class="shapla-box shapla-widget-box">
					<div class="shapla-widget-box__heading">
						<h5 class="shapla-widget-box__title">Status</h5>
						<a href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
					</div>
					<div class="shapla-widget-box__content" v-if="Object.keys(item).length">
						<list-item label="Status">{{item.status.name}}</list-item>
						<list-item label="Category">{{item.category.name}}</list-item>
						<list-item label="Priority">{{item.priority.name}}</list-item>
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
						<h5 class="shapla-widget-box__title">Assign Agent(s)</h5>
						<a href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
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

			</column>
		</columns>

		<modal :active="activeThreadModal" title="Edit this Thread" @close="closeThreadEditor">
			<editor :init="mce" v-model="activeThreadContent"></editor>
			<template slot="foot">
				<mdl-button @click="updateThread">Save</mdl-button>
			</template>
		</modal>
	</div>
</template>

<script>
	import axios from 'axios';
	import Editor from '@tinymce/tinymce-vue'
	import mdlButton from '../../material-design-lite/button/mdlButton'
	import ListItem from '../../components/ListItem'
	import columns from '../../shapla/columns/columns'
	import column from '../../shapla/columns/column'
	import ImageContainer from "../../shapla/image/image";
	import Icon from "../../shapla/icon/icon";
	import modal from '../../shapla/modal/modal'

	export default {
		name: "SingleSupportTicket",
		components: {Icon, ImageContainer, mdlButton, columns, column, ListItem, Editor, modal},
		data() {
			return {
				loading: false,
				activeThreadModal: false,
				activeThread: {},
				activeThreadContent: '',
				id: 0,
				content: '',
				item: {},
				threads: []
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
		},
		mounted() {
			let id = this.$route.params.id;
			if (id) {
				this.id = parseInt(id);
				this.getItem();
			}
		},
		methods: {
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
			updateThread() {
				this.activeThreadModal = false;
				this.activeThread = {};
				this.activeThreadContent = '';
				alert('It is not ready yet.');
			},
			deleteThread(thread) {
				if (confirm('Are you sure to delete this thread?')) {
					alert('It is not ready yet.');
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
			getItem() {
				let self = this;
				self.loading = true;
				axios
					.get(PhoneRepairs.rest_root + '/support-ticket/' + self.id)
					.then((response) => {
						self.loading = false;
						self.item = response.data.data.ticket;
						self.threads = response.data.data.threads;
					})
					.catch((error) => {
						self.loading = false;
					});
			}
		}
	}
</script>

<style lang="scss">
	.stackont-single-support-ticket-container {
		margin-top: 50px;
		margin-bottom: 50px;

		.stackont-single-support-ticket-actions {
			display: flex;
			border-bottom: 1px solid rgba(#000, 0.1);
			padding-bottom: 1.5rem;
			margin-bottom: 1.5rem;

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
			}

			.shapla-thread__actions {
				width: 62px;
			}

			.shapla-thread__customer_name {
				font-weight: bold;
			}

			.shapla-thread__content {
				padding-left: 16px;
				padding-right: 16px;
				width: calc(100% - 100px);

				table {
					margin-bottom: 0;
				}

				&-top {
					border-bottom: 1px solid rgba(#000, 0.2);
					padding-bottom: 10px;
					display: flex;
					flex-direction: column;
					margin-bottom: 10px;
				}
			}
		}

		&__time {
			display: block;
		}
	}
</style>
