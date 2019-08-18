import axios from 'axios';

const TrackerMixin = {
	data() {
		return {
			markers: [],
		}
	},
	methods: {
		getTrackableObjects() {
			return new Promise((resolve, reject) => {
				axios.get(PhoneRepairs.rest_root + '/trackable-objects')
					.then(response => resolve(response.data.data))
					.catch(error => reject(error))
			});
		},
		getObject(object_id, date = null) {
			return new Promise((resolve, reject) => {
				axios
					.get(PhoneRepairs.rest_root + '/trackable-objects/log', {
						params: {object_id: object_id, log_date: date,}
					}).then(response => resolve(response.data.data))
					.catch(error => reject(error))
			})
		},
	}
};
export {TrackerMixin};
