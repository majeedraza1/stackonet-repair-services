<template>
	<div>
		<wp-pagination
			size="medium"
			:total_items="pagination.totalCount"
			:per_page="limit"
			:current_page="pagination.currentPage"
			@pagination="paginate"
		></wp-pagination>
		<div v-for="item in items" class="checkout-analysis-item" @click="toggleTable($event)">
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
			<table class="mdl-data-table is-hidden">
				<tr v-for="_item in item.steps">
					<td class="mdl-data-table__cell--non-numeric">{{_item.label}}</td>
					<td class="mdl-data-table__cell--non-numeric">
						<template v-if="_item.active">
							<span><strong>Date:</strong> {{_item.date}}<br></span>
							<span><strong>Time:</strong> {{_item.time}}<br></span>

							<template v-if="_item.label === 'Time'">
								<span v-if="_item.value">
									<strong>Date:</strong> {{_item.value.date}}
								</span>
								<span v-if="_item.value">
									<strong>Time Range:</strong> {{_item.value.time_range}}
								</span>
							</template>
							<template v-else-if="_item.label === 'User Details' || _item.label === 'User Info'">
								<span v-if="_item.value && _item.value.first_name">
									<strong>First Name:</strong> {{_item.value.first_name}}
								</span>
								<span v-if="_item.value && _item.value.last_name">
									<strong>Last Name:</strong> {{_item.value.last_name}}
								</span>
								<span v-if="_item.value && _item.value.email">
									<strong>Email:</strong> {{_item.value.email}}
								</span>
								<span v-if="_item.value && _item.value.phone">
									<strong>Phone:</strong> {{_item.value.phone}}
								</span>
							</template>
							<template v-else-if="_item.label === 'Issues'">
								<span>
									<strong>Issues:</strong>
									<template v-for="(_issue, index) in _item.value">
										<template v-if="index !== 0">,</template>
										{{_issue.title}}
									</template>
								</span>
							</template>
							<template v-else>
								<span v-if="_item.value">
									<strong>{{_item.label}}:</strong> {{_item.value}}
								</span>
							</template>

						</template>
						<template v-else> -</template>
					</td>
				</tr>
			</table>
		</div>
	</div>
</template>

<script>
	import axios from 'axios'
	import {columns, column} from "shapla-columns";
	import StepProgressBar from "../../../components/StepProgressBar";
	import Box from "../../../shapla/box/box";
	import WpPagination from "../../../wp/wpPagination";

	export default {
		name: "CheckoutAnalysis",
		components: {WpPagination, Box, column, columns, StepProgressBar},
		data() {
			return {
				items: [],
				pagination: {},
				currentPage: 1,
				limit: 20,
			}
		},
		mounted() {
			this.$store.commit('SET_TITLE', 'Checkout Analysis');
			this.getItems();
		},
		methods: {
			getItems() {
				let self = this;
				this.$store.commit('SET_LOADING_STATUS', true);
				axios
					.get(PhoneRepairs.rest_root + '/checkout-analysis?paged=' + self.currentPage + '&per_page=' + self.limit)
					.then((response) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						self.items = response.data.data.items;
						self.pagination = response.data.data.pagination;
					})
					.catch((error) => {
						this.$store.commit('SET_LOADING_STATUS', false);
						alert('Some thing went wrong. Please try again.');
					});
			},
			paginate(page) {
				this.currentPage = page;
				this.getItems();
			},
			toggleTable(event) {
				let items = this.$el.querySelectorAll('.checkout-analysis-item'),
					item = event.target.closest('.checkout-analysis-item');

				item.querySelector('.mdl-data-table').classList.toggle('is-hidden');
				items.forEach(element => {
					if (element !== item) {
						element.querySelector('.mdl-data-table').classList.add('is-hidden');
					}
				});
			}
		}
	}
</script>

<style lang="scss">
	.mdl-data-table.is-hidden {
		display: none;
	}

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
