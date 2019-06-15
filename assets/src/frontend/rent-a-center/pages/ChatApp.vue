<template>
	<div class="demo-layout stackonet-dashboard mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
        	<header class="demo-header mdl-layout__header">
			<div class="mdl-layout__header-row">
				<span class="mdl-layout-title"> Andrew andy</span>
				<div class="mdl-layout-spacer"></div>
				<mdl-button type="icon" id="hdrbtn">
					<icon small><i class="fa fa-comment-o" aria-hidden="true"></i></icon>
				</mdl-button>
				<ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="hdrbtn">
					<li class="mdl-menu__item"><a :href="home_url">Home</a></li>
					<li class="mdl-menu__item"><a :href="logout_url">Log out</a></li>
				</ul>
			</div>
		</header>
<div class="container" id="shablabox-border">
         <div class="messaging" id="box-color">
  <div class="inbox_msg" id="shablabox-border">
		<div class="demo-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">

      <div class="inbox_people">
                    <div class="field has-addons">
                        <div class="type_msg">
                            <div class="input_msg_write">
                                <input type="text" class="write_msg"
                                       placeholder="Type Manager name OR store name to search"/>
                            </div>
                        </div>
                    </div>
                    <div class="inbox_chat">
                        <div class="chat_list active_chat">
                            <div class="chat_people">
                                <div class="chat_img">
                                    <figure class="image is-48x48">
                                        <img class="is-rounded"
                                             src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
                                    </figure>
                                </div>
                                <div class="chat_ib">
                                    <h5>Domnic Brown<span class="chat_date">2 mins</span></h5>
                                    <p>It is a long established
                                        fact....</p>
                                </div>
                            </div>
                        </div>
                        <div class="chat_list">
                            <div class="chat_people">
                                <div class="chat_img">
                                    <figure class="image is-48x48">
                                        <img class="is-rounded"
                                             src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
                                    </figure>
                                </div>
                                <div class="chat_ib">
                                    <h5>Garry Sobars <span class="chat_date">10 mins</span></h5>
                                    <p>English versions from the
                                        1914...</p>
                                </div>
                            </div>
                        </div>
                        <div class="chat_list">
                            <div class="chat_people">
                                <div class="chat_img">
                                    <figure class="image is-48x48">
                                        <img class="is-rounded"
                                             src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
                                    </figure>
                                </div>
                                <div class="chat_ib">
                                    <h5>Jeson Born <span class="chat_date">25 mins</span></h5>
                                    <p>>It is a long established
                                        fact....</p>
                                </div>
                            </div>
                        </div>
                        <div class="chat_list">
                            <div class="chat_people">
                                <div class="chat_img">
                                    <figure class="image is-48x48">
                                        <img class="is-rounded"
                                             src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
                                    </figure>
                                </div>
                                <div class="chat_ib">
                                    <h5>John Smith<span class="chat_date">1 hour</span></h5>
                                    <p>The standard chunk of Lorem
                                        Ip....</p>
                                </div>
                            </div>
                        </div>
                        <div class="chat_list">
                            <div class="chat_people">
                                <div class="chat_img">
                                    <figure class="image is-48x48">
                                        <img class="is-rounded"
                                             src="//localhost:3000/wp-content/uploads/2019/06/img.jpg">
                                    </figure>
                                </div>
                                <div class="chat_ib">
                                    <h5>John Smith <span class="chat_date">1 day</span></h5>
                                    <p>The standard chunk of Lorem
                                        Ip....</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
		</div>
	<main class="mdl-layout__content">
			<div class="demo-content">
    <div class="mesgs">
                    <div>
                        <div class="incoming_msg">
                            <div class="received_msg">
                                <div class="received_withd_msg">
                                    <p>English versions from the
                                        1914...
                                        <br>
                                        <span class="time_date"> Tuesday</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="outgoing_msg">
                            <div class="sent_msg">
                                <p>There are many variations of passages of<br>
                                    <span class="time_date-sent"> 3:12:36 PM</span>
                                </p>
                            </div>
                        </div>
                        <div class="incoming_msg">

                            <div class="received_msg">
                                <div class="received_withd_msg">
                                    <p>All the Lorem Ipsum generators on the<br>
                                        <span class="time_date">3:12:45 PM</span>
                                    </p>

                                </div>
                            </div>
                        </div>
                        <div class="outgoing_msg">
                            <div class="sent_msg">
                                <p>There are many variations of passages of
                                    <br>
                                    <span class="time_date-sent"> 3:13:04 PM</span></p>

                            </div>
                        </div>
                        <div class="incoming_msg">

                            <div class="received_msg">
                                <div class="received_withd_msg">
                                    <p>It is a long established fact<br>
                                        <span class="time_date"> 3:11:25 PM</span>
                                    </p>

                                </div>
                            </div>
                        </div>
                        <div class="outgoing_msg">
                            <div class="sent_msg">
                                <p>The standard chunk of Lorem Ipsum used
                                    since the 1500s is reproduced below for those interested<br>
                                    <span class="time_date-sent"> 3:11:25 PM</span>
                                </p>

                            </div>
                        </div>
                    </div>
                    <div class="type_msg">
                        <div class="input_msg_write">
                            <div id="fa-plus"><i class="fa">&#xf067;</i></div>
                            <input id="input" type="text" class="write_msg" placeholder="Type a message"/>
                                <icon id="button" class="msg_send_btn">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 30 35">
                                    <path d="M17 32c-0.072 0-0.144-0.008-0.217-0.024-0.458-0.102-0.783-0.507-0.783-0.976v-15h-15c-0.469 0-0.875-0.326-0.976-0.783s0.129-0.925 0.553-1.123l30-14c0.381-0.178 0.833-0.098 1.13 0.199s0.377 0.749 0.199 1.13l-14 30c-0.167 0.358-0.524 0.577-0.906 0.577zM5.508 14h11.492c0.552 0 1 0.448 1 1v11.492l10.931-23.423-23.423 10.931z"></path>
                                </svg>
                                </icon>
                        </div>
                    </div>
                </div>
            </div>
    </main>
  </div>
         </div>
