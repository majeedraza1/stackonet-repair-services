<template>
	<div class="stackonet-survey-form">

		<div class="form-field">
			<label>Brands</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="(_brand, index) in brands" :key="index">
					<div class="shapla-survey-box hoverable" :class="{'is-active':_brand === brand}"
						 @click="chooseBrand(_brand)">
						<div>{{_brand}}</div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">
			<label>Gadgets</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="(_gadget, index) in gadgets" :key="index">
					<div class="shapla-survey-box hoverable" :class="{'is-active':_gadget === gadget}"
						 @click="chooseGadget(_gadget)">
						<div>{{_gadget}}</div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">

			<columns mobile multiline centered>
				<column :mobile="6" :tablet="6">
					<div class="shapla-survey-box hoverable" :class="{'is-active':'low' === model}"
						 @click="chooseModel('low')">
						<div>Low End Model?</div>
					</div>
				</column>
				<column :mobile="6" :tablet="6">
					<div class="shapla-survey-box hoverable" :class="{'is-active':'high' === model}"
						 @click="chooseModel('high')">
						<div>High End Model?</div>
					</div>
				</column>
			</columns>

		</div>

		<div class="form-field">
			<label>Status</label>
			<columns mobile multiline centered>
				<column :mobile="12" :tablet="12" v-for="_status in statuses" :key="_status.value">
					<div class="shapla-survey-box hoverable" @click="chooseStatus(_status.value)"
						 :class="{'is-active':_status.value === device_status}">
						<div>{{_status.label}}</div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">
			<label>If a barber could come to you in 1-2 hours anywhere, home, work, etc. What would you pay with tip for
				such a service?</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="_charge in tips_amounts" :key="_charge">
					<div class="shapla-survey-box hoverable" @click="chooseTipsAmount(_charge)"
						 :class="{'is-active':_charge === tips_amount}">
						<div>${{_charge}}</div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">
			<animated-input label="Email" type="email" v-model="email"></animated-input>
		</div>

		<div class="form-field">
			<animated-input label="Phone" type="tel" v-model="phone"></animated-input>
		</div>

		<div class="form-field">
			<g-map-autocomplete geolocation :value="formatted_address" @change="changeGeoLocation"></g-map-autocomplete>

			<p>
				<button class="button" @click="open_address_modal = true">Change Address</button>
			</p>

			<modal :active="open_address_modal" @close="open_address_modal = false" title="Address">
				<div class="formatted-address-list">
					<div
						v-for="_address in addresses"
						class="formatted-address-list_item"
						:class="{'is-active':_address.formatted_address === formatted_address}"
						@click="changeAddress(_address)"
					>
						<div>{{_address.formatted_address}}</div>
					</div>
				</div>
				<div slot="foot">
					<button @click="open_address_modal = false">Close</button>
				</div>
			</modal>
		</div>

		<div class="form-field">
			<label>Images</label>
			<p>
				<button @click="openLogoModal = true">Add Images</button>
			</p>
			<columns multiline>
				<column :tablet="4" v-for="_image in images" :key="_image.id">
					<div class="mdl-box mdl-shadow--2dp">
						<image-container square>
							<img :src="_image.thumbnail.src" alt="">
						</image-container>
					</div>
				</column>
			</columns>

			<media-modal
				title="Upload image"
				:active="openLogoModal"
				:images="attachments"
				:image="images"
				:options="dropzoneOptions"
				@upload="dropzoneSuccess"
				@selected="chooseImage"
				@close="openLogoModal = false"
			></media-modal>
		</div>

		<big-button fullwidth @click="handleSubmit">Submit</big-button>

		<modal :active="open_thank_you_model" type="box" @close="closeThankYouModel">
			<div class="mdl-box mdl-shadow--2dp">
				<h3>Data has been submitted successfully.</h3>
				<mdl-button @click="closeThankYouModel">Close</mdl-button>
			</div>
		</modal>

	</div>
</template>

