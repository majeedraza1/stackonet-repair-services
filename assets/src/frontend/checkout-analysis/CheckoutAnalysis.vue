<template>
	<div>
		<template v-for="item in items">
			<columns>
				<column :desktop="2">
					<div>{{item.ip_address}}</div>
				</column>
				<column :desktop="10">
					<step-progress-bar :steps="item.steps" :selected="item.steps_percentage"></step-progress-bar>
				</column>
			</columns>
		</template>
	</div>
</template>

<script>
	import axios from 'axios'
	import StepProgressBar from "../../components/StepProgressBar";
	import Columns from "../../shapla/columns/columns";
	import Column from "../../shapla/columns/column";

	export default {
		name: "CheckoutAnalysis",
		components: {Column, Columns, StepProgressBar},
		data() {
			return {
				loading: false,
				items: [],
			}
		},
		mounted() {
			this.getItems();
		},
		methods: {
			getItems() {
				let self = this;
				self.loading = true;
				axios
					.get(PhoneRepairs.rest_root + '/checkout-analysis', {},
						{headers: {'X-WP-Nonce': window.PhoneRepairs.rest_nonce},})
					.then((response) => {
						self.loading = false;
						self.items = response.data.data.items;
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
	.display-flex {
		display: flex;
	}
</style>
