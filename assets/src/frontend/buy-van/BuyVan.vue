<template>
	<div class="repair-van-wrapper">
		<div class="repair-van">
			<div class="shape"></div>
			<section class="repair-van__header">
				<div class="container">
					<columns>
						<column>
							<div class="repair-van__title">Expand to Mobile ASAP!</div>
							<div class="repair-van__content">
								Mobile is the next driving force in the smart tech repair industry!
								Imagine doing laser back glass removal on the go or even tapping that solder point
								because you have the POWER?
							</div>
							<div class="repair-van__content">
								Between Smartphones, Tablets, Computers and now Smart Home Installation our vans do it
								all!?
							</div>
							<columns multiline>
								<column :mobile="12">
									<h3>Puls</h3>
									<p class="repair-van__device-count">6,000 Mobile Tech</p>
								</column>
								<column :mobile="12">
									<h3>UBreakifix</h3>
									<p class="repair-van__device-count">500 Vans</p>
								</column>
							</columns>
						</column>
						<column>
							<div style="position:absolute"><img
									:src="`${img_url}/van-image.png`" alt="van">
							</div>
						</column>
					</columns>
					<div class="repair-van__sub-content">Keep your <span
							style="font-weight:700">Competitive Business</span> Edge and expand your repair tool kit!
					</div>
					<a href="tel:+1 (561) 377-6340">
						<mdl-button class="s">
							<svg data-v-ec9dc320="" aria-hidden="true" focusable="false" data-prefix="fas"
								 data-icon="phone-volume" role="img" xmlns="http://www.w3.org/2000/svg"
								 viewBox="0 0 384 512" class="svg-inline--fa fa-phone-volume fa-w-12">
								<path data-v-ec9dc320=""
									  d="M97.333 506.966c-129.874-129.874-129.681-340.252 0-469.933 5.698-5.698 14.527-6.632 21.263-2.422l64.817 40.513a17.187 17.187 0 0 1 6.849 20.958l-32.408 81.021a17.188 17.188 0 0 1-17.669 10.719l-55.81-5.58c-21.051 58.261-20.612 122.471 0 179.515l55.811-5.581a17.188 17.188 0 0 1 17.669 10.719l32.408 81.022a17.188 17.188 0 0 1-6.849 20.958l-64.817 40.513a17.19 17.19 0 0 1-21.264-2.422zM247.126 95.473c11.832 20.047 11.832 45.008 0 65.055-3.95 6.693-13.108 7.959-18.718 2.581l-5.975-5.726c-3.911-3.748-4.793-9.622-2.261-14.41a32.063 32.063 0 0 0 0-29.945c-2.533-4.788-1.65-10.662 2.261-14.41l5.975-5.726c5.61-5.378 14.768-4.112 18.718 2.581zm91.787-91.187c60.14 71.604 60.092 175.882 0 247.428-4.474 5.327-12.53 5.746-17.552.933l-5.798-5.557c-4.56-4.371-4.977-11.529-.93-16.379 49.687-59.538 49.646-145.933 0-205.422-4.047-4.85-3.631-12.008.93-16.379l5.798-5.557c5.022-4.813 13.078-4.394 17.552.933zm-45.972 44.941c36.05 46.322 36.108 111.149 0 157.546-4.39 5.641-12.697 6.251-17.856 1.304l-5.818-5.579c-4.4-4.219-4.998-11.095-1.285-15.931 26.536-34.564 26.534-82.572 0-117.134-3.713-4.836-3.115-11.711 1.285-15.931l5.818-5.579c5.159-4.947 13.466-4.337 17.856 1.304z">
								</path>
							</svg>
							Call Us Now
						</mdl-button>
					</a>
				</div>
			</section>
			<div class="repairs-van-background">
				<img :src="`${img_url}/wave.png`" alt="van">
				<div class="container">
					<div class="buy--van--form">
						<div>
							<form method="post" @submit.prevent="submitTicket" class="have-ques__form">
								<div v-if="showSuccessMessage"
									 class="message shapla-color--primary-bg shapla-color--on-primary">
									Thank you for you message
								</div>
								<columns>
									<column>
										<animated-input
												type="text"
												v-model="customer_name"
												label="Name"
												autocomplete="given-name"
										/>
									</column>
									<column>
										<animated-input
												type="text"
												v-model="customer_email"
												label="Email Address"
												autocomplete="email"
										/>
									</column>
									<column>
										<animated-input
												type="text"
												v-model="phone_number"
												label="Phone Number"
												autocomplete="tel"
										/>
									</column>
								</columns>
								<animated-input
										type="textarea"
										v-model="ticket_content"
										label="Message"
								/>

								<big-button fullwidth>Send</big-button>
							</form>
						</div>
					</div>
					<section class="repair-van__working">
						<h6> We Are Your One Stop Shop</h6>
						<h4> Here is how it works.</h4>
						<div class="repair-van__working-title">We supply the van, equipment, upgraded technology and
							construct your mobile Business! We Even Ship World Wide!
						</div>
						<div class="repair-van__working-content">
							Mobile has never been done to this magnitude.
							ASAP owns it’s custom mobile software company and constantly invests with new batteries,
							solar power, and other smart tech elements to create the highest amount of energy using the
							least amount of power.
						</div>
						<div class="repair-van-box__box-wrapper">
							<columns multiple class="has-inner-custom-padding">
								<column :desktop="3" :fullwidth="3">
									<div class="repair-van-box__box">
										<img :src="`${img_url}/get-detail.png`"
											 alt="van">
										<div class="repair-van-box__box-title">
											Upgrades
										</div>
										<div class="repair-van-box__box-content">
											Back Glass laser removal<br>
											Solder Station<br>
											Solar Power<br>
											Telsa Battery<br>
											Rims
										</div>
									</div>
								</column>
								<column :desktop="3" :fullwidth="3">
									<div class="repair-van-box__box">
										<img :src="`${img_url}/buy-van.png`" alt="van">
										<div class="repair-van-box__box-title">
											Buy a Van
										</div>
										<div class="repair-van-box__box-content">
											144” Mercedes Sprinter<br>
											Custom Wrap and graphic design<br>
											Power Pack One<br>
											Power Pack Two
										</div>
									</div>
								</column>
								<column :desktop="3" :fullwidth="3">
									<div class="repair-van-box__box">
										<img :src="`${img_url}/get-paid.png`" alt="van">
										<div class="repair-van-box__box-title">
											Full Financing!
										</div>
										<div class="repair-van-box__box-content">
											550 Fico<br>
											Low interest rates<br>
											Low or no Down Payment!
										</div>
									</div>
								</column>
								<column :desktop="3" :fullwidth="3">
									<div class="repair-van-box__box">
										<img :src="`${img_url}/start-working.png`" alt="van">
										<div class="repair-van-box__box-title">
											Start Working!
										</div>
										<div class="repair-van-box__box-content">
											<div>
												<p>Begin receiving mobile jobs!<br>
													Make idle employees efficient<br>
													Idle Employees don’t make money!
													<span v-if="showHiddenText">
													</span>
													<span v-else>..</span>
													<a @click="showHiddenText =! showHiddenText">
														<span v-if="showHiddenText">less</span>
														<span v-else>more</span>
													</a>
												</p>
												<p v-show="showHiddenText">
													We all know we can make a few thousand dollars in a few hours. But
													what do you do for the other 8 hours?<br>
													Don’t Go out of business from new trends!
												</p>
											</div>
										</div>
									</div>
								</column>
							</columns>
						</div>
					</section>
					<section class="van-software">
						<div class="van-software__box-wrapper">
							<div>
								<div class="van-software__title">
									Mobile Software
									<br>Which has everything
								</div>
								<div class="van-software__content">
									Prior to ASAP, there wasn’t any mobile software to keep perfect track of the
									efficacy of your mobile day.
									Our App Makes sure there’s never a dull moment. Even if your business is slow, our
									app will direct you where you should dispatch to in order to find jobs on the spot
									or create new future customers!
								</div>
								<div class="van-software__content">
									Additionally, ASAP creates an easy interactive environment to manage all of your
									leads while your own the go
								</div>
							</div>
							<div class="van-software__img">
								<img :src="`${img_url}/van-phone-merge.png`" alt="van">
							</div>
						</div>
					</section>
					<section class="van-wrapper">
						<div class="van-wrapper__boxes">
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon1@3x.png`" alt="van">
								</template>
								<p>You will receive confirmation email ans SMS with all the details</p>
								<p slot="hidden-text" class="hidden-text">
									Just like Uber!
									We will send you notifications ASAP to inform you of a new client service request.
									Make up to $450 a Job.
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon2@3x.png`" alt="van">
								</template>
								<p>Were selling one in every city in South Floirda</p>
								<p slot="hidden-text" class="hidden-text">
									That’s Right! We are finding Agents in every city in Florida to purchase a van.
									We will have one in every city in Florida
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon3@3x.png`" alt="van">
								</template>
								<p>Share a van with Co Workers!</p>
								<p slot="hidden-text" class="hidden-text">
									Rather make the investment in mobile alone, why not go in with some repair friends?
									Share the van when you're slow and lacking appointments or walk ins. Our Vans comes
									with enough room for two techs!
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon4@3x.png`" alt="van">
								</template>
								<p>Financing options available!</p>
								<p slot="hidden-text" class="hidden-text">
									We offer the best financing at the lowest rates! Starting at 1.5% APR
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon5@3x.png`" alt="van">
								</template>
								<p>No Credit? No Big Deal!</p>
								<p slot="hidden-text" class="hidden-text">
									We are able to finance nearly everyone!
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon6@3x.png`" alt="van">
								</template>
								<p>Premium software applications</p>
								<p slot="hidden-text" class="hidden-text">
									ASAP Corp is a software engineering company that creates Mobile ASAP AI Tools.
									These tools are very important they because themselves create routes to follow that
									allows the user to find jobs on
									the spot.
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon7@3x.png`" alt="van">
								</template>
								<p>Send you to where potential clients are!</p>
								<p slot="hidden-text" class="hidden-text">
									We build had crafted mobile phone stores. Clients increase their revenue because
									they always have their repair store with them.
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon8@3x.png`" alt="van">
								</template>
								<p>Never be empty handed. Always have your store with you</p>
								<p slot="hidden-text" class="hidden-text">
									Our AI system, Supel eliminates the need for agent to think of the uncertainties.
									Supel will directly instruct the agent where to drive, where to stop, what to say,
									how long to stay,
									and collect data on the success of the locations in the background to determine
									prices and location timings.
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon9@3x.png`" alt="van">
								</template>
								<p>Software that tells you where your next move should be</p>
								<p slot="hidden-text" class="hidden-text">
									Our system is an all in one Artificial Intelligence Central Resource Management
									Enterprise System. Supel will manage your schedule such as when to make follow up
									calls to clients to collect sales. Or even prioritize a upset client. Supel will do
									that and inform you of what to say for each and everything. Meanwhile, Supel is
									operating in the background reminding leads of your status. For example, if you
									collect the lead at a location you are near by, Supel will inform them that they are
									in the area. This message can be pre programmed by user. Please visit
									AnythingASAP.com for more
								</p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon10@3x.png`" alt="van">
								</template>
								<p>Our AI Central Resource Management Tool kit is all you’ll ever need to run a
									enterprise as an
									entrepreneur.</p>
								<p slot="hidden-text" class="hidden-text">
									Our system is an all in one Artifical Intelligence Central Resource Management
									Entrerprise
									System.
									Supel will manage your scheduale such as when to make follow up calls to clients
									to
									collect
									sales. Or even prioritize a upset client. Supel will do that and inform you of
									what
									to say for
									each and everything. Meanwhile, Supel is operating in the backround reminding
									leads
									of your
									status. For example, if you collect the lead at a location you are near by,
									Supel
									will inform
									them that they are in the area. This message can be pre pregrammed by user.
									Please
									visit
									AnythingASAP.com for more </p>
							</buy-van-box>
							<buy-van-box>
								<template slot="icon">
									<img :src="`${img_url}/icon11@3x.png`" alt="van">
								</template>
								<p>Artificial intellegance</p>
								<p slot="hidden-text" class="hidden-text">
									Please Visit AnythingASAP.com for more information on Supel</p>
							</buy-van-box>
						</div>
					</section>
				</div>
			</div>
			<div class="van-tree-wrapper-main">
				<section class="van-tree-wrapper">
					<div class="van-tree">
						<div class="com-box van-tree__box1">Full 110 Watt power separate from engine</div>
						<div class="com-box van-tree__box2">Ac/Heat/Vent</div>
						<div class="com-box van-tree__box3">Laser to to remove back glass</div>
						<div class="com-box van-tree__box4">Custom Billboard Wrap</div>
						<img class="van-tree" :src="`${img_url}/van-tree.png`" alt="van-tree">
						<div class="com-box van-tree__box5">RFID Inventory Tracking</div>
						<div class="com-box van-tree__box6">Inventory Cabinet space</div>
						<div class="com-box van-tree__box7">Accessories Cabinet space</div>
						<div class="com-box van-tree__box8">Mounted Monitor for POS system</div>
					</div>
				</section>
			</div>
			<section class="van-tree-phone">
				<img class="van-tree-phone" :src="`${img_url}/van-tree-phone.png`" alt="">
				<div class="com-box-phone">Full 110 Watt power separate from engine</div>
				<div class="com-box-phone">Ac/Heat/Vent</div>
				<div class="com-box-phone">Laser to to remove back glass</div>
				<div class="com-box-phone">Custom Billboard Wrap</div>
				<div class="com-box-phone">RFID Inventory Tracking</div>
				<div class="com-box-phone">Inventory Cabinet space</div>
				<div class="com-box-phone">Accessories Cabinet space</div>
				<div class="com-box-phone">Mounted Monitor for POS system</div>
			</section>
			<section class="container requirements--table">
				<div class="table-users">
					<div class="header">finance table</div>
					<table cellspacing="0">
						<tr>
							<th>Options</th>
							<th>Requirements</th>
							<th>Terms</th>
							<th>Examples</th>
						</tr>
						<tr>

							<td>
								Cash $17,000 Instant
								Rebate
							</td>
							<td>
								* * 100% of
								total funding through cash
								payment
								OR
								* * Outside credit union or bank used to
								secure funding.
							</td>
							<td>
								Client provides full payment amount either through their
								own cash or privately secured financing. Typically, with
								proper qualification, interest rates can be found a bit
								lower privately.
								Sales Price is Discounted with payment in full.
							</td>
							<td>
								<span style="font-weight:600">1.</span> John has full payment amount.
								He obtained the full amount by having the cash
								on hand. However, he also had the ability to
								take a loan out from his own bank or credit
								union.<br><br>
								<span style="font-weight:600;">2.</span> Because John had the funds without
								ASAP's help, he will pay $42,999 plus taxes
								instead of $59,999.
							</td>
						</tr>
						<tr>
							<td>
								Full
								Financing
								$8,000 Instant
								Rebate
							</td>
							<td>
								Financing through companies such as Better Fin
								* Fico As Low as 550
								* Sales over $50,000
								OR
								* Fico over 680 with $0 in sales
								OR
								* Fico Lower than 550 AND $400,000 in sales
							</td>
							<td>
								* Terms are determined on a person by person basis.
								Lending companies,such as Betterfin offer an array of
								lending opportunities.Information requested by
								lender includes, but may not be limited to;
								* Credit report
								* Sales history
								* Bank statements for their review
								* Tax returns
							</td>
							<td>
								<span style="font-weight:600;">* Higher then 670:</span><br>
								Full finance, $0 Down, 0% APR first
								year, increase to fix rate after 12 months.
								SBA or Equipment loans as low as 3% interest with
								varying terms.<br>
								<span style="font-weight:600;">Between 550 and 650:</span><br>
								Sales around $300,000 plus
								down payment.<br>
								<span style="font-weight:600;">Credit less then 550:</span><br>
								Sales over $400,000 OR large
								down payment.
								* * * Save $8,000 with instant rebate when you finance!
								* * * * These are examples. Everyone's situation may be
								different. Consult with Better fin for exact details
							</td>
						</tr>
						<tr>
							<td>
								Lease To Own
								No Credit
								Required
							</td>
							<td>
								* $10,000 to $25,000 Down payment
								-May be obtained by financing,
								IF A
								* * * See above box for finance
								minimum requirements
								* 100 / 300 motor vehicle insurance
								coverage
								* * * Varies by state regulation
							</td>
							<td>
								* If applicant can not qualify for the full amount of the loan,
								but can qualify for some funds or has some funds already
								available. These funds can be applied to our Lease to Own options.
								Lease to Own Agreement
								* $2,000 to $3,000 monthly payment for 20 to 36 months
								-Monthly payments lower each year
								* Low down payment starting at $10,000
								* Affordable buy out options after each year
							</td>
							<td>
								<span style="font-weight:600;">1.</span> John does $200,000 in sales and has a
								550 credit score. John is able to get $20,000
								financing, but this is not enough. However,
								were ASAP Solutions and we have a
								solution for you.<br><br>
								<span style="font-weight:600;">2.</span> Apply your approved financing to our
								Lease to Own option and begin mobile
								today!<br><br>
								<span style="font-weight:600;">3.</span>
								Pay Flat monthly rate with annual
								buyout option!
							</td>
						</tr>
						<div style="padding-left:30px;background: #fff;position: absolute;bottom:0;font-weight: 600;">
							Terms and conditions effective as of 2/10/20.
							Subject to change.
						</div>
					</table>
				</div>
			</section>
			<section class="have-ques">
				<div class="have-ques__title">
					Have Questions.
				</div>
				<div class="container">
					<div class="have-ques__contact-detail">
						<div class="have-ques__address">
							<div class="cont">
								<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="home"
									 role="img"
									 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
									 class="svg-inline--fa fa-home fa-w-18">
									<path d="M280.37 148.26L96 300.11V464a16 16 0 0 0 16 16l112.06-.29a16 16 0 0 0 15.92-16V368a16 16 0 0 1 16-16h64a16 16 0 0 1 16 16v95.64a16 16 0 0 0 16 16.05L464 480a16 16 0 0 0 16-16V300L295.67 148.26a12.19 12.19 0 0 0-15.3 0zM571.6 251.47L488 182.56V44.05a12 12 0 0 0-12-12h-56a12 12 0 0 0-12 12v72.61L318.47 43a48 48 0 0 0-61 0L4.34 251.47a12 12 0 0 0-1.6 16.9l25.5 31A12 12 0 0 0 45.15 301l235.22-193.74a12.19 12.19 0 0 1 15.3 0L530.9 301a12 12 0 0 0 16.9-1.6l25.5-31a12 12 0 0 0-1.7-16.93z"
										  class=""/>
								</svg>
								<span class="cus-text">
									2500 Quantum Lakes Dr #203
										Boynton Beach, FL 33426
								</span>
							</div>
							<div class="cont">
								<svg aria-hidden="true" focusable="false" data-prefix="fas"
									 data-icon="phone-volume"
									 role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"
									 class="svg-inline--fa fa-phone-volume fa-w-12">
									<path d="M97.333 506.966c-129.874-129.874-129.681-340.252 0-469.933 5.698-5.698 14.527-6.632 21.263-2.422l64.817 40.513a17.187 17.187 0 0 1 6.849 20.958l-32.408 81.021a17.188 17.188 0 0 1-17.669 10.719l-55.81-5.58c-21.051 58.261-20.612 122.471 0 179.515l55.811-5.581a17.188 17.188 0 0 1 17.669 10.719l32.408 81.022a17.188 17.188 0 0 1-6.849 20.958l-64.817 40.513a17.19 17.19 0 0 1-21.264-2.422zM247.126 95.473c11.832 20.047 11.832 45.008 0 65.055-3.95 6.693-13.108 7.959-18.718 2.581l-5.975-5.726c-3.911-3.748-4.793-9.622-2.261-14.41a32.063 32.063 0 0 0 0-29.945c-2.533-4.788-1.65-10.662 2.261-14.41l5.975-5.726c5.61-5.378 14.768-4.112 18.718 2.581zm91.787-91.187c60.14 71.604 60.092 175.882 0 247.428-4.474 5.327-12.53 5.746-17.552.933l-5.798-5.557c-4.56-4.371-4.977-11.529-.93-16.379 49.687-59.538 49.646-145.933 0-205.422-4.047-4.85-3.631-12.008.93-16.379l5.798-5.557c5.022-4.813 13.078-4.394 17.552.933zm-45.972 44.941c36.05 46.322 36.108 111.149 0 157.546-4.39 5.641-12.697 6.251-17.856 1.304l-5.818-5.579c-4.4-4.219-4.998-11.095-1.285-15.931 26.536-34.564 26.534-82.572 0-117.134-3.713-4.836-3.115-11.711 1.285-15.931l5.818-5.579c5.159-4.947 13.466-4.337 17.856 1.304z"
										  class=""/>
								</svg>
								<span class="cus-text">561-FIX-ITFL (561-349-4835)</span>
							</div>
							<div class="cont">
								<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="envelope"
									 role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
									 class="svg-inline--fa fa-envelope fa-w-16">
									<path d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"
										  class=""/>
								</svg>
								<span class="cus-text">support@phonerepairsasap.com</span>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</template>
