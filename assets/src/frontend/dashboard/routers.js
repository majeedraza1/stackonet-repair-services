import Vue from 'vue';
import VueRouter from 'vue-router';
import SupportTicketList from "../support-ticket/SupportTicketList";
import SingleSupportTicket from "../support-ticket/SingleSupportTicket";
import NewSupportTicket from "../support-ticket/NewSupportTicket";
import Dashboard from "./Dashboard";
import SurveyForm from './pages/SurveyForm'
import CarrierStores from "./pages/CarrierStores";
import SpotAppointment from "./pages/SpotAppointment";
import CheckoutAnalysis from "./pages/CheckoutAnalysis";
import ShortMessageService from "./pages/ShortMessageService";

Vue.use(VueRouter);

const routes = [
	{path: '/', name: 'Dashboard', component: Dashboard},
	{path: '/ticket', name: 'SupportTicketList', component: SupportTicketList},
	{path: '/ticket/:id/view', name: 'SingleSupportTicket', component: SingleSupportTicket},
	{path: '/ticket/new', name: 'NewSupportTicket', component: NewSupportTicket},
	{path: '/survey', name: 'survey', component: SurveyForm},
	{path: '/carrier-stores', name: 'CarrierStores', component: CarrierStores},
	{path: '/spot-appointment', name: 'appointment', component: SpotAppointment},
	{path: '/sms', name: 'ShortMessageService', component: ShortMessageService},
	{path: '/checkout-analysis', name: 'checkout', component: CheckoutAnalysis},
];

export default new VueRouter({
	routes: routes
});
