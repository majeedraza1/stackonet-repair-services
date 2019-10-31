import Vue from 'vue';
import VueRouter from 'vue-router';
import device from './steps/device';
import deviceModel from './steps/deviceModel';
import deviceColor from './steps/deviceColor';
import zipCode from './steps/zipCode';
import screenCracked from './steps/screenCracked';
import deviceIssue from './steps/deviceIssue';
import timing from './steps/timing';
import thankyou from './steps/thankyou';
import userAddress from './steps/userAddress';
import userDetails from './steps/userDetails';
import unsupportedZipCode from './steps/unsupportedZipCode';
import unsupportedZipCodeThankyou from './steps/unsupportedZipCodeThankyou';
import termsAndConditions from './steps/termsAndConditions';
import promotion from "./steps/promotion";

Vue.use(VueRouter);

const routes = [
	// {path: '/', name: 'userInfo', component: userInfo},
	{path: '/', name: 'device', component: device},
	{path: '/device-model', name: 'device-model', component: deviceModel},
	{path: '/device-color', name: 'device-color', component: deviceColor},
	{path: '/zip-code', name: 'zip-code', component: zipCode},
	{path: '/unsupported-zip-code', name: 'unsupported-zip-code', component: unsupportedZipCode},
	{path: '/thankyou', name: 'thankyou', component: unsupportedZipCodeThankyou},
	{path: '/screen-cracked', name: 'screen-cracked', component: screenCracked},
	{path: '/select-issue', name: 'select-issue', component: deviceIssue},
	{path: '/phone', name: 'promotion', component: promotion},
	{path: '/select-time', name: 'select-time', component: timing},
	{path: '/user-address', name: 'user-address', component: userAddress},
	{path: '/user-details', name: 'user-details', component: userDetails},
	{path: '/terms-and-conditions', name: 'terms-and-conditions', component: termsAndConditions},
	{path: '/thank-you', name: 'thank-you', component: thankyou},
];

export default new VueRouter({
	routes: routes
});