</div>
	</div>
</template>

<script>
	import {MaterialLayout} from '../../../material-design-lite/layout/MaterialLayout';
	import {MaterialMenu} from '../../../material-design-lite/menu/MaterialMenu';
    import Icon from "../../../shapla/icon/icon";
    import Columns from "../../../shapla/columns/columns";
    import Column from "../../../shapla/columns/column";




    export default {
        name: "ChatApp",
        components: {Column, Columns, Icon},
        mounted() {
			this.$store.commit('SET_LOADING_STATUS', false);
			let dashboard = this.$el;
			new MaterialLayout(dashboard);
			new MaterialMenu(dashboard.querySelector('.mdl-menu'));
			new MaterialMenu(dashboard.querySelector('[for="accbtn"]'));
		},

    }
</script>
<style lang="scss" scoped>
@import "../../../material-design-lite/layout/layout";
@import "../../../material-design-lite/menu/menu";
#shablabox-border{
    border:none;
}
	.demo-avatar {
	width: 48px;
	height: 48px;
	border-radius: 24px;
}

.demo-layout .mdl-layout__header .mdl-layout__drawer-button {
	color: rgba(0, 0, 0, 0.54);
}

.mdl-layout__drawer .avatar {
	margin-bottom: 16px;
}

.demo-drawer {
	border: none;
}

/* iOS Safari specific workaround */
.demo-drawer .mdl-menu__container {
	// z-index: -1;
}

.demo-drawer .demo-navigation {
	z-index: -2;
}

/* END iOS Safari specific workaround */
.demo-drawer .mdl-menu .mdl-menu__item {
	display: flex;
	align-items: center;
}

.demo-drawer-header {
	box-sizing: border-box;
	display: flex;
	flex-direction: column;
	justify-content: flex-end;
	padding: 16px;
	height: 151px;
	background-image: linear-gradient(-90deg, #f9a73b, #f58730);
	color: rgb(255, 255, 255);
}

.demo-avatar-dropdown {
	display: flex;
	position: relative;
	flex-direction: row;
	align-items: center;
	width: 100%;
}

.demo-navigation {
	flex-grow: 1;
}

.demo-layout .demo-navigation .mdl-navigation__link {
	display: flex !important;
	flex-direction: row;
	align-items: center;
	color: rgba(#000, 0.85);
	font-weight: 500;
	padding: 16px;

	&:hover,
	&.is-active {
		background-image: linear-gradient(-90deg, #f9a73b, #f58730);
		color: #fff;
	}
}

.demo-header .mdl-layout__header-row {
	background-image: linear-gradient(-90deg, #f9a73b, #f58730);
}

.demo-content {
	padding: 30px;
}

.mdl-icon-burger {
		color: #ffffff;

		display: inline-block;
		font: normal normal normal 14px/1 FontAwesome;
		font-size: inherit;
		text-rendering: auto;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;

		&:before {
			content: "\f0c9";
		}
	}

	.user-information-box {
		&__avatar {
			width: 48px;
			height: 48px;
			margin: auto;
		}
	}

	.mdl-navigation__link {
		cursor: pointer;
	}
    .mdl-layout__drawer{

    }
</style>