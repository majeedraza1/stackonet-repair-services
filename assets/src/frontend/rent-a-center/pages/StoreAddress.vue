<template>
	<div>
		<columns multiline mobile centered>
			<column :mobile="12" :tablet="6" :desktop="4" :fullhd="3" v-for="index in [1,10,30,44,5658,6,7,8,9]" :key="index">

				<div class="store-address-box">
					<div class="store-address-box__heading">
						Main Center store
					</div>
					<div class="store-address-box__content">
						Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam, eveniet.
					</div>
					<div class="store-address-box__actions">
						<ul class="store-address-box__button-group">
							<li class="store-address-box__button--edit"><a href="#">Edit</a></li>
							<li class="store-address-box__button--message"><a href="#">Message</a></li>
							<li class="store-address-box__button--delete"><a href="#">Delete</a></li>
						</ul>
					</div>
				</div>

			</column>
		</columns>
	</div>
</template>

<script>
	import columns from '../../../shapla/columns/columns'
	import column from '../../../shapla/columns/column'

	export default {
		name: "StoreAddress",
		components: {columns, column}
	}
</script>

<style lang="scss">
	.store-address-box {
		border: 1px solid rgba(#000, 0.2);
		border-bottom-left-radius: 20px;
		border-bottom-right-radius: 20px;

		&__heading {
			background: #eceaed;
			text-align: center;
			font-weight: bold;
			font-size: 16px;
			padding: 1rem;
		}

		&__content {
			padding: 1rem;
			min-height: 130px;
			justify-content: center;
			align-items: center;
			display: flex;
		}

		&__actions {
		}

		&__button-group {
			margin: 0;
			padding: 0;
			list-style-type: none;
			display: flex;
			justify-content: space-between;

			li {
				display: block;
				flex-grow: 1;
				width: 25%;

				&:not(:last-child) {
					margin-right: 5px;
				}

				&:first-child a {
					border-bottom-left-radius: 20px;
				}

				&:last-child a {
					border-bottom-right-radius: 20px;
				}

			}

			a {
				display: block;
				background: linear-gradient(-90deg, #f88837, #faa341);
				color: black;
				text-align: center;
				padding: 1rem;
			}
		}

		&__button--message {
			width: 40% !important;
		}
	}
</style>
