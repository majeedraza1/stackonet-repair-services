<template>
	<div style="margin: 100px auto;">
		<mdl-table :columns="columns" :rows="rows"></mdl-table>
	</div>
</template>

<script>
	import mdlTable from '../../material-design-lite/data-table/mdlTable'

	export default {
		name: "SupportTicket",
		components: {mdlTable},
		data() {
			return {
				columns: [
					{key: 'material', label: 'Material', numeric: false},
					{key: 'quantity', label: 'Quantity', numeric: true},
					{key: 'unit_price', label: 'Unit price', numeric: true},
				],
				rows: [
					{id: 1, material: 'Acrylic (Transparent)', quantity: '25', unit_price: '$2.90'},
					{id: 2, material: 'Acrylic (Transparent)', quantity: '25', unit_price: '$2.90'},
					{id: 3, material: 'Acrylic (Transparent)', quantity: '25', unit_price: '$2.90'},
				],
			}
		}
	}
</script>

<style scoped>

</style>
