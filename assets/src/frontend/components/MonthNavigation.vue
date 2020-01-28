<template>
	<div class="full-calendar-header" style="margin-bottom: 10px;">
		<div class="header-left">
			<div></div>
		</div>
		<div class="header-center">
			<span class="prev-month" @click="changeMonth('pre')">&lt;</span>
			<span class="title">{{title}}</span>
			<span class="next-month" @click="changeMonth('next')">&gt;</span>
		</div>
		<div class="header-right">
			<div></div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "MonthNavigation",
		data() {
			return {
				monthNames: ["January", "February", "March", "April", "May", "June",
					"July", "August", "September", "October", "November", "December"
				],
				year: '',
				monthNumber: '',
				monthName: '',
			}
		},
		methods: {
			changeMonth(data) {
				if ('pre' === data) {
					if (this.monthNumber > 0) {
						this.monthNumber -= 1;
					} else {
						this.monthNumber = 11;
						this.year -= 1;
					}
					this.monthName = this.monthNames[this.monthNumber];
				}
				if ('next' === data) {
					if (this.monthNumber < 11) {
						this.monthNumber += 1;
					} else {
						this.monthNumber = 0;
						this.year += 1;
					}
					this.monthName = this.monthNames[this.monthNumber];
				}

				this.$emit('change', {year: this.year, month: this.monthNumber + 1})
			}
		},
		computed: {
			title() {
				return `${this.monthName}  ${this.year}`;
			}
		},
		mounted() {
			let d = new Date();
			this.year = d.getFullYear();
			this.monthNumber = d.getMonth();
			this.monthName = this.monthNames[this.monthNumber];
		}
	}
</script>

<style scoped>

</style>
