const MapMixin = {
	methods: {
		formatDate(dateString) {
			let date = new Date(dateString);
			let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

			let day = date.getDate();
			let monthIndex = date.getMonth();
			let year = date.getFullYear();

			// Jul 10, 2019
			return `${monthNames[monthIndex]} ${day}, ${year}`;
		},
		formatTime(dateString) {
			let date = new Date(dateString);
			let hr = date.getHours(), min = date.getMinutes();

			if (min < 10) {
				min = "0" + min;
			}
			let ampm = "AM";
			if (hr > 12) {
				hr -= 12;
				ampm = "PM";
			}
			// 08:12 PM
			return `${hr}:${min} ${ampm}`;
		},
		/**
		 * Update map routes
		 */
		updateMapRoute(directionsService, DirectionsRenderer, origin, addresses) {
			// Clear current route
			DirectionsRenderer.setDirections({routes: []});


			// Get total selected item length
			let totalItem = addresses.length;

			// Exit if length is less than 1
			if (totalItem < 1) return;

			let lastIndex = totalItem - 1,
				lastItem = addresses[lastIndex];

			let waypoints = [];
			for (let i = 0; i < totalItem; i++) {
				if (i !== lastIndex) {
					waypoints.push({location: addresses[i].formatted_address, stopover: true});
				}
			}

			let request = {
				origin: origin,
				waypoints: waypoints,
				destination: lastItem.location,
				travelMode: 'DRIVING',
				avoidTolls: true,
				drivingOptions: {
					departureTime: new Date(),
					trafficModel: 'optimistic'
				}
			};

			// Display Route
			directionsService.route(request, function (response, status) {
				if (status === 'OK') {
					DirectionsRenderer.setDirections(response);
				}
			});
		},
		reCalculateArrivalAndDepartureTime(addresses) {
			let total = addresses.length;
			if (total < 2) {
				return addresses;
			}
			let _addresses = [];
			for (let i = 0; i < total; i++) {
				if (i === 0) {
					_addresses.push(current);
					continue;
				}
				let pre = addresses[i - 1], current = addresses[i];
				current['reach_time'] = pre.leave_time + (current.leg.duration.value * 1000);
				if (current.reach_time) {
					let interval_seconds = (current['interval_hour'] * 60 * 60 * 1000) + (current['interval_minute'] * 60 * 1000);
					current['leave_time'] = (current.reach_time + interval_seconds);
				}
				_addresses.push(current);
			}
		},
	}
};

export {MapMixin};
