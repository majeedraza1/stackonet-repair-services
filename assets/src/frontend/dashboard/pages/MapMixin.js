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
	}
};

export {MapMixin};
