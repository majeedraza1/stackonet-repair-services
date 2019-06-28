<template>
	<div class="stackonet-payment-form">
		<columns>
			<column>
				<h2>Customer Detail</h2>
				<strong>customer.name</strong><br>
				<strong>{{customer.email}}</strong><br>
				<strong>{{customer.phone}}</strong><br>
				<div v-html="customer.address"></div>
			</column>
			<column>
				<div class="item-details">
					<table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable">
						<tr>
							<td class="mdl-data-table__cell--non-numeric" colspan="2"
								style="text-align: center;font-size: 20px;">
								<strong>{{order.device}}</strong>
							</td>
						</tr>
						<tr v-for="_fee in order.fees">
							<td class="mdl-data-table__cell--non-numeric"> {{_fee.name}}</td>
							<td v-html="_fee.total"></td>
						</tr>
						<tr>
							<td class="mdl-data-table__cell--non-numeric"><strong>Subtotal</strong></td>
							<td><strong v-html="order.fees_total"></strong></td>
						</tr>
						<tr v-for="_tax in order.taxes">
							<td class="mdl-data-table__cell--non-numeric"> {{_tax.name}}</td>
							<td v-html="_tax.total"></td>
						</tr>
						<tr>
							<td class="mdl-data-table__cell--non-numeric"><strong>Total</strong></td>
							<td><strong v-html="order.order_total"></strong></td>
						</tr>
					</table>
				</div>
				<div id="sq-ccbox">
					<form id="nonce-form" novalidate action="#" method="post" @submit.prevent="requestCardNonce">
						<div id="card-container" class="card-container">
							<columns class="card-container__row1">
								<column>
									<div class="cardfields card-number" :id="id+'-sq-card-number'"></div>
									<div class="cardfields__error" v-if="errors.cardNumber && errors.cardNumber.length">
										{{errors.cardNumber[0]}}
									</div>
								</column>
							</columns>
							<columns class="card-container__row">
								<column>
									<div class="cardfields expiration-date" :id="id+'-sq-expiration-date'"></div>
									<div class="cardfields__error"
										 v-if="errors.expirationDate && errors.expirationDate.length">
										{{errors.expirationDate[0]}}
									</div>
								</column>
								<column>
									<div class="cardfields cvv" :id="id+'-sq-cvv'"></div>
									<div class="cardfields__error" v-if="errors.cvv && errors.cvv.length">
										{{errors.cvv[0]}}
									</div>
								</column>
								<column>
									<div class="cardfields postal-code" :id="id+'-sq-postal-code'"></div>
									<div class="cardfields__error" v-if="errors.postalCode && errors.postalCode.length">
										{{errors.postalCode[0]}}
									</div>
								</column>
							</columns>

							<columns>
								<column>
									<mdl-button type="raised" color="primary" :disabled="!formLoaded"
												style="width: 100%;"
												v-html="pay_button_text">
									</mdl-button>
								</column>
							</columns>
						</div>

						<input type="hidden" id="card-nonce" name="nonce">

						<div id="sq-walletbox" style="display: none;">
							<button v-show=applePay :id="id+'-sq-apple-pay'" class="button-apple-pay"></button>
							<button v-show=masterpass :id="id+'-sq-masterpass'" class="button-masterpass"></button>
						</div>
					</form>
					<div class="stackonet-payment-form__loader" :class="{'is-active':!formLoaded || loading}">
						<spinner :active="true" single></spinner>
					</div>
				</div>
			</column>
		</columns>
	</div>
</template>

