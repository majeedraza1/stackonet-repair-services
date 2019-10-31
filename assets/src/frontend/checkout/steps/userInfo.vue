<template>
	<div class="enter-details-wrapper">

		<section-title>Enter your contact details</section-title>

		<div class="enter-details-content-wrapper">

			<animated-input v-model="firstName" label="First name" helptext="This field is required."
							:has-success="firstName.length > 2"></animated-input>

			<animated-input v-model="lastName" label="Last name" helptext="This field is required."
							:has-success="lastName.length > 2"></animated-input>

			<animated-input v-model="phone" label="Phone" helptext="This field is required."></animated-input>

			<big-button fullwidth @click="confirmAppointment" :disabled="!enabledContinueButton">Continue</big-button>
		</div>

		<section-help></section-help>
	</div>
</template>

<script>
    import {mapState} from 'vuex';
    import AnimatedInput from '../../../components/AnimatedInput.vue';
    import BigButton from '../../../components/BigButton.vue';
    import SectionTitle from '../../components/SectionTitle'
    import SectionInfo from '../../components/SectionInfo'
    import SectionHelp from '../../components/SectionHelp'

    export default {
        name: "userInfo",
        components: {AnimatedInput, BigButton, SectionTitle, SectionInfo, SectionHelp},
        data() {
            return {
                firstName: '',
                lastName: '',
                phone: '',
            }
        },
        computed: {
            ...mapState(['checkoutAnalysisId', 'address']),
            hasPhone() {
                return !!this.phone.length;
            },
            hasLastName() {
                return !!this.lastName.length;
            },
            enabledContinueButton() {
                return (this.hasPhone && this.hasLastName);
            }
        },
        mounted() {
            this.$store.commit('SET_LOADING_STATUS', false);
            this.$store.commit('SET_SHOW_CART', true);
            this.$store.commit('IS_THANK_YOU_PAGE', false);

            this.$store.dispatch('refreshCheckoutAnalysisIdFromLocalStorage');
            if (!this.checkoutAnalysisId) {
                this.$store.dispatch('checkoutAnalysis', {
                    id: 0, step: 'user_info', step_data: {}
                });
            }
        },
        methods: {
            confirmAppointment() {
                this.$store.commit('SET_LOADING_STATUS', true);
                this.$store.commit('SET_FIRST_NAME', this.firstName);
                this.$store.commit('SET_LAST_NAME', this.lastName);
                this.$store.commit('SET_PHONE', this.phone);

                this.$router.push('/device');
            }
        }
    }
</script>

<style scoped>

</style>
