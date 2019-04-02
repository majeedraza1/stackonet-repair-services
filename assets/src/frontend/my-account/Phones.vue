<template>
	<div>
		<div class="action-button-container">
			<button class="button button--primary button-add-new-phone"
					@click="isModalActive = true">Add New
			</button>
		</div>
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
		<mdl-modal :active="isModalActive" :title="modalTitle" @close="isModalActive =false">
			<div class="columns">
				<div class="column is-6">
					<animated-input v-model="phone.asset_number" label="Asset Number"></animated-input>
				</div>
				<div class="column is-6">
					<animated-input v-model="phone.brand_name" label="Brand Name"></animated-input>
				</div>
				<div class="column is-6">
					<animated-input v-model="phone.model" label="Model"></animated-input>
				</div>
				<div class="column is-6">
					<animated-input v-model="phone.color" label="Color"></animated-input>
				</div>
				<div class="column is-6">
					<animated-input v-model="phone.imei_number" label="IMEI Number"></animated-input>
				</div>
				<div class="column is-6">
					<animated-input v-model="phone.issues" label="Issues"></animated-input>
				</div>
				<div class="column is-6">
					<animated-input v-model="phone.is_broken_screen" label="Is Screen broken?"></animated-input>
				</div>
			</div>
			<div slot="foot">
				<mdl-button type="raised" color="primary">Save</mdl-button>
			</div>
		</mdl-modal>
	</div>
</template>

<script>
	import mdlModal from '../../material-design-lite/modal/mdlModal.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';
	import AnimatedInput from '../../components/AnimatedInput.vue';

	export default {
		name: "Phones",
		components: {mdlModal, AnimatedInput, mdlButton},
		data() {
			return {
				isModalActive: false,
				modalTitle: 'Add New Phone',
				phone: {
					asset_number: '',
					brand_name: '',
					model: '',
					color: '',
					imei_number: '',
					issues: '',
					is_broken_screen: '',
				}
			}
		}
	}
</script>

<style lang="scss">
	.action-button-container {
		width: 100%;
		margin-bottom: 1rem;
		display: flex;
		justify-content: flex-end;

		.button-add-new-phone {
			float: right;
		}
	}
</style>
