<template>
	<div class="my-cart-wrapper ">
		<div class="my-cart-content-wrapper">

			<div class="my-cart-small-items-container" style="padding-top: 20px;">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div>
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1"
							 xmlns="http://www.w3.org/2000/svg">
							<defs>
								<linearGradient x1="100%" y1="50%" x2="21.9573333%" y2="50%" id="linearGradient-1">
									<stop stop-color="#12FFCD" stop-opacity="0.329710145" offset="0%"></stop>
									<stop stop-color="#12FFCD" stop-opacity="0" offset="100%"></stop>
								</linearGradient>
							</defs>
							<g id="phone_20x20_icn" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="phone_icn">
									<rect id="grid" x="0" y="0" width="20" height="20"></rect>
									<path d="M15,18.6913339 L15,2.30866612 C15,1.58602069 14.403388,1 13.6670867,1 L5.33291326,1 C4.59714514,1 4,1.58602069 4,2.30866612 L4,18.6913339 C4,19.4139793 4.59714514,20 5.33291326,20 L13.6670867,20 C14.403388,20 15,19.4139793 15,18.6913339"
										  id="Fill-1" fill-opacity="0.6" fill="url(#linearGradient-1)"></path>
									<path d="M15.5,17.760211 C15.5,18.7185069 14.7362934,19.5 13.7882607,19.5 L6.21173933,19.5 C5.26405564,19.5 4.5,18.7183658 4.5,17.760211 L4.5,2.23978896 C4.5,1.28163421 5.26405564,0.5 6.21173933,0.5 L13.7882607,0.5 C14.7362934,0.5 15.5,1.28149307 15.5,2.23978896 L15.5,17.760211 Z"
										  id="Stroke-3" stroke="#0161C7" stroke-linecap="round"
										  stroke-linejoin="round"></path>
									<path d="M8,3.5 L11,3.5" id="Stroke-5" stroke="#595959" stroke-width="0.520000041"
										  stroke-linecap="round" stroke-linejoin="round"></path>
									<rect id="line" fill="#0161C7" x="7" y="3" width="6" height="1"></rect>
									<rect id="line" fill="#0161C7" x="7" y="16" width="6" height="1"></rect>
								</g>
							</g>
						</svg>
						<span class="time-title">Phone Repair</span>
						<div class="my-cart-small-items-edit" @click="editDevice">
							<svg width="12px" height="11px" viewBox="0 0 12 11" version="1.1"
								 xmlns="http://www.w3.org/2000/svg">
								<defs></defs>
								<g id="icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
								   transform="translate(-69.000000, -75.000000)">
									<g id="edit"
									   transform="translate(74.207664, 81.292336) rotate(-315.000000) translate(-74.207664, -81.292336) translate(71.707664, 74.292336)"
									   fill="#0161C7">
										<path d="M4.67241444,10.5129325 L2.98743926,13.0641555 C2.75916274,13.4097892 2.29391652,13.5049261 1.94828275,13.2766496 C1.86673107,13.2227881 1.79649272,13.1535068 1.74151762,13.0727016 L-1.10134124e-13,10.5129325 L-1.09690035e-13,1 C-1.0975767e-13,0.44771525 0.44771525,1.08015131e-13 1,1.07913678e-13 L3.67241444,1.07913678e-13 C4.22469919,1.07812225e-13 4.67241444,0.44771525 4.67241444,1 L4.67241444,10.5129325 Z"
											  id="Combined-Shape"></path>
									</g>
								</g>
							</svg>
							<span>Edit</span>
						</div>
					</div>
					<div>
						<div class="my-cart-device-section-phone-item">
							<div v-if="device" v-text="device.title"></div>
							<div v-if="deviceModel" v-text="deviceModel.title"></div>
							<div v-if="deviceColor">{{deviceColor.title}} - {{deviceColor.sub_title}}</div>
							<div></div>
							<div>
								<div class="my-cart-device-section-phone-issue-wrapper"><span>Battery Replacement</span><span>$89</span>
								</div>
								<div class="my-cart-device-section-phone-issue-wrapper">
									<span>Back Glass Replacement</span><span>$79</span></div>
							</div>
						</div>
					</div>
					<div class="my-cart-small-price-section">
						<div class="my-cart-small-total-price"><span>Total Price</span><span
								class="my-cart-small-text-bold">$168.00</span></div>
					</div>
				</div>
			</div>

			<div class="my-cart-small-items-container">
				<div class="my-cart-small-items-wrapper my-cart-small-items-wrapper-colored">
					<div>
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1"
							 xmlns="http://www.w3.org/2000/svg">
							<defs>
								<linearGradient x1="100%" y1="50%" x2="21.9573333%" y2="50%" id="linearGradient-1">
									<stop stop-color="#12FFCD" stop-opacity="0.329710145" offset="0%"></stop>
									<stop stop-color="#12FFCD" stop-opacity="0" offset="100%"></stop>
								</linearGradient>
							</defs>
							<g id="Clock_20x20_icn" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g>
									<rect id="grid" x="0" y="0" width="20" height="20"></rect>
									<path d="M3,11 C3,14.8658717 6.13412835,18 10,18 C13.8658717,18 17,14.8658717 17,11 C17,7.13412835 13.8658717,4 10,4 C6.13412835,4 3,7.13412835 3,11"
										  id="Fill" fill-opacity="0.6" fill="url(#linearGradient-1)"></path>
									<path d="M2.5,11 C2.5,6.85798597 5.85798597,3.5 10,3.5 C14.142014,3.5 17.5,6.85798597 17.5,11 C17.5,15.142014 14.142014,18.5 10,18.5 C5.85798597,18.5 2.5,15.142014 2.5,11 Z"
										  id="Stroke" stroke="#0161C7" stroke-linecap="round"
										  stroke-linejoin="round"></path>
									<rect id="line" fill="#0161C7" x="7" y="1" width="6" height="1"></rect>
									<rect id="line" fill="#0161C7" x="9" y="11" width="6" height="1"></rect>
									<rect id="line" fill="#0161C7"
										  transform="translate(9.500000, 9.000000) rotate(-90.000000) translate(-9.500000, -9.000000) "
										  x="6.5" y="8.5" width="6" height="1"></rect>
								</g>
							</g>
						</svg>
						<span
								class="time-title">Time</span>
						<div class="my-cart-small-items-edit">
							<svg width="12px" height="11px" viewBox="0 0 12 11" version="1.1"
								 xmlns="http://www.w3.org/2000/svg">
								<defs></defs>
								<g id="icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
								   transform="translate(-69.000000, -75.000000)">
									<g id="edit"
									   transform="translate(74.207664, 81.292336) rotate(-315.000000) translate(-74.207664, -81.292336) translate(71.707664, 74.292336)"
									   fill="#0161C7">
										<path d="M4.67241444,10.5129325 L2.98743926,13.0641555 C2.75916274,13.4097892 2.29391652,13.5049261 1.94828275,13.2766496 C1.86673107,13.2227881 1.79649272,13.1535068 1.74151762,13.0727016 L-1.10134124e-13,10.5129325 L-1.09690035e-13,1 C-1.0975767e-13,0.44771525 0.44771525,1.08015131e-13 1,1.07913678e-13 L3.67241444,1.07913678e-13 C4.22469919,1.07812225e-13 4.67241444,0.44771525 4.67241444,1 L4.67241444,10.5129325 Z"
											  id="Combined-Shape"></path>
									</g>
								</g>
							</svg>
							<span>Edit</span></div>
					</div>
					<div class="my-cart-small-items-text">Feb 04 2019, 10am - 11am</div>
				</div>
			</div>

			<div class="my-cart-small-items-container">
				<div class="my-cart-small-items-wrapper ">
					<div>
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1"
							 xmlns="http://www.w3.org/2000/svg">
							<defs>
								<linearGradient x1="100%" y1="50%" x2="21.9573333%" y2="50%" id="linearGradient-1">
									<stop stop-color="#12FFCD" stop-opacity="0.329710145" offset="0%"></stop>
									<stop stop-color="#12FFCD" stop-opacity="0" offset="100%"></stop>
								</linearGradient>
							</defs>
							<g id="Location_20x20_icn" stroke="none" stroke-width="1" fill="none"
							   fill-rule="evenodd">
								<g>
									<rect id="grid" x="0" y="0" width="20" height="20"></rect>
									<path d="M9.98621629,1 C5.57478864,1 2,4.46474089 2,8.671613 C2,14.8630361 9.55218977,19.6961303 9.87370277,19.9561425 C9.91024562,19.9855897 9.95480226,20 10,20 C10.0448772,20 10.0897544,19.9855897 10.1262972,19.9561425 C10.4474897,19.6961303 18,14.6033371 18,8.671613 C18,4.46474089 14.3973234,1 9.98621629,1"
										  id="Fill-1" fill-opacity="0.6" fill="url(#linearGradient-1)"></path>
									<path d="M7,8 C7,9.65675 8.343,11 10,11 C11.65675,11 13,9.65675 13,8 C13,6.34325 11.65675,5 10,5 C8.343,5 7,6.34325 7,8"
										  id="Fill-5" fill-opacity="0.6" fill="url(#linearGradient-1)"></path>
									<path d="M9.9973922,19.4335133 C10.2738434,19.2285863 10.7879571,18.8412153 11.1304775,18.566531 C12.0175707,17.8551264 12.8853775,17.0753294 13.6966172,16.2275152 C16.0825881,13.7339738 17.5,11.1660451 17.5,8.671613 C17.5,4.74023364 14.1200746,1.5 9.98621629,1.5 C5.8578659,1.5 2.5,4.73452704 2.5,8.671613 C2.5,11.2824917 3.91558327,13.8692529 6.3004586,16.3198373 C7.10991212,17.1515932 7.97534479,17.9088646 8.86288635,18.5966083 C9.20687876,18.8631632 9.72120458,19.236143 9.9973922,19.4335133 Z"
										  id="Stroke-3" stroke="#0161C7"></path>
									<path d="M7.5,8 C7.5,9.38070041 8.61923516,10.5 10,10.5 C11.3806076,10.5 12.5,9.38060763 12.5,8 C12.5,6.61939237 11.3806076,5.5 10,5.5 C8.61923516,5.5 7.5,6.61929959 7.5,8 Z"
										  id="Stroke-7" stroke="#0161C7"></path>
								</g>
							</g>
						</svg>
						<span class="time-title">Location</span></div>
				</div>
			</div>

			<div class="my-cart-small-items-container">
				<div class="my-cart-small-items-wrapper ">
					<div>
						<svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1"
							 xmlns="http://www.w3.org/2000/svg">
							<defs>
								<linearGradient x1="100%" y1="50%" x2="21.9573333%" y2="50%" id="linearGradient-1">
									<stop stop-color="#12FFCD" stop-opacity="0.329710145" offset="0%"></stop>
									<stop stop-color="#12FFCD" stop-opacity="0" offset="100%"></stop>
								</linearGradient>
							</defs>
							<g id="Contact_20x20_icn" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<path d="M16.7303021,19 L3.27005684,19 C2.02123841,19 1,17.978423 1,16.7299884 L1,3.27001157 C1,2.021577 2.02123841,1 3.27005684,1 L16.7303021,1 C17.9787616,1 19,2.021577 19,3.27001157 L19,16.7299884 C19,17.978423 17.9787616,19 16.7303021,19 Z"
									  id="Fill" fill-opacity="0.6" fill="url(#linearGradient-1)"></path>
								<path d="M16.7303021,19.5 L3.27005684,19.5 C1.74520983,19.5 0.5,18.2546792 0.5,16.7299884 L0.5,3.27001157 C0.5,1.74532081 1.74520983,0.5 3.27005684,0.5 L16.7303021,0.5 C18.2548581,0.5 19.5,1.74538871 19.5,3.27001157 L19.5,16.7299884 C19.5,18.2546113 18.2548581,19.5 16.7303021,19.5 Z"
									  id="Stroke" stroke="#0161C7" stroke-linecap="round"
									  stroke-linejoin="round"></path>
								<path d="M6.5,7.00019622 C6.5,5.06717635 8.06717635,3.5 10.0001962,3.5 C11.9328944,3.5 13.5,5.06724715 13.5,7.00019622 C13.5,8.93309067 11.9329491,10.5 10.0001962,10.5 C8.06712171,10.5 6.5,8.93316147 6.5,7.00019622 Z"
									  id="Stroke" stroke="#0161C7" stroke-linecap="round"
									  stroke-linejoin="round"></path>
								<path d="M15,16 C15,12.8488233 12.5911826,10.5 9.89017395,10.5 C7.18916531,10.5 5,12.8488233 5,16"
									  id="Stroke" stroke="#0161C7" opacity="0.900000036" stroke-linecap="round"
									  stroke-linejoin="round"></path>
							</g>
						</svg>
						<span class="time-title">Contact Details</span></div>
				</div>
			</div>

		</div>
	</div>
