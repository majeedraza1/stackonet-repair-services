<template>
	<div class="rent-a-center-dashboard-container">

		<div :tablet="12">
			<div class="section-total-damaged-devices">
				<div>Notifications</div>
				<box class="shablabox-border">
					<div class="columns">
						<div class="column">
							Filter
						</div>
						<div class="column">
							<div class="select">
								<select>
									<option>Status:All</option>
									<option> options</option>
								</select>
							</div>
						</div>
						<div class="column">
							<div class="select">
								<select>
									<option>Date:Today</option>
									<option> options</option>
								</select>
							</div>
						</div>
						<div class="column">
							<div class="field is-grouped">
								<div class="control has-icons-left">
									<span class="icon is-small is-left"><i
										class="fa fa-search fa-xs searchIcon"></i></span>
									<input class="input" type="text" placeholder="Quick Search"/>
								</div>
							</div>
						</div>
					</div>
				</box>
				<columns>
					<column :tablet="3" :mobile="6">
						<box>
							<div class="columns is-vcentered">
								<div class="column is-2">
									<div class="letterCircle"><strong>A</strong></div>
								</div>
								<div class="column">
									<p>1 h ago</p>
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones</p>
									<hr width="30%">
								</div>
							</div>
							<div class="columns">
								<div class="column is-2">
									<div class="letterCircle"><strong>S</strong></div>
								</div>
								<div class="column is-10 is-pulled-right">
									<p>1 h ago</p>
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones</p>
									<hr width="30%">
								</div>
							</div>
							<div class="columns">
								<div class="column is-2">
									<div class="letterCircle"><strong>A</strong></div>
								</div>
								<div class="column is-10 is-pulled-right">
									<p>1 h ago</p>
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones</p>
									<hr width="30%">
								</div>
							</div>
							<div class="columns">
								<div class="column is-2">
									<div class="letterCircle"><strong>M</strong></div>
								</div>
								<div class="column is-10 is-pulled-right">
									<p>1 h ago</p>
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones</p>
									<hr width="30%">
								</div>
							</div>
							<div class="columns">
								<div class="column is-2">
									<div class="letterCircle"><strong>V</strong></div>
								</div>
								<div class="column is-10 is-pulled-right">
									<p>1 h ago</p>
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones</p>
									<hr width="30%">
								</div>
							</div>
						</box>
					</column>
					<column :tablet="9" :mobile="6">
						<box>
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
						<box class="notificaion-box-right">
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
						<box>
							<div class="columns">
								<div class="column is-2">
									<figure class="image is-48x48">
										<img class="is-rounded"
											 src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
									</figure>
								</div>
								<div class="column is-8">
									<p><strong>Andrew Arce</strong></p>
									<p>added 3 phones-iphone 7 plus,samsungS9,iphone8</p>
								</div>
								<div class="column is-2"><p style="opacity:0.5">1 hr ago</p></div>
							</div>
						</box>
					</column>

				</columns>
			</div>
		</div>

	</div>
</template>

<script>
	import {columns, column} from 'shapla-columns'
	import icon from '../../../shapla/icon/icon'
	import imageContainer from '../../../shapla/image/image'
	import box from '../../../shapla/box/box'
	import MdlButton from "../../../material-design-lite/button/mdlButton";


	export default {
		name: "Invoices",
		components: {MdlButton, columns, column, icon, imageContainer, box},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SNACKBAR', {
				message: 'Check if working.'
			});
		}
	}
</script>

<style lang="scss" scoped>

	.letterCircle {
		border-radius: 50%;
		width: 36px;
		height: 36px;
		padding: 8px;
		background-color: #F4F5F9;
		text-align: center;
	}

	.shapla-box {
		background-color: white;
		border: 0px solid rgba(0, 0, 0, 0.12);
		border-radius: 6px;
		padding: 1.25rem;
	}

</style>
