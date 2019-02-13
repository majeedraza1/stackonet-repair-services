<template>
	<div class="repair-services-device">
		<h1 class="wp-heading-inline">Device</h1>

		<accordion :active="true" title="Brand">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="product_id">Product</label></th>
					<td>
						<select id="product_id" class="regular-text" v-model="product_id" @change="updateBrandName">
							<option value="-1">Choose Product</option>
							<option v-for="product in products" :value="product.value">{{product.label}}</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="device_title">Brand Name</label></th>
					<td>
						<input name="device_title" type="text" id="device_title" class="regular-text"
							   v-model="device_title">
					</td>
				</tr>
				<tr>
					<th scope="row"><label>Brand Image</label></th>
					<td>
						<div style="max-width: 25em;">
							<background-image v-model="device_image"></background-image>
						</div>
					</td>
				</tr>
			</table>
		</accordion>
		<accordion :active="true" title="Device Models">
			<p>
				<button class="button" @click="addNewDeviceModel">Add New</button>
			</p>
			<template v-for="(model, index) in device_models">
				<accordion :title="model.title" :active="false">
					<div class="columns">
						<div class="column is-3">
							<p>
								<strong>Model Name</strong>
								<input type="text" class="widefat" v-model="model.title">
							</p>
							<p>
								<strong>Screen Broken Price</strong>
								<small>(If provided, this will overwrite device issue value.)</small>
								<input type="number" class="widefat" v-model="model.broken_screen_price">
							</p>
						</div>
						<div class="column is-9">
							<strong>Colors</strong>
							<table class="rs-table">
								<thead>
								<tr>
									<td>Title</td>
									<td>Subtitle</td>
									<td>Color</td>
								</tr>
								</thead>
								<tbody>
								<tr v-for="(color, colorIndex) in model.colors" :key="colorIndex">
									<td>
										<label class="screen-reader-text">Color Title</label>
										<input type="text" class="widefat" v-model="color.title">
									</td>
									<td>
										<label class="screen-reader-text">Color Subtitle</label>
										<input type="text" class="widefat" v-model="color.subtitle">
									</td>
									<td>
										<label class="screen-reader-text">Color Code</label>
										<color-picker v-model="color.color"></color-picker>
									</td>
								</tr>
								</tbody>
							</table>
							<p>
								<button class="button" @click="addColor(model, index)">Add Color</button>
							</p>
						</div>
					</div>
				</accordion>
			</template>
		</accordion>
		<accordion :active="true" title="Device Issue">
			<div class="columns">
				<div class="column">
					<div class="device-issue-inside">
						<h4>Screen Broken</h4>
						<div>
							<label for="broken_screen_label">Label on Shop</label>
							<input id="broken_screen_label" type="text" class="widefat" v-model="broken_screen_label">
						</div>
						<div>
							<label for="broken_screen_price">Price</label>
							<input id="broken_screen_price" type="number" class="widefat" v-model="broken_screen_price">
						</div>
					</div>
				</div>
				<div class="column">
					<div class="device-issue-inside">
						<div>
							<h4>Screen Not Broken</h4>
							<button class="button" @click="showNotCrackedModel = true">Choose Issue</button>
						</div>
						<table class="rs-table">
							<thead>
							<tr>
								<td>&nbsp;</td>
								<td>Issue</td>
								<td>Price</td>
							</tr>
							</thead>
							<tbody>
							<tr v-for="(device_issue, index) in no_issues" :key="device_issue.id">
								<td>
									<delete @click="removeIssue(no_issues,device_issue, index)"></delete>
								</td>
								<td>{{device_issue.title}}</td>
								<td>
									<input type="text" v-model="device_issue.price">
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="column">
					<div class="device-issue-inside">
						<div>
							<h4>Multiple Issue</h4>
							<button class="button" @click="showModel = true">Choose Issue</button>
						</div>
						<table class="rs-table">
							<thead>
							<tr>
								<td>&nbsp;</td>
								<td>Issue</td>
								<td>Price</td>
							</tr>
							</thead>
							<tbody>
							<tr v-for="(device_issue, index) in multi_issues" :key="device_issue.id">
								<td>
									<delete @click="removeIssue(multi_issues, device_issue, index)"></delete>
								</td>
								<td>{{device_issue.title}}</td>
								<td>
									<input type="text" v-model="device_issue.price">
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</accordion>
		<mdl-modal :active="showModel" @close="closeModel" title="Issues">
			<template v-for="issue in issues">
				<mdl-checkbox v-model="multi_issues" :value="issue">{{issue.title}}</mdl-checkbox>
			</template>
			<template slot="foot">
				<button class="button" @click="closeModel">Ok</button>
			</template>
		</mdl-modal>
		<mdl-modal :active="showNotCrackedModel" @close="closeModel" title="Issues">
			<template v-for="issue in issues">
				<mdl-checkbox v-model="no_issues" :value="issue">{{issue.title}}</mdl-checkbox>
			</template>
			<template slot="foot">
				<button class="button" @click="closeModel">Ok</button>
			</template>
		</mdl-modal>
		<mdl-fab @click="saveDeviceData">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
				<path fill="none" d="M0 0h24v24H0V0z"></path>
				<path fill="white"
					  d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm2 16H5V5h11.17L19 7.83V19zm-7-7c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zM6 6h9v4H6z"></path>
			</svg>
		</mdl-fab>
	</div>