<script>
	import axios from 'axios'
	import {column, columns} from 'shapla-columns';
	import BuyVanBox from "../../components/BuyVanBox";
	import AnimatedInput from "../../components/AnimatedInput";
	import BigButton from "../../components/BigButton";
	import MdlButton from "../../material-design-lite/button/mdlButton";

	export default {
		name: "phoneBuyVan",
		components: {
			MdlButton,
			BigButton,
			AnimatedInput,
			BuyVanBox,
			columns,
			column
		},
		data() {
			return {
				showHiddenText: false,
				showSuccessMessage: false,
				testimonials_Slides: [],
				customer_name: '',
				customer_email: '',
				phone_number: '',
				ticket_content: '',
			}
		},
		computed: {
			img_url() {
				return PhoneRepairs.plugin_url + '/assets/img';
			}
		},
		mounted() {
			if (window.CustomerSupportTickets) {
				this.categories = CustomerSupportTickets.categories;
				this.customer_name = CustomerSupportTickets.user.display_name;
				this.customer_email = CustomerSupportTickets.user.user_email;
			}
		},
		methods: {
			submitTicket() {
				let self = this;
				self.loading = true;
				axios.post(PhoneRepairs.rest_root + '/customer-support-ticket', {
					customer_name: self.customer_name,
					customer_email: self.customer_email,
					phone_number: self.phone_number,
					ticket_subject: "Message from Buy van	",
					ticket_content: self.ticket_content,
				}).then(() => {
					self.customer_name = '';
					self.customer_email = '';
					self.phone_number = '';
					self.ticket_content = '';
					this.showSuccessMessage = true;
					setTimeout(() => this.showSuccessMessage = false, 3000);
				}).catch(error => {
					console.log(error);
					//self.loading = false;
				});
			}
		},
	}
