import Vue from 'vue';
import SurveyForm from './SurveyForm';

let element = document.querySelector('#stackonet_survey_form');
if (element) {
	new Vue({
		el: element,
		render: h => h(SurveyForm)
	});
}
