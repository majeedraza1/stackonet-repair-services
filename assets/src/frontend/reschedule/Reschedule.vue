<template>
    <div class="stackonet-reschedule-section">
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
                                :class="{'time-content-box-active': tempTime === time}"
                                @click="setTimeRange(time)">
                            <div v-text="time"></div>
                        </button>
                    </template>
                </template>
            </div>
            <div class="select-time-continue-button-wrapper">
                <big-button @click="handleContinue" :disabled="!isButtonActive">Continue</big-button>
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
    import {mapState} from 'vuex';

    export default {
        name: "Reschedule",
        components: {BigButton},
        data() {
            return {
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
            this.tempDate = this.dateRanges[0];
            this.tempTime = this.timeRanges[this.tempDate.day][0];

            if (window.RescheduleData.order_id) {
                this.$store.commit('SET_ORDER_ID', window.RescheduleData.order_id);
            }
            if (window.RescheduleData.token) {
                this.$store.commit('SET_TOKEN', window.RescheduleData.token);
            }
        },
        computed: {
            ...mapState(['date', 'timeRange']),
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
                    this.$store.commit('SET_DATE', this.tempDate.date);
                    this.$store.commit('SET_TIME_RANGE', this.tempTime);
                    this.$store.dispatch('rescheduleOrder');
                }
            }
        }
    }
</script>

<style lang="scss">
    .stackonet-reschedule-section {
        background-color: #eff2f5 !important;
    }
</style>
