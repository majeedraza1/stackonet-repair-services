<template>
	<div>
		<table class="shop_table shop_table_responsive my_account_phones">
			<thead>
			<tr>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Brand</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Device Model</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Issues</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">LCD</span>
				</th>
				<th class="woocommerce-orders-table__header">
					<span class="nobr">Actions</span>
				</th>
			</tr>
			</thead>

			<tbody>
			<tr class="woocommerce-orders-table__row">
				<td class="woocommerce-orders-table__cell" data-title="Brand">
					Apple
				</td>
				<td class="woocommerce-orders-table__cell" data-title="Device Model">
					Iphone X
				</td>
				<td class="woocommerce-orders-table__cell" data-title="Issues">
					Broken Screen
				</td>
				<td class="woocommerce-orders-table__cell" data-title="LCD">
					No
				</td>
				<td class="woocommerce-orders-table__cell" data-title="Actions">
					<a href="" class="woocommerce-button button view">Edit</a>
					<a href="" class="woocommerce-button button view">View</a>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
	export default {
		name: "TrackStatus"
	}
</script>

<style scoped>

</style>
