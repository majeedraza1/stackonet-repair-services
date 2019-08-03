import axios from 'axios';

const TrackerMixin = {
	data() {
		return {
			markers: [],
		}
	},
	methods: {
		getRecordFromFirebase(db) {
			return new Promise(resolve => {
				db.ref('Employees').on('value', snapshot => {
					let employees = Object.values(snapshot.val());
					resolve(employees);
				});
			})
		},
		getTrackableObjects() {
			return new Promise((resolve, reject) => {
				axios.get(PhoneRepairs.rest_root + '/trackable-objects')
					.then(response => resolve(response.data.data))
					.catch(error => reject(error))
			});
		},
		logToDatabase(objects) {
			return new Promise((resolve, reject) => {
				axios
					.post(PhoneRepairs.rest_root + '/trackable-objects/log', {objects: objects})
					.then(response => resolve(response))
					.catch(error => reject(error));
			});
		},
		getObject(object_id, date = null) {
			return new Promise((resolve, reject) => {
				axios.get(PhoneRepairs.rest_root + '/trackable-objects/log', {
					params: {
						object_id: object_id,
						log_date: date,
					}
				})
					.then(response => resolve(response.data.data))
					.catch(error => reject(error))
			})
		},
	}
};
export {TrackerMixin};
