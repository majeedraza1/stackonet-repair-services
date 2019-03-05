<template>
	<div class="select-issue-wrapper">
		<div class="step-nav-page-wrapper">
			<div class="step-nav-wrapper"><span class="step-nav-title">What can we fix for you?</span></div>
		</div>
		<div class="info-box-wrapper">
			<div class="info-box-inner-wrapper">
				<span>Parts and labor come with a </span><span><b>lifetime guarantee.</b></span>
			</div>
		</div>
		<label class="issue-subline">Select issue(s)</label>
		<div class="select-issue-content-container">

			<template v-if="'multiple' === screenCracked">
				<template v-for="issue in multi_issues">
					<div class="scale-on-mount scale-on-mount-active" @click="pushIssue(issue)">
						<div :class="issueItemClass(issue)" v-text="issue.title"></div>
					</div>
				</template>
			</template>
			<template v-else>
				<template v-for="issue in no_issues">
					<div class="scale-on-mount scale-on-mount-active" @click="pushIssue(issue)">
						<div :class="issueItemClass(issue)" v-text="issue.title"></div>
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
	</div>
</template>

<script>
	import BigButton from '../../components/BigButton.vue';
	import {mapState} from 'vuex';

	export default {
		name: "deviceIssue",
		components: {BigButton},
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
				return {
					'select-issue-item': true,
					'select-issue-item-selected': -1 !== this.issues.indexOf(issue),
				}
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

	.select-issue-item {
		box-sizing: border-box;
		cursor: pointer;
		display: inline-block;
		height: 50px;
		width: 200px;
		margin: 10px;
		text-align: center;
		border-radius: 4px;
		background-color: #fff;
		display: -ms-flexbox;
		display: flex;
		align-items: center;
		font-size: 16px;
		color: #4a4a4a;
		border: 2px solid transparent;
		transition: all .4s;
		padding: 0 5px;

		@media (max-width: 1500px) {
			height: 45px;
			width: 130px;
			font-size: 14px;
			margin: 5px;
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

	.select-issue-item-selected {
		border: 2px solid #0161c7;
	}
</style>