<script>
	import axios from 'axios'
	import {columns, column} from 'shapla-columns';
	import modal from 'shapla-modal';
	import AnimatedInput from '../../../components/AnimatedInput';
	import BigButton from '../../../components/BigButton';
	import imageContainer from '../../../shapla/image/image';
	import mdlRadio from '../../../material-design-lite/radio/mdlRadio';
	import mdlSpinner from '../../../material-design-lite/spinner/mdlSpinner';
	import mdlButton from '../../../material-design-lite/button/mdlButton';
	import gMapAutocomplete from '../../components/gMapAutocomplete'
	import MediaModal from '../../components/MediaModal'

	export default {
		name: "SurveyForm",
		components: {
			AnimatedInput,
			BigButton,
			mdlRadio,
			mdlSpinner,
			modal,
			gMapAutocomplete,
			mdlButton,
			MediaModal,
			imageContainer,
			columns,
			column
		},
		data() {
			return {
				openLogoModal: false,
				open_address_modal: false,
				open_thank_you_model: false,
				device_status: '',
				latitude: '',
				longitude: '',
				email: '',
				phone: '',
				addresses: [],
				address_object: {},
				formatted_address: '',
				attachments: [],
				attachment: {},
				images: [],
				brands: ['Apple', 'Samsung', 'LG'],
				gadgets: ['Phone', 'Tablet', 'Computer'],
				statuses: [
					{label: 'Product or service does not pertain to them.', value: 'not-pertain'},
					{label: 'Device can be fixed at an affordable price.', value: 'affordable'},
					{label: 'Device cannot be fixed at an affordable price.', value: 'not-affordable'},
				],
				brand: '',
				gadget: '',
				model: '',
				images_ids: '',
				tips_amount: '',
				tips_amounts: [49, 59, 69, 79, 89, 99],
			}
		},
		computed: {
			dropzoneOptions() {
				return {
					url: window.PhoneRepairs.rest_root + '/logo',
					maxFilesize: 5,
					headers: {
						"X-WP-Nonce": window.PhoneRepairs.rest_nonce
					}
				}
			},
			map_api_key() {
				return window.PhoneRepairs.map_api_key;
			},
			c_address_object() {
				let place = this.address_object;
				let placeData = {};
				let componentForm = {
					street_number: 'street_number',
					route: 'street_address', // Street address
					locality: 'city', // City
					administrative_area_level_1: 'state', // State
					country: 'country', // Country Code
					postal_code: 'postal_code' // Post code
				};

				if (place.address_components) {
					for (let i = 0; i < place.address_components.length; i++) {
						let addressComponent = place.address_components[i];
						let addressType = addressComponent.types[0];
						if (componentForm[addressType]) {
							let val = addressComponent;
							delete addressComponent.types;
							placeData[componentForm[addressType]] = val;
						}
					}
				}

				return placeData;
			},
		},
		mounted() {
			let self = this;
			this.loading = false;
			this.$store.commit('SET_TITLE', 'Survey');
			this.getImages();
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function (position) {
					self.latitude = position.coords.latitude;
					self.longitude = position.coords.longitude;

					let geocoder = new google.maps.Geocoder;
					geocoder.geocode({
						'location': {
							lat: self.latitude,
							lng: self.longitude
						}
					}, function (results, status) {
						if (status === 'OK') {
							if (results[0]) {
								self.addresses = results;
								self.address_object = results[0];
								self.formatted_address = results[0].formatted_address;
							}
						} else {
							console.log('Geocoder failed due to: ' + status);
						}
					});
				});

			}
		},
		methods: {
			closeThankYouModel() {
				this.open_thank_you_model = false;
				window.location.reload();
			},
			chooseBrand(brand) {
				this.brand = brand;
			},
			chooseGadget(gadget) {
				this.gadget = gadget;
			},
			chooseModel(model) {
				this.model = model;
			},
			chooseImagesIDs(images_ids) {
				this.images_ids = images_ids;
			},
			chooseStatus(status) {
				this.device_status = status;
			},
			chooseTipsAmount(amount) {
				this.tips_amount = amount;
			},
			dropzoneSuccess(file, response) {
				this.attachments.unshift(response.data);
				this.images.push(response.data);
				this.openLogoModal = false;
			},
			chooseImage(attachment) {
				let index = this.images.indexOf(attachment);
				if (index === -1) {
					this.images.push(attachment);
				} else {
					this.images.splice(index, 1);
				}
			},
			getImages() {
				let self = this;
				self.$store.commit('SET_LOADING_STATUS', true);
				axios
					.get(PhoneRepairs.rest_root + '/logo', {},
						{headers: {'X-WP-Nonce': window.PhoneRepairs.rest_nonce},})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.attachments = response.data.data;
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						console.log(error);
					});
			},
			changeGeoLocation(data) {
				this.address_object = data.address;
				this.formatted_address = data.formatted_address;
				this.latitude = data.latitude;
				this.longitude = data.longitude;
			},
			changeAddress(address) {
				this.address_object = address;
				this.formatted_address = address.formatted_address;
			},
			handleSubmit() {
				let self = this;
				if (self.device_status.length < 1) {
					alert('Please choose an option.');
					return;
				}
				self.$store.commit('SET_LOADING_STATUS', true);
				let images_ids = self.images.map(image => {
					return image.image_id;
				});
				axios
					.post(PhoneRepairs.rest_root + '/survey', {
						brand: self.brand,
						gadget: self.gadget,
						model: self.model,
						device_status: self.device_status,
						latitude: self.latitude,
						longitude: self.longitude,
						address: self.c_address_object,
						full_address: self.formatted_address,
						images_ids: images_ids,
						tips_amount: self.tips_amount,
						email: self.email,
						phone: self.phone,
					})
					.then((response) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						self.open_thank_you_model = true;
					})
					.catch((error) => {
						self.$store.commit('SET_LOADING_STATUS', false);
						alert('Some thing went wrong. Please try again.');
					});
			}
		}
	}
</script>

<style lang="scss">

</style>
