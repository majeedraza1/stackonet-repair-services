<template>
	<div class="select-issue-wrapper">

		<section-title>What can we fix for you?</section-title>

		<section-info>
			<span>Parts and labor come with a </span><span><b>lifetime guarantee.</b></span>
		</section-info>

		<label class="issue-subline">Select issue(s)</label>
		<div class="select-issue-content-container">

			<template v-if="'multiple' === screenCracked">
				<template v-for="issue in multi_issues">
					<div class="shapla-device-box shapla-device-box--issue" @click="pushIssue(issue)">
						<div class="shapla-device-box__content" :class="issueItemClass(issue)">
							<div v-text="issue.title"></div>
						</div>
					</div>
				</template>
			</template>
			<template v-else>
				<template v-for="issue in no_issues">
					<div class="shapla-device-box shapla-device-box--issue" @click="pushIssue(issue)">
						<div class="shapla-device-box__content" :class="issueItemClass(issue)">
							<div v-text="issue.title"></div>
						</div>
					</div>
				</template>
			</template>

		</div>
		<div class="select-issue-description">
			<label>Describe your issue (optional)</label>
			<textarea placeholder="Tell us what's wrong" v-text="issueDescription"
					  @input="updateDescription($event)"></textarea>
		</div>
		<div class="select-issue-continue-wrapper">
			<big-button :disabled="!showContinueButton" @click="handleContinue">Continue</big-button>
		</div>

		<section-help></section-help>
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';
	import SectionTitle from '../components/SectionTitle'
	import SectionInfo from '../components/SectionInfo'
	import SectionHelp from '../components/SectionHelp'
	import {mapState} from 'vuex';

	export default {
		name: "deviceIssue",
		components: {BigButton, SectionTitle, SectionInfo, SectionHelp},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SHOW_CART', true);
			this.$store.commit('IS_THANK_YOU_PAGE', false);

			// If no models, redirect one step back
			if (!this.isScreenCracked) {
				this.$router.push('/screen-cracked');
			}
		},
		data() {
			return {}
		},
		computed: {
			...mapState(['screenCracked', 'device', 'issues', 'issueDescription']),
			isScreenCracked() {
				return !!(this.screenCracked && (this.screenCracked === 'no' || this.screenCracked === 'multiple'));
			},
			no_issues() {
				if (this.device && this.device.no_issues) {
					return this.device.no_issues;
				}

				return [];
			},
			multi_issues() {
				if (this.device && this.device.multi_issues) {
					return this.device.multi_issues;
				}

				return [];
			},
			showContinueButton() {
				return (this.issues && this.issues.length);
			}
		},
		methods: {
			issueItemClass(issue) {
				return {'is-active': -1 !== this.issues.indexOf(issue),}
			},
			updateDescription(event) {
				this.$store.commit('SET_ISSUE_DESCRIPTION', event.target.value);
			},
			pushIssue(issue) {
				let issues = this.issues, index = issues.indexOf(issue);
				if (-1 === index) {
					issues.push(issue);
				} else {
					issues.splice(index, 1);
				}

				this.$store.commit('SET_ISSUE', issues);
			},
			isActive(issue) {
				return {
					'is-active': this.issues.indexOf(issue) !== false,
				};
			},
			handleContinue() {
				this.$router.push('/select-time');
			}
		}
	}
</script>

<style lang="scss">
	.issue-subline,
	.select-issue-description label {
		text-align: center;
		display: block;
		font-size: 13px;
		margin-top: 20px;
		margin-bottom: 10px;
	}

	.issue-subline {
		margin-top: 0;
	}

	.shapla-device-box--issue {
		.shapla-device-box__content {
			height: 75px;
			padding: 10px;
			text-align: center;

			&.is-active {
				border: 2px solid #f58730;
			}
		}
	}

	.select-issue-description {
		width: 350px;
		display: block;
		margin: 0 auto;

		label {
			text-align: center;
			display: block;
			font-size: 13px;
			margin-top: 20px;
			margin-bottom: 10px;
		}

		textarea {
			width: 350px;
			box-sizing: border-box;
			height: auto;
			resize: vertical;
			padding: 15px;
			font-size: 15px;
			border: none;
			border-radius: 6px;
		}
	}

	.select-issue-continue-wrapper {
		height: 64px;
		width: 280px;
		margin: 20px auto 50px;
	}
</style>
