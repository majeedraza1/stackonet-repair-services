<template>
	<div class="rent-a-center-account-detail-container">
		<box class="shablabox-border" id="box-color">
			<form>
				<div class="container">
					<columns>
						<column><label for="first-name"><b>First Name</b></label>
							<input type="text" placeholder="Brain" name="first-name" required></column>
						<column><label for="last-name"><b>Last Name</b></label>
							<input type="text" placeholder="MacManus" name="last-name" required></column>
					</columns>
					<label for="display-name"><b>Display Name</b></label>
					<input type="text" placeholder="Brain MacManus" name="display-name" required>

					<label for="email"><b>Email Address</b></label>
					<input type="text" placeholder="brianmacmanus@phonerepairsasap.com" name="email" required>
					<h3>Password Change</h3><br>
					<label for="psw"><b>Current Password</b></label> (leave blank to leave unchanged)
					<input type="password" placeholder="Enter Password" name="psw" required>

					<label for="psw-repeat"><b>New Password</b></label> (leave blank to leave unchanged)
					<input type="password" placeholder="Repeat Password" name="psw-repeat" required>

					<button type="submit" class="submitbtn">Submit</button>
				</div>

			</form>

		</box>
	</div>
</template>

<script>
	import {columns, column} from "shapla-columns";
	import Box from '../../../shapla/box/box';


	export default {
		name: "AccountDetails",
		components: {column, columns, Box},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
		}
	}
</script>
<style lang="scss">

	.rent-a-center-account-detail-container {
		//form
		.container {
			padding: 16px;
		}

		/* input fields */
		input[type=text], input[type=password] {
			width: 100%;
			padding: 15px;
			margin: 5px 0 22px 0;
			display: inline-block;
			border: 1px solid #c2c2c2;
			background-color: white;

		}

		input[type=text]:focus, input[type=password]:focus {
			background-color: #ddd;
			outline: none;
		}

		/* Set a style for the submit button */
		.submitbtn {
			background-image: linear-gradient(90deg, #f9a73b, #f58730);
			color: white;
			padding: 14px 38px;
			margin: 8px 0;
			border: none;
			cursor: pointer;
			opacity: 0.9;
		}

		.submitbtn:hover {
			opacity: 1;
		}
	}

</style>
