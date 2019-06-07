<template>
	<div>
		<template v-for="item in items">
			<columns gapless>
				<column :desktop="2">
					<box class="button--ip-address">
						<strong>IP: </strong>{{item.ip_address}}<br>
						<span v-if="item.city"><strong>City: </strong>{{item.city}}<br></span>
						<span v-if="item.postal_code"><strong>Zip: </strong>{{item.postal_code}}<br></span>
					</box>
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
	import Box from "../../shapla/box/box";

	export default {
		name: "CheckoutAnalysis",
		components: {Box, Column, Columns, StepProgressBar},
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

	.button--ip-address {
		background-image: linear-gradient(to right, #f9a73b 0%, #f58730 100%);
		font-weight: bold;
		text-align: center;

		strong {
		}

		&:hover {
			background-image: radial-gradient(0, #f9a73b 0%, #f58730 100%);
		}
	}
</style>
