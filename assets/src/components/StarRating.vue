<template>
	<div class="star-rating">
		<label v-for="rating in ratings" class="star-rating__star"
			   :class="{'is-selected': ((value >= rating) && value != null), 'is-disabled': disabled}"
			   @click="set(rating)" @mouseover="star_over(rating)" @mouseout="star_out">
			<input type="radio" class="star-rating star-rating__checkbox"
				   :value="rating" :name="name" :disabled="disabled" @input="$emit('input',$event.target.value)">★
		</label>
	</div>
</template>

<script>
	export default {
		name: "StarRating",
		props: {
			id: String,
			name: String,
			ratings: {
				type: Array, default: function () {
					return [1, 2, 3, 4, 5];
				}
			},
			value: {type: [String, Number], default: null},
			disabled: {type: Boolean, default: false},
			required: {type: Boolean, default: false}
		},
		computed: {},
		data() {
			return {
				temp_value: null,
			};
		},
		methods: {
			/*
             * Behaviour of the stars on mouseover.
             */
			star_over(index) {
				if (!this.disabled) {
					this.temp_value = this.value;
					// this.$emit('input', index);
					return this.value = index;
				}
			},

			/*
             * Behaviour of the stars on mouseout.
             */
			star_out() {
				if (!this.disabled) {
					return this.value = this.temp_value;
					// this.$emit('input', this.temp_value);
				}
			},

			/*
             * Set the rating.
             */
			set(value) {
				if (!this.disabled) {
					this.temp_value = value;
					this.$emit('input', value);
					return this.value = value;
				}
			}
		}
	}
</script>

<style lang="scss">
	.star-rating {
		&__star {
			display: inline-block;
			padding: 3px;
			vertical-align: middle;
			line-height: 1;
			font-size: 1.5em;
			color: #ABABAB;
			transition: color .2s ease-out;

			&:hover {
				cursor: pointer;
			}

			&.is-selected {
				color: #FFD700;
			}

			&.is-disabled:hover {
				cursor: default;
			}
		}

		&__checkbox {
			position: absolute;
			overflow: hidden;
			clip: rect(0 0 0 0);
			height: 1px;
			width: 1px;
			margin: -1px;
			padding: 0;
			border: 0;
		}
	}
</style>
