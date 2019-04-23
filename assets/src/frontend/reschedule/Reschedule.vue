<template>
    <div class="stackonet-reschedule-section">

        <div class="repair-services-loader" :class="{'is-active':loading}">
            <mdl-spinner :active="loading"></mdl-spinner>
        </div>

        <div class="select-time-wrapper">
            <div class="step-nav-page-wrapper">
                <div class="step-nav-wrapper">
                    <span class="step-nav-title">What time do you prefer?</span>
                </div>
            </div>
            <div class="info-box-wrapper">
                <div class="info-box-inner-wrapper">
                    <span>We do our best to deliver amazing service at affordable prices to everyone. If we are unable to meet you at your desired time, we will deduct $5 and include a free tempered glass with the repair!</span>
                    <span>We will confirm timing by email and SMS</span>
                </div>
                <div class="info-box-inner-wrapper">
                    Current time {{last_reschedule_date.date}}, {{last_reschedule_date.time}}
                </div>
            </div>
            <div>
                <div class="select-time-day-selector-container-desktop">
                    <div class="select-time-day-selector-triangle"></div>
                    <div class="select-time-day-selector-box">

                        <div class="select-time-day-selector-wrapper">

                            <template v-for="(_date, index) in dateRanges">
                                <template v-if="index === 0">
                                    <div class="select-time-day-item-wrapper" @click="updateDate(_date)">
                                        <div class="select-time-day-item"
                                             :class="{'day-active': tempDate.date === _date.date }">
                                            <div class="select-time-weekday">Today</div>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="select-time-day-item-wrapper" @click="updateDate(_date)">
                                        <div class="select-time-day-item"
                                             :class="{'day-active': tempDate.date === _date.date }">
                                            <div class="">
                                                <div class="select-time-weekday" v-html="getDayFromDate(_date)"></div>
                                                <div class="select-time-day-in-number"
                                                     v-html="getDateNumber(_date)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="tempDate.note" v-html="tempDate.note" class="holiday-note"></div>

            <div class="select-time-time-picker-wrapper">
                <template v-for="(times, dayName) in timeRanges">
                    <template v-if="dayName === tempDate.day">
                        <button v-for="time in times"
                                :disabled="isHoliday"
                                class="time-content-box hoverable"
                                :class="{'is-active': tempTime === time}"
                                @click="setTimeRange(time)">
                            <div v-text="time"></div>
                        </button>
                    </template>
                </template>
            </div>
            <div class="select-time-continue-button-wrapper">
                <big-button @click="handleContinue" :disabled="!isButtonActive">Reschedule</big-button>
            </div>
            <p class="select-time-additional-text">
                Timing is subject to technician availability. We will<br>
                confirm timing by email and SMS.
            </p>
        </div>
    </div>
</template>

<script>
    import BigButton from '../../components/BigButton.vue';
    import mdlSpinner from '../../material-design-lite/spinner/mdlSpinner.vue';

    export default {
        name: "Reschedule",
        components: {BigButton, mdlSpinner},
        data() {
            return {
                loading: false,
                order_id: 0,
                token: '',
                date: '',
                timeRange: '',
                dateRanges: [],
                timeRanges: [],
                rescheduleData: [],
                tempDate: {
                    date: '',
                    day: '',
                    holiday: false,
                },
                tempTime: '',
            }
        },
        mounted() {
            this.dateRanges = window.Stackonet.dateRanges;
            this.timeRanges = window.Stackonet.timeRanges;
            this.rescheduleData = window.RescheduleData.reschedule;
            this.order_id = window.RescheduleData.order_id;
            this.token = window.RescheduleData.token;
            this.tempDate = this.dateRanges[0];
            this.tempTime = this.timeRanges[this.tempDate.day][0];
        },
        computed: {
            last_reschedule_date() {
                let rescheduleData = this.rescheduleData;
                return rescheduleData ? rescheduleData.pop() : {};
            },
            isHoliday() {
                return !!(this.tempDate.holiday);
            },
            isButtonActive() {
                return !!(this.tempDate && this.tempDate.date && this.tempTime.length && !this.isHoliday);
            }
        },
        methods: {
            setTimeRange(time) {
                this.tempTime = time;
            },
            updateDate(date) {
                this.tempDate = date;
            },
            getDayFromDate(time) {
                let days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                let date = new Date(time.date);
                return days[date.getDay()];
            },
            getDateNumber(time) {
                let date2 = new Date(time.date);
                let dateNumber = date2.getDate();

                return dateNumber.length === 1 ? '0' + dateNumber : dateNumber;
            },
            handleContinue() {
                if (confirm('Are you sure to change date and time?')) {
                    this.date = this.tempDate.date;
                    this.timeRange = this.tempTime;
                    this.reschedule();
                }
            },
            reschedule() {
                let $ = window.jQuery, self = this;

                self.loading = true;
                $.ajax({
                    url: window.Stackonet.rest_root + '/reschedule',
                    method: 'POST',
                    data: {
                        order_id: self.order_id,
                        token: self.token,
                        date: self.date,
                        time_range: self.timeRange,
                    },
                    success: function (response) {
                        self.loading = false;
                        alert("Order has rescheduled. You will get SMS and mail soon.");
                        setTimeout(() => {
                            window.location.href = window.Stackonet.home_url;
                        })
                    },
                    error: function () {
                        self.loading = true;
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    .stackonet-reschedule-section {
        position: relative;
        background-color: #eff2f5 !important;

        .repair-services-loader {
            position: absolute;
            height: 100%;
            width: 100%;
        }
    }
</style>