</template>

<script>
	import Accordion from '../../components/Accordion.vue';
	import MediaUploader from '../../components/MediaUploader.vue';
	import BackgroundImage from '../../components/BackgroundImage.vue';
	import Delete from '../../components/Delete.vue';
	import ColorPicker from '../../components/ColorPicker.vue';
	import mdlModal from '../../material-design-lite/modal/mdlModal';
	import mdlCheckbox from '../../material-design-lite/checkbox/mdlCheckbox';
	import mdlFab from '../../material-design-lite/button/mdlFab';

	export default {
		name: "Device",
		components: {Accordion, MediaUploader, BackgroundImage, ColorPicker, mdlModal, mdlCheckbox, mdlFab, Delete},
		data() {
			return {
				id: 0,
				isEditPage: false,
				showCrackedModel: false,
				showNotCrackedModel: false,
				showModel: false,
				device_image: {},
				device_title: '',
				device_models: [],
				yes_issues: [],
				no_issues: [],
				multi_issues: [],
				product_id: '-1',
				broken_screen_label: 'Broken Screen',
				broken_screen_price: '',
			}
		},
		computed: {
			loading() {
				return this.$store.state.loading;
			},
			issues() {
				return this.$store.state.issues;
			},
			products() {
				return this.$store.state.products;
			},
			devices() {
				return this.$store.state.devices;
			}
		},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			if (!this.issues.length) {
				this.fetchIssues();
			}
			if (!this.products.length) {
				this.fetchProducts();
			}
			if (this.$route.params.id) {
				this.id = this.$route.params.id;
				this.isEditPage = true;
				this.fetchDevice(this.id);
			}
		},
		methods: {
			closeModel() {
				this.showModel = false;
				this.showNotCrackedModel = false;
			},
			removeIssue(issues, issue, index) {
				issues.splice(index, 2);
			},
			addNewDeviceModel() {
				let data = {
					title: 'Undefined',
					broken_screen_price: '',
					colors: [],
				};

				this.device_models.push(data);
			},
			addColor(model, index) {
				let data = {
					color: '',
					title: '',
					subtitle: '',
				};
				model.colors.push(data);
			},
			updateBrandName() {

			},
			saveDeviceData() {
				let self = this, $ = window.jQuery;
				self.$store.commit('SET_LOADING_STATUS', true);

				$.ajax({
					method: 'POST',
					url: ajaxurl,
					data: {
						action: 'create_device',
						id: self.id,
						product_id: self.product_id,
						device_title: self.device_title,
						device_image: self.device_image.id,
						device_models: self.device_models,
						broken_screen_label: self.broken_screen_label,
						broken_screen_price: self.broken_screen_price,
						multi_issues: self.multi_issues,
						no_issues: self.no_issues,
					},
					success: function (response) {
						self.$store.commit('SET_LOADING_STATUS', false);
						if (response.data) {
							if (!self.isEditPage) {
								let devices = self.devices;
								devices.push(response.data);
								self.$store.commit('SET_DEVICES', devices);
								self.$router.push('/');
							}

							self.$root.$emit('show-snackbar', {
								message: 'Date has been saved.',
							});
						}
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			fetchIssues() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_device_issues',
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_ISSUES', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			fetchProducts() {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_woocommerce_products',
					},
					success: function (response) {
						if (response.data) {
							self.$store.commit('SET_PRODUCTS', response.data);
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			},
			fetchDevice(device_id) {
				let $ = window.jQuery, self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				$.ajax({
					method: 'GET',
					url: ajaxurl,
					data: {
						action: 'get_device',
						id: device_id,
					},
					success: function (response) {
						if (response.data) {
							console.log(response.data);
							// self.$store.commit('SET_PRODUCTS', response.data);
							let data = response.data;

							self.id = data.id;
							self.product_id = data.product_id;
							self.device_title = data.device_title;
							self.device_models = data.device_models;
							self.broken_screen_label = data.broken_screen_label;
							self.broken_screen_price = data.broken_screen_price;
							self.multi_issues = data.multi_issues;
							self.no_issues = data.no_issues;
							self.device_image = data.image;
						}
						self.$store.commit('SET_LOADING_STATUS', false);
					},
					error: function () {
						self.$store.commit('SET_LOADING_STATUS', false);
					}
				});
			}
		}
	}
</script>

<style lang="scss">
	.columns {
		display: flex;
		margin: -1rem;

		.column {
			padding: 1rem;
			flex-grow: 1;

			&.is-3 {
				width: 25%;
			}

			&.is-9 {
				width: 75%;
			}
		}
	}

	.rs-table {
		border-collapse: collapse;
		margin-top: 0.5em;
		width: 100%;
		clear: both;

		th, td {
			padding: 10px;
		}

		thead {
			td, th {
				background: #f1f1f1;
			}
		}

		tbody {
			th, td {
				border-bottom: 1px solid #f1f1f1;
			}
		}
	}

	.device-issue-inside {
		border: 1px solid #ddd;
		display: flex;
		flex-direction: column;
		height: 100%;
		padding: 0.5rem;

		h4 {
			margin-top: 0;
			display: inline-block;
			margin-right: 1rem;
			font-size: 16px;
		}
	}

	.mdl-button--fab {
		position: fixed;
		bottom: 20px;
		right: 20px;
		z-index: 100;
	}
</style>
