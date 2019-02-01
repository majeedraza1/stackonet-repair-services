<template>
	<div>
		<div class="title">Settings page</div>

		<div>
			<h3>Tabs</h3>
			<mdl-tabs :vertical="true">
				<mdl-tab name="Tab one" selected>Tab content one</mdl-tab>
				<mdl-tab name="Tab two">Tab content two</mdl-tab>
				<mdl-tab name="Tab three">Tab content three</mdl-tab>
				<mdl-tab name="Tab four">Tab content four</mdl-tab>
			</mdl-tabs>
		</div>

		<div>
			<h3>Button</h3>
			<mdl-fab>+</mdl-fab>
			<mdl-button type="raised" color="primary">Button</mdl-button>

		</div>

		<div>
			<h3>Slider</h3>
			<mdl-slider :default="10" :min="5" :max="500" :step="5" v-model="slider1"></mdl-slider>
		</div>

		<div>
			<h3>Radio Button</h3>
			<mdl-radio-button v-model="radio" value="on">Radio One</mdl-radio-button>
			<mdl-radio-button v-model="radio" value="off">Radio Two</mdl-radio-button>
		</div>

	</div>
</template>

<script>
	import mdlTabs from '../../material-design-lite/tabs/mdlTabs.vue';
	import mdlTab from '../../material-design-lite/tabs/mdlTab.vue';
	import mdlFab from '../../material-design-lite/button/mdlFab.vue';
	import mdlButton from '../../material-design-lite/button/mdlButton.vue';
	import mdlSlider from '../../material-design-lite/slider/mdlSlider.vue';
	import mdlRadioButton from '../../material-design-lite/radio-button/mdlRadioButton.vue';

	export default {
		name: "Settings",
		components: {mdlTabs, mdlTab, mdlFab, mdlButton, mdlSlider, mdlRadioButton},
		data() {
			return {
				checked: ['on'],
				checked2: true,
				radio: 'on',
				switch1: 'off',
				slider1: 50,
				checkbox_label: 'This is a checkbox',
				snackbar: {
					message: 'This is snackbar initial message.',
					timeout: 2000,
				}
			}
		},
		mounted() {

		}
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/ripple/ripple";
</style>