<script>
	import {columns, column} from 'shapla-columns';
	import axios from 'axios';
	import MdlButton from "../../material-design-lite/button/mdlButton";
	import Spinner from "../../shapla/spinner/spinner";

	export default {
		name: "PaymentForm",
		components: {Spinner, MdlButton, columns, column},
		data: function () {
			return {
				loading: false,
				formLoaded: false,
				errors: {
					cardNumber: [],
					cvv: [],
					expirationDate: [],
					postalCode: [],
				},
				masterpass: false,
				applePay: false
			};
		},
		watch: {
			showPaymentForm: function () {
				if (!this.showPaymentForm) {
					return 1;
				}
				this.paymentForm.build();
			}
		},
		props: {
			showPaymentForm: {type: Boolean, default: true},
			id: {type: String, default: 'stackonet-payment-form'}
		},
		computed: {
			location_id() {
				return StackonetPayment.location_id;
			},
			application_id() {
				return StackonetPayment.application_id;
			},
			order_id() {
				return StackonetPayment.order_id;
			},
			order() {
				return StackonetPayment.order;
			},
			customer() {
				return StackonetPayment.customer;
			},
			pay_button_text() {
				return StackonetPayment.pay_button_text;
			},
		},
		mounted: function () {
			let that = this;
			this.paymentForm = new SqPaymentForm({
				autoBuild: true,
				applicationId: that.application_id,
				locationId: that.location_id,
				inputClass: "sq-input",
				// Initialize the payment form elements

				// Customize the CSS for SqPaymentForm iframe elements
				inputStyles: [
					{fontSize: ".9em", padding: "1em",}
				],

				// Initialize Apple Pay placeholder ID
				applePay: {
					elementId: that.id + "-sq-apple-pay"
				},

				// Initialize Masterpass placeholder ID
				masterpass: {
					elementId: that.id + "-sq-masterpass"
				},

				// Initialize the credit card placeholders
				cardNumber: {
					elementId: that.id + "-sq-card-number",
					placeholder: "Card number"
				},
				cvv: {
					elementId: that.id + "-sq-cvv",
					placeholder: "CVV"
				},
				expirationDate: {
					elementId: that.id + "-sq-expiration-date",
					placeholder: "MM / YY"
				},
				postalCode: {
					elementId: that.id + "-sq-postal-code",
					placeholder: "Postal Code"
				},

				// SqPaymentForm callback functions
				callbacks: {
					/*
                       * callback function: methodsSupported
                       * Triggered when: the page is loaded.
                       */
					methodsSupported: function (methods) {
						// Only show the button if Apple Pay for Web is enabled
						// Otherwise, display the wallet not enabled message.
						that.applePay = methods.applePay;
						that.masterpass = methods.masterpass;
					},

					/*
                       * Digital Wallet related functions
                       */
					createPaymentRequest: function () {
						var paymentRequestJson;
						/* ADD CODE TO SET/CREATE paymentRequestJson */
						return paymentRequestJson;
					},
					validateShippingContact: function (contact) {
						var validationErrorObj;
						/* ADD CODE TO SET validationErrorObj IF ERRORS ARE FOUND */
						return validationErrorObj;
					},

					/*
                       * callback function: cardNonceResponseReceived
                       * Triggered when: SqPaymentForm completes a card nonce request
                       */
					cardNonceResponseReceived: function (errors, nonce, cardData) {
						that.errors = {
							cardNumber: [],
							cvv: [],
							expirationDate: [],
							postalCode: [],
						};
						if (errors) {
							errors.forEach(function (error) {
								that.errors[error.field].push(error.message);
							});
							return;
						}
						// Assign the nonce value to the hidden form field
						document.getElementById("card-nonce").value = nonce;

						that.processPayment({
							order: that.order_id,
							nonce: nonce,
						});

						// POST the nonce form to the payment processing page
						// document.getElementById("nonce-form").submit();
					},

					/*
                    * callback function: paymentFormLoaded
                    * Triggered when: SqPaymentForm is fully loaded
                    */
					paymentFormLoaded: function () {
						that.formLoaded = true;
					}
				}
			});
		},
		methods: {
			requestCardNonce() {
				// Request a nonce from the SqPaymentForm object
				this.paymentForm.requestCardNonce();
			},
			processPayment(data) {
				this.loading = true;
				axios
					.post(window.PhoneRepairs.rest_root + `/checkout`, data)
					.then(response => {
						this.loading = false;
					})
					.catch(error => {
						console.log(error);
						this.loading = false;
					})
			}
		}
	};
</script>

<style lang="scss">
	.stackonet-payment-form {
		position: relative;

		&__loader {
			position: absolute;
			width: 100%;
			height: 100%;
			display: none;
			justify-content: center;
			align-items: center;
			top: 0;
			left: 0;
			background: rgba(255, 255, 255, 0.8);
			min-height: 170px;

			&.is-active {
				display: flex;
				z-index: 999;
			}
		}

		.cardfields {
			&__error {
				color: red;
			}
		}

		.sq-input {
			border: 1px solid rgba(#000, 0.2);

			&--error {
				border-color: red;
			}
		}

		.card-container {
			display: flex;
			flex-direction: column;

			&__row2 {
				display: flex;

				> *:not(:last-child) {
					margin-right: 1rem;
				}
			}
		}
	}
</style>
