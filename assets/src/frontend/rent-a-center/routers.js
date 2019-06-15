import Vue from 'vue';
import VueRouter from 'vue-router';
import Dashboard from './pages/Dashboard';
import StoreAddress from './pages/StoreAddress';
import RentcenterOrders from './pages/RentcenterOrders';
import AccountDetails from './pages/AccountDetails';
import TrackStatus from './pages/TrackStatus';
import Phones from './pages/Phones';
import ChatApp from './pages/ChatApp';
import Notifications from './pages/Notifications';
import Invoices from './pages/Invoices';
import Newdashboard from './pages/Newdashboard';
import Logout from './pages/Logout';

Vue.use(VueRouter);
const routes = [
	{path: '/', name: 'Dashboard', component: Dashboard},
	{path: '/store-addresses', name: 'StoreAddress', component: StoreAddress},
	{path: '/RentcenterOrders', name: 'RentcenterOrders', component:RentcenterOrders},
	{path: '/AccountDetails', name: 'AccountDetails', component:AccountDetails},
	{path: '/Phones', name: 'Phones', component:Phones},
	{path: '/TrackStatus', name: 'TrackStatus', component:TrackStatus},
	{path: '/chat-app', name: 'ChatApp', component: ChatApp},
	{path: '/Notifications', name: 'Notifications', component:Notifications},
	{path: '/Invoices', name: 'Invoices', component:Invoices},
	{path: '/Newdashboard', name: 'Newdashboard', component:Newdashboard},
		{path: '/Logout', name: 'Logout', component:Logout},

];

export default new VueRouter({
	routes: routes
});
