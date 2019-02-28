<template>
	<div>
		<form action="#" method="post" accept-charset="UTF-8" @submit.prevent="handleSubmit">
			<animated-input
					id="full_name"
					v-model="full_name"
					label="Name"
					:has-success="!!full_name.length"
					:has-error="!!(errors.full_name && errors.full_name.length)"
					helptext="Name is required."
					required
			></animated-input>
			<animated-input
					type="textarea"
					id="description"
					v-model="description"
					label="Description"
					:has-success="!!description.length"
					:has-error="!!(errors.description && errors.description.length)"
					helptext="Description is required."
					required
			></animated-input>
			<animated-input
					id="phone"
					v-model="phone"
					label="Phone number"
					:has-success="!!phone.length"
			></animated-input>
			<animated-input
					id="description"
					v-model="email"
					label="Email"
					:has-success="!!email.length"
					type="email"
					:has-error="!!(errors.email && errors.email.length)"
					helptext="Email is required."
					required
			></animated-input>
			<div class="star-rating-container">
				<span class="star-rating-label">Your Rating</span>
				<star-rating v-model="rating"></star-rating>
				<span class="rating-error" v-if="!!(errors.rating && errors.rating.length)">Choose a rating</span>
			</div>
			<big-button>Submit</big-button>
		</form>
		<mdl-modal :active="openModel" type="box" @close="closeModel">
			<div class="mdl-box mdl-shadow--2dp">
				<h3>Thank you for you review.</h3>
				<mdl-button @click="closeModel">Close</mdl-button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import StarRating from '../components/StarRating';
	import AnimatedInput from '../components/AnimatedInput';
	import BigButton from '../components/BigButton';
	import mdlModal from '../material-design-lite/modal/mdlModal';
	import mdlButton from '../material-design-lite/button/mdlButton';

	export default {
		name: "TestimonialForm",
		components: {AnimatedInput, StarRating, BigButton, mdlModal, mdlButton},
		data() {
			return {
				full_name: '',
				email: '',
				description: '',
				phone: '',
				rating: null,
				openModel: false,
				modelText: '',
				errors: {
					full_name: [],
					email: [],
					description: [],
					phone: [],
					rating: [],
				},
			}
		},
		methods: {
			closeModel() {
				this.openModel = false;
			},
			handleSubmit() {
				let self = this, $ = window.jQuery;
				$.ajax({
					method: "POST",
					url: window.Stackonet.ajaxurl,
					data: {
						action: 'create_client_testimonial',
						name: self.full_name,
						email: self.email,
						description: self.description,
						phone: self.phone,
						rating: self.rating,
					},
					success: function (response) {
						self.openModel = true;
						self.full_name = '';
						self.email = '';
						self.description = '';
						self.phone = '';
						self.rating = null;
						self.errors = {full_name: [], email: [], description: [], phone: [], rating: [],}
					},
					error: function (data) {
						self.errors = data.responseJSON.data
					}
				});
			}
		}
	}
</script>

<style lang="scss">
	.star-rating-container {
		padding: 0;
		background: #fff;
		margin: 0 auto 20px;

		label {
			margin-bottom: 0;
		}

		.star-rating-label {
			color: #383e42;
		}

		.star-rating__star:first-child {
			margin-left: -3px;
		}
	}

	.mdl-box {
		padding: 1rem;
		background: #ffffff;
		border-radius: 4px;
		min-height: 150px;
		align-items: center;
		justify-content: center;
		display: flex;
		flex-direction: column;
	}

	.rating-error {
		color: red;
	}
</style>
