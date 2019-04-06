<template>
	<div>
		<h4>New</h4>
		<table class="shop_table shop_table_responsive my_account_track_status_new">
			<thead>
			<tr>
				<th class="woocommerce-orders-table__header cell-device">
					<span class="nobr">Device</span>
				</th>
				<th class="woocommerce-orders-table__header cell-issues">
					<span class="nobr">Issues</span>
				</th>
				<th class="woocommerce-orders-table__header cell-broken-screen">
					<span class="nobr">LCD Broken?</span>
				</th>
				<th class="woocommerce-orders-table__header cell-status">
					<span class="nobr">Status</span>
				</th>
			</tr>
			</thead>
			<tbody>
			<template v-if="has_new">
				<tr class="woocommerce-orders-table__row" v-for="phone in new_phones">
					<td class="woocommerce-orders-table__cell cell-device" data-title="Device">
						{{phone.brand_name}} {{phone.model}}
					</td>
					<td class="woocommerce-orders-table__cell cell-issues" data-title="Issues">
						{{phone.issues.join(', ')}}
					</td>
					<td class="woocommerce-orders-table__cell cell-broken-screen" data-title="LCD Broken?">
						{{phone.broken_screen}}
					</td>
					<td class="woocommerce-orders-table__cell cell-status" data-title="Status">
						<button class="woocommerce-button button" :class="phone.status">
							{{phone_statuses[phone.status]?phone_statuses[phone.status]:phone.status}}
						</button>
					</td>
				</tr>
			</template>
			<template v-else>
				<tr class="woocommerce-orders-table__row">
					<td colspan="4" style="text-align: center;">Now new track status found.</td>
				</tr>
			</template>
			</tbody>
		</table>

		<h4>Phones with Phone Repairs ASAP</h4>
		<table class="shop_table shop_table_responsive my_account_track_status_old">
			<thead>
			<tr>
				<th class="woocommerce-orders-table__header cell-device">
					<span class="nobr">Device</span>
				</th>
				<th class="woocommerce-orders-table__header cell-issues">
					<span class="nobr">Issues</span>
				</th>
				<th class="woocommerce-orders-table__header cell-broken-screen">
					<span class="nobr">LCD Broken?</span>
				</th>
				<th class="woocommerce-orders-table__header cell-status">
					<span class="nobr">Status</span>
				</th>
			</tr>
			</thead>

			<tbody>
			<template v-if="has_old">
				<tr class="woocommerce-orders-table__row" v-for="phone in old_phones">
					<td class="woocommerce-orders-table__cell cell-device" data-title="Device">
						{{phone.brand_name}} {{phone.model}}
					</td>
					<td class="woocommerce-orders-table__cell cell-issues" data-title="Issues">
						{{phone.issues.join(', ')}}
					</td>
					<td class="woocommerce-orders-table__cell cell-broken-screen" data-title="LCD Broken?">
						{{phone.broken_screen}}
					</td>
					<td class="woocommerce-orders-table__cell cell-status" :class="`cell-${phone.status}`"
						data-title="Status">
						<button class="woocommerce-button button" :class="phone.status">
							{{phone_statuses[phone.status]?phone_statuses[phone.status]:phone.status}}
						</button>
					</td>
				</tr>
			</template>
			<template v-else>
				<tr class="woocommerce-orders-table__row">
					<td colspan="4" style="text-align: center;">Now track status found.</td>
				</tr>
			</template>
			</tbody>
		</table>
	</div>
</template>

<script>
	export default {
		name: "TrackStatus",
		data() {
			return {
				loading: true,
				new_phones: [],
				old_phones: [],
			}
		},
		computed: {
			has_new() {
				return !!this.new_phones.length;
			},
			has_old() {
				return !!this.old_phones.length;
			},
			phone_statuses() {
				return StackonetRentCenter.phone_statuses;
			}
		},
		mounted() {
			this.getNewPhones();
			this.getOldPhones();
		},
		methods: {
			getNewPhones() {
				let $ = window.jQuery, self = this;
				self.loading = true;
				$.ajax({
					method: 'GET',
					url: window.PhoneRepairs.rest_root + '/track-status',
					data: {
						type: 'new',
						paged: 1,
						per_page: 50,
					},
					success: function (response) {
						if (response.data) {
							self.new_phones = response.data.items;
						}
						self.loading = false;
					},
					error: function () {
						self.loading = false;
					}
				});
			},
			getOldPhones() {
				let $ = window.jQuery, self = this;
				self.loading = true;
				$.ajax({
					method: 'GET',
					url: window.PhoneRepairs.rest_root + '/track-status',
					data: {
						type: 'old',
						paged: 1,
						per_page: 50,
					},
					success: function (response) {
						if (response.data) {
							self.old_phones = response.data.items;
						}
						self.loading = false;
					},
					error: function () {
						self.loading = false;
					}
				});
			},
		}
	}
</script>

<style lang="scss">
	.my_account_track_status_new,
	.my_account_track_status_old {
		.cell-status {
			.button {
				width: 100%;
			}
		}

		@media screen and (min-width: 768px) {
			.cell-status {
				width: 150px;
			}
			.cell-broken-screen {
				width: 115px;
			}
			.cell-device {
				width: 120px;
			}
		}
	}

	.my_account_track_status_new {

	}

	.my_account_track_status_old {
	}

	.woocommerce-button.button {
		&.processing {
			background: #ff823f;
			color: #fff;
		}

		&.arriving-soon,
		&.repairing {
			background-color: #ffde00;
			color: #444;
		}

		&.picked-off,
		&.not-repaired {
			background-color: #079d00;
			color: #fff;
		}

		&.not-picked-off,
		&.delivered {
			background-color: #fe0000;
			color: #fff;
		}
	}
</style>
