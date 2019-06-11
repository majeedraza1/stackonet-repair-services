import Vue from 'vue';
import VueRouter from 'vue-router';
import SurveyForm from '../survey/SurveyForm'
import SupportTicketList from "../support-ticket/SupportTicketList";
import SingleSupportTicket from "../support-ticket/SingleSupportTicket";
import NewSupportTicket from "../support-ticket/NewSupportTicket";
import SpotAppointment from "../spot-appointment/SpotAppointment";
import CheckoutAnalysis from "../checkout-analysis/CheckoutAnalysis";

Vue.use(VueRouter);

const routes = [
	{path: '/ticket', name: 'SupportTicketList', component: SupportTicketList},
	{path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
	{path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
	{path: '/survey', name: 'survey', component: SurveyForm},
	{path: '/spot-appointment', name: 'appointment', component: SpotAppointment},
	{path: '/checkout-analysis', name: 'checkout', component: CheckoutAnalysis},

];

export default new VueRouter({
	routes: routes
});
