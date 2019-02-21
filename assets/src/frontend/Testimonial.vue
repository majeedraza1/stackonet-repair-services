<template>
	<div>
		<form action="#" method="post" accept-charset="UTF-8" @submit.prevent="handleSubmit">
			<animated-input
					id="full_name"
					v-model="full_name"
					label="Name"
					:has-success="!!full_name.length"
			></animated-input>
			<animated-input
					id="description"
					v-model="description"
					label="Description"
					:has-success="!!description.length"
			></animated-input>
			<star-rating v-model="rating"></star-rating>
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
			></animated-input>
			<big-button @click.prevent="handleSubmit">Submit</big-button>
		</form>
	</div>
</template>

<script>
	import StarRating from '../components/StarRating';
	import AnimatedInput from '../components/AnimatedInput';
	import BigButton from '../components/BigButton';

	export default {
		name: "Testimonial",
		components: {AnimatedInput, StarRating, BigButton},
		data() {
			return {
				full_name: '',
				email: '',
				description: '',
				phone: '',
				rating: null,
			}
		},
		methods: {
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
					}
				});
			}
		}
	}
</script>

<style scoped>

</style>
