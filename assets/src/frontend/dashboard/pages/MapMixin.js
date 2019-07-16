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
		textSearch(PlacesService, request) {
			return new Promise((resolve, reject) => {
				PlacesService.textSearch(request, (results, status, pagination) => {
					if (status === 'OK') {
						resolve({results, pagination});
					} else {
						reject(status);
					}
				});
			});
		},
		createMarkers(googleMap, baseLocationLatLng, results, places, markers) {
			let bounds = new google.maps.LatLngBounds();

			for (let i = 0; i < results.length; i++) {
				let place = results[i];
				if (!place.geometry) continue;
				let image = {
					url: place.icon,
					size: new google.maps.Size(71, 71),
					origin: new google.maps.Point(0, 0),
					anchor: new google.maps.Point(17, 34),
					scaledSize: new google.maps.Size(25, 25)
				};

				let marker = new google.maps.Marker({
					map: googleMap,
					icon: image,
					title: place.name,
					position: place.geometry.location
				});

				place.distance = this.distance(baseLocationLatLng, place.geometry.location);

				markers.push(marker);

				places.push({
					place_id: place.place_id,
					name: place.name,
					formatted_address: place.formatted_address,
					location: place.geometry.location,
					distance: place.distance,
				});

				bounds.extend(place.geometry.location);
			}
			// console.log(places);
			googleMap.fitBounds(bounds);
		},
		clearMarkers(markers) {
			for (let i = 0; i < markers.length; i++) {
				markers[i].setMap(null);
			}
			markers = [];
		},
		/**
		 * Update map routes
		 */
		getDirectionRoutes(directionsService, placeObject) {
			let origin = {lat: placeObject.base_address_latitude, lng: placeObject.base_address_longitude};
			let addresses = placeObject.places;

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
			return new Promise((resolve, reject) => {
				directionsService.route(request, (response, status) => {
					if (status === 'OK') {
						resolve(response);
					} else {
						reject(response);
					}
				});
			});
		},
		addLegOnSelectedPlaces(placeObject, routesLegs) {
			let legs = [], _selectedPlaces = [];

			for (let i = 0; i < routesLegs.length; i++) {
				legs.push({distance: routesLegs[i].distance, duration: routesLegs[i].duration});
			}
			if (legs.length) {
				let baseTime = placeObject.base_datetime;
				if (typeof baseTime === "string") {
					baseTime = new Date(baseTime);
				}
				for (let i = 0; i < legs.length; i++) {
					let _data = placeObject.places[i];
					_data['leg'] = legs[i];
					_data['interval_hour'] = _data.interval_hour ? _data.interval_hour : 0;
					_data['interval_minute'] = _data.interval_minute ? _data.interval_minute : 0;
					_data['reach_time'] = _data.reach_time ? _data.reach_time : 0;
					_data['leave_time'] = _data.leave_time ? _data.leave_time : 0;
					if (i === 0) {
						_data['reach_time'] = baseTime.getTime() + (_data.leg.duration.value * 1000);
					}
					_selectedPlaces.push(_data);
				}

				placeObject.places = _selectedPlaces;
			}
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
		//Returns Distance between two latlng objects using haversine formula
		distance(placeOne, placeTwo) {
			if (!placeOne || !placeTwo) return 0;

			let R = 6371000; // Radius of the Earth in m
			let dLat = (placeTwo.lat() - placeOne.lat()) * Math.PI / 180;
			let dLon = (placeTwo.lng() - placeOne.lng()) * Math.PI / 180;
			let a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
				Math.cos(placeOne.lat() * Math.PI / 180) * Math.cos(placeTwo.lat() * Math.PI / 180) *
				Math.sin(dLon / 2) * Math.sin(dLon / 2);
			let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
			return R * c;
		},
		metres_to_km(metres) {
			if (metres < 100) return Math.round(metres) + " metres away";
			if (metres < 1000) return (metres / 1000).toFixed(2) + " km away";
			return (metres / 1000).toFixed(1) + " km away";
		},
	}
};

export {MapMixin};
