<template>
	<div>
		<h2 class="title">Home Page</h2>

		<div>
			<h3>Switch</h3>
			<mdl-switch v-model="switch1">On/Off toggle</mdl-switch>
		</div>

		<div>
			<h3>Radio</h3>
			<mdl-radio v-model="radio1" value="on">On</mdl-radio>
			<mdl-radio v-model="radio1" value="off">Off</mdl-radio>
			<mdl-radio v-model="radio1" value="both">Both</mdl-radio>
		</div>

		<div>
			<h3>Checkbox (Multiple)</h3>
			<mdl-checkbox v-model="checked" value="on">Checkbox On</mdl-checkbox>
			<mdl-checkbox v-model="checked" value="off">Checkbox Off</mdl-checkbox>
			<mdl-checkbox v-model="checked" value="both">Checkbox Both</mdl-checkbox>
		</div>

		<div>
			<h3>Checkbox (Single)</h3>
			<mdl-checkbox v-model="checked2" value="true">Checkbox On</mdl-checkbox>
		</div>

	</div>
</template>

<script>
	import mdlSwitch from '../../material-design-lite/switch/mdlSwitch.vue';
	import mdlRadio from '../../material-design-lite/radio/mdlRadio.vue';
	import mdlCheckbox from '../../material-design-lite/checkbox/mdlCheckbox.vue';

	export default {
		name: "Home",
		components: {mdlSwitch, mdlRadio, mdlCheckbox},
		data() {
			return {
				switch1: false,
				radio1: 'both',
				checked: ['on'],
				checked2: true,
			}
		},
		mounted() {
			this.$root.$emit('show-snackbar', {
				message: 'A message with action',
				actionText: 'undo',
				actionHandler() {
				},
			});
		},
		methods: {
			deleteIcon() {
				console.log('process delete icon.');
			}
		}
	}
</script>

<style lang="scss">
	@import "../../material-design-lite/ripple/ripple";

	.title {
		font-size: 20px;
	}
</style>