</script>

<style lang="scss">
	.repair-van-wrapper {
		.message {
			padding: 1rem;
			margin-bottom: 1rem;
		}

		.navbar:not(.navbar-fixed-top) {
			background-color: #f58730 !important;
			color: #464648 !important;
		}

		.repair-van {
			.shapla-column {
				z-index: 1;
			}

			&__header {
				margin-top: 200px;
				@media screen and (max-width: 768px) {
					padding: 0 2rem;
					margin-top: 100px;
					margin-bottom: 350px;
					img {
						position: relative;
						margin-top: 280px;
						right: 20px;
					}
				}
				@media screen and (max-width: 500px) {
					margin-top: 100px;
					margin-bottom: 220px;
				}
			}

			&__title {
				font-size: 26px;
				font-weight: 900;
				color: rgb(254, 135, 0);
				//text-transform: uppercase;
				@media screen and (max-width: 768px) {
					text-align: center !important;
				}
			}

			&__content {
				font-size: 22px;
				font-weight: 100;
				color: #646464;
				text-align: left;
				max-width: 500px;
				line-height: 1.2em;
				margin-top: 15px;
				@media screen and (max-width: 768px) {
					text-align: center !important;
					max-width: 100%;
				}

			}

			&__device-count {
				font-size: 18px;
				font-weight: 100;
				color: #646464;
				text-align: left;
				line-height: 1.2em;
				margin-top: 15px;
			}

			&__sub-content {
				font-size: 18px;
				font-weight: 100;
				color: #646464;
				text-align: left;
				//line-height: 1.2em;
				margin-bottom: 20px;
			}

			$size: 400px;

			.shape {
				z-index: 1;
				top: 0;
				right: 0;
				position: absolute;
				background: rgb(253, 135, 21);
				display: inline-block;
				height: $size * 2;
				width: $size;
				border-bottom-left-radius: $size * 2;
				border-top-left-radius: $size * 2;
				@media screen and (max-width: 768px) {
					background: transparent;
				}
			}

			&__working {
				margin-top: 100px;
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				text-align: center;

				h4 {
					font-size: 24px;
					color: #646464;
				}

				h6 {
					font-size: 24px;
					color: #646464;
				}

				@media screen and (max-width: 768px) {
					//padding: 0 2rem;
				}
			}

			&__working-title {
				font-size: 24px;
				font-weight: 600;
				color: #646464;
			}

			&__working-content {
				margin-top: 15px;
				font-size: 20px;
				color: #646464;
			}

			.repair-van-box {
				&__box {
					min-height: 265px;
					max-width: 250px;
					padding: 20px;
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
					text-align: center;
					background: #fff;
					box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
					@media screen and (max-width: 990px) {
						padding: 5px;
					}
					@media screen and (max-width: 769px) {
						padding: 20px;
					}
				}

				&__box-title {
					margin-top: 15px;
					font-size: 20px;
					font-weight: 900;
					color: rgb(100, 100, 100);

				}

				&__box-content {
					margin-top: 10px;
					font-size: 12px;
					color: rgb(100, 100, 100);
				}

				&__box-wrapper {
					margin-top: 50px;
				}
			}

			@media screen and (min-width: 1100px) {
				$gap: 25px;
				.shapla-columns.has-inner-custom-padding {
					margin-left: -#{$gap};
					margin-right: -#{$gap};
					margin-top: -#{$gap};

					&:last-child {
						margin-bottom: -#{$gap};
					}

					> .shapla-column {
						padding: #{$gap};
					}
				}
			}

			.repairs-van-background {
				background: rgb(243, 249, 255);
				position: relative;
				box-sizing: border-box;
				@media screen and (max-width: 769px) {
					// padding: 0 2rem;
				}
				@media screen and (max-width: 500px) {
				}
				@media screen and (max-width: 350px) {
				}
			}
		}

		.van-software {
			margin-top: 100px;
			@media screen and (max-width: 768px) {
				//padding: 0 2rem;
			}

			&__box-wrapper {
				display: flex;
				justify-content: space-between;
				align-items: center;
				flex-wrap: wrap;
				@media screen and (max-width: 1087px) {
					justify-content: center;
				}
			}

			&__img {
				max-width: 400px;
			}

			&__title {
				line-height: 1.3em;
				font-size: 26px;
				font-weight: 700;
				color: rgb(100, 100, 100);
				@media screen and (max-width: 769px) {
					text-align: center;
				}
			}

			&__content {
				margin-top: 20px;
				font-size: 20px;
				color: #646464;
				max-width: 500px;
				@media screen and (max-width: 769px) {
					text-align: center;
					font-size: 18px;
				}
			}
		}

		.van-wrapper {
			display: grid;
			grid-gap: 20px;
			justify-items: center;
			margin-top: 80px;
			padding-bottom: 50px;
			@media screen and (max-width: 768px) {
				//padding: 0 2rem;
			}

			&__image-section {
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				grid-gap: 0px;
				align-items: center;
			}

			.hidden-text {
				font-weight: 600;
				font-size: 14px;
				text-align: justify;
			}

			&__text {
				font-size: 26px;
				padding-top: 0;
				font-weight: 400;
				line-height: 2em;

				ul {
					list-style: none;
				}

				li::before {
					display: inline-block;
					content: '';
					border-radius: 0.375rem;
					height: 0.75rem;
					width: 0.75rem;
					margin-right: 0.5rem;
					background-color: rgb(168, 0, 0);
				}
			}


			&__boxes {
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				grid-gap: 20px;
				@media screen and (max-width: 1024px) {
					grid-template-columns: repeat(1, 1fr);
				}
			}

			&__box-wrapper {
				margin: 2rem;
			}

			&__box {
				display: flex;
				justify-content: flex-start;
				align-items: center;
				border: 1px solid rgb(255, 109, 0);
				padding: 15px 5px;
				margin: 5px;
				position: relative;
				background: #fff;

				.van-wrapper__image-box {
					background-image: linear-gradient(to bottom right, rgb(255, 126, 0), rgb(255, 102, 43));
				}

				&:hover {
					box-shadow: 0 25px 50px -12px gray;
				}
			}

			&__text-button {
				background: white;
				color: black;
				font-size: 12px;
				border: 1px solid rgb(255, 109, 0);
				padding: 5px;
				position: absolute;
				right: 4px;
				bottom: 4px;

				&:hover, &:focus {
					background: white;
					color: black;
				}
			}

			&__image-box {
				padding: 20px;
				border-radius: 50%;
				width: 75px;
				height: 75px;
				min-width: 75px;
				display: flex;
				align-items: center;
				justify-content: center;

				img {
					max-width: 48px;
					height: auto;
				}
			}

			&__text-box {
				font-size: 16px;
				margin-left: 10px;

				p {
					margin: 0;
					line-height: 1.4em;
					@media screen and (max-width: 500px) {
						margin: 20px 0 20px 0;
					}
				}

				@media screen and (max-width: 374px) {
					font-size: 14px;
				}
			}
		}

		.van-tree-wrapper-main {
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.van-tree-wrapper {
			width: 768px;
			margin-top: 100px;
			margin-bottom: 100px;
		}

		.van-tree {
			position: relative;

			img {
				padding: 8rem;
			}

			.com-box {
				display: flex;
				justify-content: center;
				align-items: center;
				position: absolute;
				padding: 12px;
				border-radius: 12px;
				text-align: center;
				box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
				font-size: 10px;
				height: 45px;
				width: 135px;
				background: rgb(243, 249, 255);
			}

			&__box1 {
				top: 97px;
				left: 125px;
			}

			&__box2 {
				top: 35px;
				left: 282px;
			}

			&__box3 {
				top: 103px;
				right: 220px;
			}

			&__box4 {
				top: 34px;
				right: 70px;
			}

			&__box5 {
				bottom: 35px;
				left: 21px;
			}

			&__box6 {
				bottom: 68px;
				left: 197px;
			}

			&__box7 {
				bottom: 53px;
				right: 264px;
			}

			&__box8 {
				bottom: 69px;
				right: 102px;
			}
		}

		@media screen and (max-width: 768px) {
			.van-tree {
				display: none;
			}
		}

		.van-tree-phone {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;

			img {
				max-width: 400px;
				padding: 0 2rem;
			}

			.com-box-phone {
				margin: 15px;
				display: flex;
				justify-content: center;
				align-items: center;
				padding: 12px;
				border-radius: 12px;
				height: 60px;
				text-align: center;
				box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
				width: 160px;
				font-size: 12px;
				background: rgb(243, 249, 255);
			}
		}

		@media screen and (min-width: 769px) {
			.van-tree-phone {
				display: none;
			}
		}

		.have-ques {
			&__title {
				margin-top: 100px;
				text-align: center;
				line-height: 1.3em;
				font-size: 26px;
				font-weight: 700;
				color: rgb(100, 100, 100);
				margin-bottom: 100px;
			}

			&__contact-section {
				margin-top: 100px;
				background-size: cover;
				background-position: center;
				text-align: center;
				position: relative;
				overflow: hidden;

				h1 {
					text-align: center;
				}
			}

			&__contact-section:before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				z-index: -1;
				opacity: 0.5;
			}

			&__contact-section-wrapper {
				padding: 4rem;
			}

			&__contact-box {
				margin-top: 0px;
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			&__contact-detail {
				display: flex;
				justify-content: center;

				.cont {
					width: 100%;
					margin-bottom: 1.5em;
					display: flex;
					justify-content: center;
					align-items: center;

					span {
						font-size: 18px;
					}

					svg {
						display: inline-block;
						float: left;
						width: 35px;
						fill: rgb(251, 135, 0) !important;
					}

					.cus-text {
						width: 60%;
						display: inline-block;
						float: left;
						margin-left: 15px;
						line-height: 1em;
						font-weight: 100;
					}
				}
			}

			&__address {
				display: flex;
				flex-direction: column;
				justify-content: flex-start;
				align-items: flex-start;
				text-align: start;
			}

			.big-button-wrapper .big-button {
				border-radius: 5px;
			}

			&__form {
				max-width: 700px;

				.mdl-textfield {
					border: none;
					background: rgb(234, 238, 243);

					.mdl-textfield__label {
						font-size: 16px;
					}
				}
			}
		}

		@media screen and (max-width: 768px) {
			.have-ques {
				padding: 0 2rem;
			}
		}
	}

	.s {
		color: #ffffff;
		border: none;
		background-color: #f9a73b;
		background-image: -webkit-linear-gradient(left, #f9a73b 0%, #f58730 50%, #f9a73b 100%);
		background-image: linear-gradient(to right, #f9a73b 0%, #f58730 50%, #f9a73b 100%);
		transition: all .2s ease-in-out;
		background-size: 200% 100%;
		height: 50px;

		svg {
			width: 15px;
			fill: #fff;
			position: relative;
			top: 3px;
			right: 3px;
		}
	}

	.buy--van--form {
		display: flex;
		justify-content: center;
	}

	.requirements--table {
		$baseColor: #398B93;
		$borderRadius: 10px;
		$imageBig: 100px;
		$imageSmall: 60px;
		$padding: 10px;

		.header {
			background-color: #f58730;
			color: white;
			font-size: 1.5em;
			padding: 1rem;
			text-align: center;
			text-transform: uppercase;
		}

		.table-users {
			border: 1px solid #f58730;
			border-radius: $borderRadius;
			box-shadow: 3px 3px 0 rgba(0, 0, 0, 0.1);
			overflow: hidden;
		}

		@media screen and (max-width: 768px) {
			.table-users {
				margin-top: 50px;
			}
		}

		table {
			position: relative;
			width: 100%;

			td, th {
				color: #333;
				padding: $padding;
			}

			td {
				padding-top: 20px;
				vertical-align: top;
				text-align: left;
				max-width: 200px;

				&:first-child {
					font-weight: 600;
					font-size: 18px;
					max-width: 100px;
				}
			}

			th {
				background-color: #f7fafc;
				font-weight: 600;
				font-size: 18px;
			}

			tr {
				font-size: 15px;

				&:nth-child(2n) {
					background-color: white;
				}

				&:nth-child(2n+1) {
					background-color: #f7fafc;
				}
			}
		}

		@media only screen and (max-width: 760px),
		(min-device-width: 768px) and (max-device-width: 1024px) {

			table {
				width: 100%;
			}

			/* Force table to not be like tables anymore */
			table, thead, tbody, th, td, tr {
				display: block;
			}

			/* Hide table headers (but not display: none;, for accessibility) */
			thead tr {
				position: absolute;
				top: -9999px;
				left: -9999px;
			}

			tr {
				border: 1px solid #ccc;
			}

			td {
				/* Behave  like a "row" */
				border: none;
				border-bottom: 1px solid #eee;
				position: relative;
				padding-left: 50%;
			}

			td:before {
				/* Now like a table header */
				position: absolute;
				/* Top/left values mimic padding */
				top: 6px;
				left: 6px;
				width: 45%;
				padding-right: 10px;
				white-space: nowrap;
				/* Label the data */
				content: attr(data-column);

				color: #000;
				font-weight: bold;
			}

		}
	}
</style>
