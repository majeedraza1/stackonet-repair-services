<template>
	<div class="stackonet-survey-form stackonet-spot-appointment">

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
			<label>Choose device</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="(_device, index) in devices" :key="index">
					<div class="shapla-survey-box hoverable" :class="{'is-active':_device === device}"
						 @click="chooseDevice(_device)">
						<div v-html="_device.device_title"></div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">
			<label>Choose device model</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="(_model, index) in devices_models" :key="index">
					<div class="shapla-survey-box hoverable" :class="{'is-active':_model === device_model}"
						 @click="chooseDeviceModel(_model)">
						<div v-html="_model.title"></div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">
			<label>Choose issue(s)</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="(_issue, index) in _issues" :key="index">
					<div class="shapla-survey-box hoverable"
						 :class="{'is-active':-1 !== selectedIssues.indexOf(_issue)}"
						 @click="chooseIssue(_issue)">
						<div v-html="_issue.title"></div>
					</div>
				</column>
			</columns>
		</div>

		<div class="form-field">
			<label>Appointment Date</label>
			<columns mobile multiline centered>
				<template v-for="(_date, index) in dateRanges">
					<column :mobile="6" :tablet="4" :key="index" v-if="index !== 0">
						<div class="shapla-survey-box hoverable"
							 :class="{'is-active': _date.date === appointment_date}"
							 @click="chooseDate(_date.date)">
							<div v-html="getFormattedDateTime(_date.date)"></div>
						</div>
					</column>
				</template>
			</columns>
		</div>

		<div class="form-field">
			<label>Appointment Time</label>
			<columns mobile multiline centered>
				<column :mobile="6" :tablet="4" v-for="(_time, index) in times" :key="index">
					<div class="shapla-survey-box hoverable"
						 :class="{'is-active': _time === appointment_time}"
						 @click="chooseTime(_time)">
						<div v-html="_time"></div>
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
			<animated-input v-model="store_name" label="Name of Store" autocomplete="organization"></animated-input>
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

		<div class="form-field">
			<animated-input type="textarea" v-model="note" label="Note (optional)"></animated-input>
		</div>

		<big-button @click="handleSubmit">Submit</big-button>

		<div class="loading-container" :class="{'is-active':loading}">
			<mdl-spinner :active="loading"></mdl-spinner>
		</div>

		<mdl-modal :active="open_thank_you_model" type="box" @close="closeThankYouModel">
			<div class="mdl-box mdl-shadow--2dp">
				<h3>Data has been submitted successfully.</h3>
				<mdl-button @click="closeThankYouModel">Close</mdl-button>
			</div>
		</mdl-modal>

	</div>
</template>

