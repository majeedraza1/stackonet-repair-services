<template>
	<div class="fp-tns-slider-outer arrows-visible-hover">
		<div class="fp-tns-slider-controls">
	            <span class="prev" data-controls="prev">
	                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 32 32">
	                    <path
								d="M12.3 17.71l6.486 6.486c0.39 0.39 1.024 0.39 1.414 0s0.39-1.024 0-1.414l-5.782-5.782 5.782-5.782c0.39-0.39 0.39-1.024 0-1.414s-1.024-0.39-1.414 0l-6.486 6.486c-0.196 0.196-0.292 0.452-0.292 0.71s0.096 0.514 0.292 0.71z"></path>
	                </svg>
	            </span>
			<span class="next" data-controls="next">
	                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 32 32">
	                    <path
								d="M13.8 24.196c0.39 0.39 1.024 0.39 1.414 0l6.486-6.486c0.196-0.196 0.294-0.454 0.292-0.71 0-0.258-0.096-0.514-0.292-0.71l-6.486-6.486c-0.39-0.39-1.024-0.39-1.414 0s-0.39 1.024 0 1.414l5.782 5.782-5.782 5.782c-0.39 0.39-0.39 1.024 0 1.414z"></path>
	                </svg>
	            </span>
		</div>
		<div class="client-testimonial fp-tns-slider">
			<div class="client-testimonial-item item" v-for="item in items" :key="item.id">
				<div class="demo-card-wide mdl-card mdl-shadow--2dp">
					<div class="mdl-card__supporting-text">
						<div class="client-testimonial-avatar">
							<div class="client-testimonial-avatar-image">
								<template v-if="item.image">
									<img :src="item.image.src" alt="">
								</template>
							</div>
						</div>
						<div class="client-testimonial-description" v-html="item.description"></div>
					</div>
					<div class="mdl-card__actions mdl-card--border">
						<div class="client-name" v-html="item.name"></div>
						<div class="mdl-layout-spacer"></div>
						<star-rating :value="item.rating" disabled></star-rating>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import {tns} from 'tiny-slider/src/tiny-slider';
	import StarRating from '../components/StarRating'

	export default {
		name: "TestimonialCarousel",
		components: {StarRating},
		props: {
			items: {type: Array, default: () => []}
		},
		updated() {
			setTimeout((self) => {

				let controls = self.$el.querySelector('.fp-tns-slider-controls'),
						slider = self.$el.querySelector('.client-testimonial');

				new tns({
					container: slider,
					controls: true,
					controlsContainer: controls,
					slideBy: 1,
					gutter: 10,
					nav: false,
					autoplay: true,
					edgePadding: 0,
					items: 1,
					responsive: {
						600: {items: 2},
						900: {items: 3},
					}
				});

			}, 1000, this);
		}
	}
</script>

<style lang="scss">

</style>
