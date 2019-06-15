<template>
	<div class="rent-a-center-dashboard-container">
		<columns>
			<column :tablet="12">
				<div class="section-reports">
					<box class="shablabox-border">
						<div class="section-title">Rent a Centers Orders</div>
						<div class="columns">
  <div class="column">
    Total Orders
  </div>
  <div class="column">
    Total Delivered
  </div>
  <div class="column">
    Processing Orders
  </div>
  <div class="column">
    Orders not Repaired
  </div>
 <div class="column">
    <button class="button is-medium is-warning">Add New Orders</button>
  </div>
</div>
					</box>
				</div>
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
      <span  class="icon is-small is-left"><i class="fa fa-search fa-xs searchIcon"></i></span>
    <input class="input" type="text" placeholder="Quick Search" />
  </div>
</div>
  </div>
</div>
				</box>
				<div class="section-total-damaged-devices">
					<box class="shablabox-border" id="box-color">
	<div class="section-title">6,500 Orders Fund</div>
 <div class="columns">
  <div class="column">
<div class="control">
  <label class="radio">
    <input type="radio" name="answer">
    Order
  </label>
</div>
  </div>
  <div class="column">
    Date
  </div>
  <div class="column">
    Name
  </div>
  <div class="column">
    Store Name
  </div>
  <div class="column">
  Payment Status
  </div>
   <div class="column">
 Order Status
  </div>
   <div class="column">
   Total
  </div>
</div>
<box>
  <div class="columns">
  <div class="column">
      <div class="control">
  <label class="radio">
    <input type="radio" name="answer">
  #1234
  </label>
</div>
  </div>
  <div class="column">
10 April 2019<br>
    11:21 AM
  </div>
  <div class="column">
    Andrew Arce
  </div>
  <div class="column">
    Florida main center
  </div>
     <div class="column">
 Processing
  </div>
     <div class="column">
    Repairing
  </div>
     <div class="column">
$210.16
  </div>
</div>
</box>
                      <box>
  <div class="columns">
  <div class="column">
      <div class="control">
  <label class="radio">
    <input type="radio" name="answer">
  #1234
  </label>
</div>
  </div>
  <div class="column">
10 April 2019<br>
    11:21 AM
  </div>
  <div class="column">
    Andrew Arce
  </div>
  <div class="column">
    Florida main center
  </div>
     <div class="column">
 Processing
  </div>
     <div class="column">
    Repairing
  </div>
     <div class="column">
$210.16
  </div>
</div>
</box>
                      <box>
  <div class="columns">
  <div class="column">
      <div class="control">
  <label class="radio">
    <input type="radio" name="answer">
  #1234
  </label>
</div>
  </div>
  <div class="column">
10 April 2019<br>
    11:21 AM
  </div>
  <div class="column">
    Andrew Arce
  </div>
  <div class="column">
    Florida main center
  </div>
     <div class="column">
 Processing
  </div>
     <div class="column">
    Repairing
  </div>
     <div class="column">
$210.16
  </div>
</div>
</box>
<box>
  <div class="columns">
  <div class="column">
      <div class="control">
  <label class="radio">
    <input type="radio" name="answer">
  #1234
  </label>
</div>
  </div>
  <div class="column">
10 April 2019<br>
    11:21 AM
  </div>
  <div class="column">
    Andrew Arce
  </div>
  <div class="column">
    Florida main center
  </div>
     <div class="column">
 Processing
  </div>
     <div class="column">
    Repairing
  </div>
     <div class="column">
$210.16
  </div>
</div>
</box>
                      <box>
  <div class="columns">
  <div class="column">
      <div class="control">
  <label class="radio">
    <input type="radio" name="answer">
  #1234
  </label>
</div>
  </div>
  <div class="column">
10 April 2019<br>
    11:21 AM
  </div>
  <div class="column">
    Andrew Arce
  </div>
  <div class="column">
    Florida main center
  </div>
     <div class="column">
 Processing
  </div>
     <div class="column">
    Repairing
  </div>
     <div class="column">
$210.16
  </div>
</div>
</box>
                      <box>
  <div class="columns">
  <div class="column">
      <div class="control">
  <label class="radio">
    <input type="radio" name="answer">
  #1234
  </label>
</div>
  </div>
  <div class="column">
10 April 2019<br>
    11:21 AM
  </div>
  <div class="column">
    Andrew Arce
  </div>
  <div class="column">
    Florida main center
  </div>
     <div class="column">
 Processing
  </div>
     <div class="column">
    Repairing
  </div>
     <div class="column">
$210.16
  </div>
</div>
</box>
                             <div class="vl"></div>
					</box>
				</div>
			</column>

		</columns>

	</div>
</template>

<script>
	import columns from '../../../shapla/columns/columns'
	import column from '../../../shapla/columns/column'
	import icon from '../../../shapla/icon/icon'
	import imageContainer from '../../../shapla/image/image'
	import box from '../../../shapla/box/box'
	import StatusBox from '../../components/StatusBox'
    import Search from "../../../components/Search";

	export default {
		name: "Dashboard",
		components: {Search, columns, column, icon, imageContainer, StatusBox, box},
		mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			this.$store.commit('SET_SNACKBAR', {
				message: 'Check if working.'
			});
		}
	}
</script>

<style lang="scss">
    #box-color{
        background-color: #f4f8fb;
    }
 .shablabox-border{
            border:none;
        }
    .vl {
  border-left: 6px solid grey;
  height: 500px;
        float: right;
        margin-top:-500px;
        margin-right: -50px;


}

 .shapla-column.is-9, .shapla-column.is-9-tablet {
   background-color: #E7ECF2;
}
	.rent-a-center-dashboard-container {

.select:not(.is-multiple):not(.is-loading)::after {
    border-color: black;
}
.control{
    margin-left: 30px;
    font-size: medium;
}
        .shapla-box {
    margin-bottom: 10px;
}
		.store-managers-list {
			display: flex;
			justify-content: center;

			&__item {
				display: block;
				width: 42px;

				&:not(:first-child) {
					margin-left: -10px;
				}

				img {
					border: 2px solid white;
				}
			}

			&__icon {
				background: #F1F1F1;
				border-radius: 100px;
				cursor: pointer;
			}
		}

		.section-reports,
		.section-latest-status,

		.section-store-managers {
			margin-bottom: 1.5rem;
		}

		.section-title {
			margin-bottom: 1em;
			font-weight: bold;
			font-size: 16px;
		}
	}

	.recent-activities-list {
		&__item {
			display: flex;

			&:not(:last-child) {
				margin-bottom: 1rem;
				padding-bottom: 1rem;
				border-bottom: 1px solid rgba(#000, .2);
			}
		}

		&__avatar {
			padding-right: 8px;
			width: 48px;
		}

		&__content {
			font-size: 16px;
			line-height: 1.5;
		}

		&__time {
			color: rgba(#000, .65);
			font-size: .75em;
		}

		&__title {
			font-size: 0.875em;
			font-weight: bold;
		}

		&__description {
			font-size: 0.875em;
		}
	}
</style>