<script>
	import axios from 'axios'
	import AnimatedInput from '../../components/AnimatedInput';
	import BigButton from '../../components/BigButton';
	import PricingAccordion from '../../components/PricingAccordion';
	import modal from '../../shapla/modal/modal';
	import imageContainer from '../../shapla/image/image';
	import columns from '../../shapla/columns/columns';
	import column from '../../shapla/columns/column';
	import mdlRadio from '../../material-design-lite/radio/mdlRadio';
	import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner';
	import mdlModal from '../../material-design-lite/modal/mdlModal';
	import mdlButton from '../../material-design-lite/button/mdlButton';
	import gMapAutocomplete from '../components/gMapAutocomplete'
	import MediaModal from '../components/MediaModal'

	export default {
		name: "SpotAppointment",
		components: {
			AnimatedInput,
			BigButton,
			mdlRadio,
			mdlSpinner,
			modal,
			gMapAutocomplete,
			mdlModal,
			mdlButton,
			MediaModal,
			imageContainer,
			columns,
			column,
			PricingAccordion
		},
		data() {
			return {
				loading: true,
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
				appointment_date: '',
				appointment_time: '',
				note: '',
				store_name: '',
				device: {},
				devices_models: [],
				device_model: {},
				issues: [],
				selectedIssues: [],
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
			devices() {
				return window.Stackonet.devices;
			},
			dateRanges() {
				return window.Stackonet.dateRanges;
			},
			timeRanges() {
				return window.Stackonet.timeRanges;
			},
			times() {
				let _date = this.dateRanges.find((element) => element.date === this.appointment_date);
				if (typeof _date === "undefined") {
					return [];
				}
				return this.timeRanges[_date.day];
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
			_issues() {
				let brokenPrice = this.device_model.broken_screen_price;
				let issues = this.issues.map(issue => {
					if (issue.title === 'Broken Screen') {
						issue.price = brokenPrice;
					}

					return issue;
				});

				if (brokenPrice) {
					issues.unshift({
						id: '',
						title: 'Front Glass',
						price: brokenPrice,
						description: 'Glass price is subject to undamaged display.',
					});
				}

				return issues;
			},
			selectedIssueNames() {
				let names = this.selectedIssues.map(issue => issue.title);
				return names.join(', ');
			}
		},
		mounted() {
			let self = this;
			this.loading = false;
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
			chooseDevice(device) {
				this.device = device;
				this.devices_models = device.device_models;
				this.device_model = device.device_models[0];
				this.issues = device.multi_issues;
				this.activeDeviceAccordion = false;
				this.selectedIssues = [];
			},
			chooseDeviceModel(model) {
				this.device_model = model;
				this.model = model.title;
				this.activeModelAccordion = false;
				this.selectedIssues = [];
			},
			chooseDate(date) {
				this.appointment_date = date;
			},
			chooseTime(time) {
				this.appointment_time = time;
			},
			getFormattedDateTime(date) {
				let _date = new Date(date);

				let days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
				let dayName = days[_date.getDay()];

				let number = _date.getDate();
				let dateNumber = number.length === 1 ? '0' + number : number;

				let monthNames = ["January", "February", "March", "April", "May", "June",
					"July", "August", "September", "October", "November", "December"
				];

				return dayName + ', ' + dateNumber + ' ' + monthNames[_date.getMonth()];
			},
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
				self.loading = true;
				axios
					.get(PhoneRepairs.rest_root + '/logo', {},
						{headers: {'X-WP-Nonce': window.PhoneRepairs.rest_nonce},})
					.then((response) => {
						self.loading = false;
						self.attachments = response.data.data;
					})
					.catch((error) => {
						self.loading = false;
						alert('Some thing went wrong. Please try again.');
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
			chooseIssue(issue) {
				let issues = this.selectedIssues, index = issues.indexOf(issue);
				if (-1 === index) {
					issues.push(issue);
				} else {
					issues.splice(index, 1);
				}

				this.selectedIssues = issues;
			},
			issueClass(issue) {
				let isSelected = -1 !== this.selectedIssues.indexOf(issue);
				return {
					'selected-issue': isSelected,
					'disabled-issue': !isSelected,
				}
			},
			handleSubmit() {
				let self = this;
				this.loading = true;
				let images_ids = self.images.map(image => {
					return image.image_id;
				});
				let data = {
					gadget: self.gadget,
					brand: self.brand,
					device: self.device.device_title,
					device_model: self.model,
					device_issues: self.selectedIssues,
					appointment_date: self.appointment_date,
					appointment_time: self.appointment_time,
					email: self.email,
					phone: self.phone,
					store_name: self.store_name,
					full_address: self.formatted_address,
					address: self.c_address_object,
					images_ids: images_ids,
					note: self.note,
				};

				axios
					.post(PhoneRepairs.rest_root + '/spot-appointment', data,
						{
							headers: {'X-WP-Nonce': window.PhoneRepairs.rest_nonce},
						})
					.then((response) => {
						self.loading = false;
						self.open_thank_you_model = true;
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
	.stackonet-spot-appointment {
		select {
			width: 100%;
		}

		.pricing-accordion {
			min-width: 100%;
		}
	}
</style>
