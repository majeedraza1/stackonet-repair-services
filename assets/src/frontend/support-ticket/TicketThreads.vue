<template>
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
					<div class="shapla-thread__content">
						<div class="shapla-thread__content-top">

							<div class="shapla-thread__content-align-left">
								<div class="shapla-thread__avatar">
									<image-container>
										<img :src="thread.customer_avatar_url" width="32" height="32">
									</image-container>
								</div>

								<div>
									<div style="display: flex;align-items: center;">
												<span
													class="shapla-thread__customer_name">{{thread.customer_name}}</span>
										<small class="shapla-thread__time">&nbsp;
											<template v-if="thread.thread_type === 'note'">added note
											</template>
											<template v-if="thread.thread_type === 'sms'">sent sms
											</template>
											<template v-else-if="thread.thread_type === 'reply'">replied
											</template>
											<template v-else>reported</template>
											{{thread.human_time}} ago
										</small>
									</div>
									<span class="shapla-thread__customer_email">{{thread.customer_email}}</span>
								</div>
							</div>

							<div class="shapla-thread__content-align-right shapla-thread__actions">
								<icon medium>
									<i @click="openThreadEditor(thread)" class="fa fa-pencil-square-o"
									   aria-hidden="true"></i>
								</icon>
								<icon medium>
									<i @click="deleteThread(thread)" class="fa fa-trash-o" aria-hidden="true"></i>
								</icon>
							</div>
						</div>
						<template v-if="thread.checkout_analysis">
							<step-progress-bar
								:steps="thread.checkout_analysis.steps"
								:selected="thread.checkout_analysis.steps_percentage"
							></step-progress-bar>
						</template>
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
				</div>
			</template>

		</template>
	</div>
</template>

<script>
	import ImageContainer from "../../shapla/image/image";
	import Icon from "../../shapla/icon/icon";
	import StepProgressBar from "../../components/StepProgressBar";

	export default {
		name: "TicketThreads",
		components: {StepProgressBar, Icon, ImageContainer},
		props: {
			threads: {type: Array}
		},
		methods: {
			threadClass(thread_type) {
				return [
					'shapla-thread',
					`shapla-thread--${thread_type}`
				]
			},
		}
	}
</script>

<style scoped>

</style>
