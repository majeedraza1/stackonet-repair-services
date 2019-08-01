import axios from 'axios';

const TrackerMixin = {
	data() {
		return {
			markers: [],
		}
	},
	methods: {
		logToDatabase(objects) {
			return new Promise((resolve, reject) => {
				axios
					.post(PhoneRepairs.rest_root + '/trackable-objects/log', {objects: objects})
					.then(response => resolve(response))
					.catch(error => reject(error));
			});
		},
		runSnapToRoad(path) {
			return new Promise((resolve, reject) => {
				axios
					.get('https://roads.googleapis.com/v1/snapToRoads',
						{
							interpolate: true,
							params: {path: path}
						}
					).then(response => resolve(response))
					.catch(error => reject(error));
			});
		}
	}
};
export {TrackerMixin};
