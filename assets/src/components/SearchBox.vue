<template>
	<form class="place-search-box" method="post" action="#" @submit.prevent="submit">
		<label for="place-search-box__input" class="screen-reader-text">Search...</label>
		<input type="search"
			   id="place-search-box__input"
			   class="place-search-box__input"
			   :value="value"
			   @input="input($event)"
		/>
		<div class="place-search-box__buttons">
			<div class="place-search-box__clear" v-if="value.length > 0">
				<delete-icon @click="clear"></delete-icon>
			</div>
			<button type="submit" class="place-search-box__submit" :disabled="disabled">
				<i class="fa fa-search"></i>
			</button>
		</div>
	</form>
</template>

<script>
	import DeleteIcon from "../shapla/delete/deleteIcon";

	export default {
		name: "SearchBox",
		components: {DeleteIcon},
		props: {
			value: {type: String, default: ''},
			disabled: {type: Boolean, default: false}
		},
		data() {
			return {}
		},
		methods: {
			submit() {
				this.$emit('submit', this.value);
			},
			input(event) {
				this.$emit('input', event.target.value);
			},
			clear() {
				this.$emit('input', '');
				this.$emit('clear', '');
			}
		}
	}
</script>

<style lang="scss" scoped>
	.place-search-box {
		position: relative;
		font-size: 1rem;
		box-sizing: border-box;

		&__input {
			box-sizing: border-box !important;
			border: 1px solid rgba(#f58730, .85);
			width: 100%;
			border-radius: 4px;
			padding: 0 5em 0 0.5em;
			height: 48px;

			&:focus {
				outline: 0;
				box-shadow: 0 0 0 0.125em rgba(#f58730, .25);
			}
		}

		&__buttons {
			position: absolute;
			top: 3px;
			right: 3px;
			display: flex;
			align-items: center;
		}

		.place-search-box__clear {
			visibility: hidden;
		}

		&:hover {
			.place-search-box__clear {
				visibility: visible;
			}
		}

		&__submit {
			box-sizing: inherit;
			border: 1px solid #f58730;
			background-color: #f58730;
			color: white;
			margin-left: .5em;
			height: 42px;
			width: 42px;
			border-radius: 3px;

			&[disabled] {
				cursor: not-allowed;
				opacity: 0.5;
			}
		}
	}
</style>
