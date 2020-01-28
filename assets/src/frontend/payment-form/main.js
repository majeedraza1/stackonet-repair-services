import Vue from 'vue';
import PaymentForm from './PaymentForm'

if (document.querySelector('#stackonet_payment_form')) {
	new Vue({el: '#stackonet_payment_form', render: h => h(PaymentForm)});
}