</template>

<script>
	export default {
		name: "cart",
		computed: {
			device() {
				return this.$store.state.device;
			},
			deviceModel() {
				return this.$store.state.deviceModel;
			},
			deviceColor() {
				return this.$store.state.deviceColor;
			},
		},
		methods: {
			editDevice() {
				this.$router.push('/');
			}
		}
	}
</script>

<style lang="scss">
	.my-cart-wrapper {
		position: fixed;
		top: 104px;
		right: 0;
		height: calc(100% - 104px);
		box-sizing: border-box;
		width: 355px;
		background-color: #fff;
		box-shadow: -50px 7px 50px 0 hsla(0, 0%, 79%, .25);
		display: flex;
		flex-direction: column;
		transition: all .4s ease;
		overflow: auto;
	}

	.my-cart-small-items-container {
		padding: 10px 20px;
	}

	.my-cart-small-items-wrapper-colored {
		background-color: #eff2f5;
		border-radius: 6px;
	}

	.my-cart-small-items-wrapper {
		padding: 15px;
		color: #46515b;

		.time-title {
			width: 50px;
			margin-left: 10px;
			font-size: 19px;
			line-height: 21px;
		}
	}

	.my-cart-small-items-edit {
		color: #0161c7;
		float: right;
		font-size: 13px;

		svg {
			margin-right: 3px;
		}
	}

	.my-cart-device-section-phone-item {
		padding: 10px 0;
		margin: 10px 0;

		> div {
			line-height: 22px;
		}
	}

	.my-cart-small-price-section {
		border-top: 2px solid #e0e4e7;
		padding-top: 10px;
		margin-top: 20px;
	}

	.my-cart-small-total-price {
		font-size: 18px;
		align-items: center;
		margin-top: 5px;
		height: 25px;
		display: flex;
		justify-content: space-between;
	}
</style>
