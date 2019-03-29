import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex);

export default new Vuex.Store({
    // Same as Vue data
    state: {
        loading: true,
        order_id: 0,
        token: '',
        date: '',
        timeRange: '',
    },

    // Commit + track state changes
    mutations: {
        SET_LOADING_STATUS(state, loading) {
            state.loading = loading;
        },
        SET_ORDER_ID(state, order_id) {
            state.order_id = order_id;
        },
        SET_TOKEN(state, token) {
            state.token = token;
        },
        SET_DATE(state, date) {
            state.date = date;
        },
        SET_TIME_RANGE(state, timeRange) {
            state.timeRange = timeRange;
        }
    },

    // Same as Vue methods
    actions: {
        rescheduleOrder({state}) {
            let $ = window.jQuery, self = this;
            $.ajax({
                url: window.Stackonet.rest_root + '/reschedule',
                method: 'POST',
                data: {
                    order_id: state.order_id,
                    token: state.token,
                    date: state.date,
                    time_range: state.timeRange,
                },
                success: function (response) {

                }
            });
        }
    },

    // Save as Vue computed property
    getters: {},
});
