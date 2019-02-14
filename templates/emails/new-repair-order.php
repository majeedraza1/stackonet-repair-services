<?php defined( 'ABSPATH' ) || exit; ?>
<table width="100%" cellpadding="12" cellspacing="0" border="0">
	<tr>
		<td>
			<div style="overflow: hidden;">
				<div
					style="margin-top:0!important;margin-bottom:0!important;margin-right:0!important;margin-left:0!important;padding-top:0!important;padding-bottom:0!important;padding-right:0!important;padding-left:0!important;background-color:#ffffff">
					<div style="text-align:center;width:100%;table-layout:fixed">
						<div style="max-width:600px;margin-top:0;margin-bottom:0;margin-right:auto;margin-left:auto">
							<table width="100%" align="center"
							       style="border-spacing:0!important;font-family:sans-serif;color:#333333;margin:0 auto;width:100%;max-width:600px">
								<tbody>
								<tr>
									<td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0">
										<table width="100%" cellspacing="0" cellpadding="0"
										       style="border-spacing:0;font-family:sans-serif;width:100%;max-width:600px;margin:0 auto">
											<tbody>
											<tr>
												<td align="center" width="600" height="48"
												    style="background:#0065d1;padding-top:12px;padding-bottom:12px">
													<a href="<?php echo home_url(); ?>" target="_blank">
														<?php
														if ( $img = get_option( 'woocommerce_email_header_image' ) ) {
															echo '<img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" />';
														}
														?>
													</a>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td align="center" style="padding-top:25px;padding-bottom:15px">
										<img
											src="<?php echo STACKONET_REPAIR_SERVICES_ASSETS . '/img/confirmed_sign.png'; ?>"
											height="80" width="80" style="border-width:0" alt="checkArrow.png">
									</td>
								</tr>
								<tr>
									<td style="padding-top:20px;padding-bottom:20px;padding-right:0;padding-left:0">
										<table width="100%"
										       style="border-spacing:0!important;font-family:'Helvetica'">
											<tbody>
											<tr>
												<td align="center" style="padding-bottom:20px">
<span style="font-size:18px;font-family:'Helvetica';font-weight:500">
Thank you for requesting an appointment with <?php echo get_option( 'blogname' ); ?>!
</span>
												</td>
											</tr>
											<tr>
												<td align="center">
<span style="color:#9b9b9b;font-size:15px;font-weight:300;line-height:23px;text-align:center;padding-bottom:20px">
We are currently locating the best technicians in your area and
will be in touch shortly with an expected arrival time.
</span>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td>
										<table style="border-top:1px solid #dedede" cellpadding="20"
										       cellspacing="20"
										       align="center">
											<tbody>
											<tr>
												<td width="570" bgcolor="#ffffff" align="center">
													<img
														style="outline:none;text-decoration:none;clear:both;display:block;border:0;height:auto;line-height:100%;margin:0 auto;float:none;width:75px;max-width:75px;height:auto"
														border="0"
														src="<?php echo esc_url( $device['image']['src'] ) ?>"
														alt="Image" title="Image" width="52">
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td style="border-bottom:1px solid #dedede;padding-bottom:20px;padding-top:20px">
										<table width="100%"
										       style="border-spacing:0!important;font-family:sans-serif">
											<tbody>
											<tr style="color:#000000">
												<td align="left" style="width:30%;max-width:170px">
<span style="font-size:18px;font-family:'Helvetica';font-weight:lighter">
Device
</span>
												</td>
												<td align="left">
													<span
														style="font-size:18px;font-family:'Helvetica';font-weight:lighter"><b><?php echo esc_html( $title ); ?></b></span>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td style="border-bottom:1px solid #dedede;padding-bottom:20px;padding-top:20px">
										<table width="100%"
										       style="border-spacing:0!important;font-family:sans-serif">
											<tbody>
											<tr style="color:#000000">
												<td valign="top" align="left" style="width:30%;max-width:170px">
<span style="font-size:18px;font-family:'Helvetica';font-weight:lighter">
Cost
</span>
												</td>
												<td align="left" style="color:#000000">
													<p style="font-size:18px;font-family:'Helvetica';margin:0;margin-bottom:10px;font-weight:lighter">
														<b><?php echo $order_total; ?></b>
														<span style="color:#9b9b9b;font-size:10pt"></span>
													</p>
													<p style="font-size:12pt;font-family:'Helvetica';color:#9b9b9b;line-height:20px;margin:0;font-weight:lighter">
														Parts and service come with a 90-day guarantee. Your credit
														card will be charged when the appointment is complete.
													</p>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td style="border-bottom:1px solid #dedede;padding-bottom:20px;padding-top:20px">
										<table width="100%"
										       style="border-spacing:0!important;font-family:sans-serif">
											<tbody>
											<tr style="color:#000000">
												<td valign="top" align="left" style="width:30%;max-width:170px">
<span style="font-size:18px;font-family:'Helvetica';font-weight:lighter">
Requested time
</span>
												</td>
												<td align="left">
													<p style="font-size:18px;font-family:'Helvetica';margin:0;margin-bottom:10px;font-weight:lighter">
														<b><?php echo esc_html( $requested_time ); ?></b>
													</p>
													<p style="font-size:12pt;font-family:'Helvetica';color:#9b9b9b;line-height:20px;margin:0;font-weight:lighter">
														We will be in touch soon to confirm the arrival time
													</p>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td style="border-bottom:1px solid #dedede;padding-bottom:20px;padding-top:20px">
										<table width="100%"
										       style="border-spacing:0!important;font-family:sans-serif">
											<tr style="color:#000000">
												<td valign="top" align="left" style="width:30%;max-width:170px">
													<span
														style="font-size:18px;font-family:'Helvetica';font-weight:lighter">Your info</span>
												</td>
												<td align="left">
													<p style="font-size:18px;color:#000000;font-family:'Helvetica';margin:0;margin-bottom:10px;font-weight:lighter">
														<b><?php echo esc_html( $name ); ?></b>
													</p>
													<p style="font-size:12pt;color:#000000;font-family:'Helvetica';margin:0;margin-bottom:10px;font-weight:lighter">
														<?php echo $order->get_formatted_billing_address(); ?>
													</p>
													<p style="font-size:12pt;color:#000000;font-family:'Helvetica';margin:0;margin-bottom:10px;font-weight:lighter">
														<?php echo $order->get_billing_email(); ?>
													</p>
													<p style="font-size:12pt;color:#000000;font-family:'Helvetica';margin:0;margin-bottom:10px;font-weight:lighter">
														<?php echo $order->get_billing_phone(); ?>
													</p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr style="height:20px">
								</tr>
								<tr>
									<td style="padding-top:50px;padding-bottom:50px;padding-right:0;padding-left:0;border-bottom:1px solid #dedede">
										<table>
											<tbody>
											<tr>
												<td align="center" style="padding-bottom:10px">
<span style="font-size:18px;font-family:'Helvetica';font-weight:500">
What happens next?
</span>
												</td>
											</tr>
											<tr>
												<td align="left" style="padding-bottom:10px">
<span style="padding:0;color:#000000;font-size:15px;font-weight:300;line-height:23px;text-align:left">
- <?php echo get_option( 'blogname' ); ?> will match you with the best technician in your area
</span>
												</td>
											</tr>
											<tr>
												<td align="left" style="padding-bottom:10px">
<span style="padding:0;color:#000000;font-size:15px;font-weight:300;line-height:23px;text-align:left">
- We will contact you directly with any scheduling updates
</span>
												</td>
											</tr>
											<tr>
												<td align="left" style="padding-bottom:10px">
<span style="padding:0;color:#000000;font-size:15px;font-weight:300;line-height:23px;text-align:left">
- Feel free to call, text or email us with any questions
</span>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td align="center" style="padding-bottom:20px;padding-top:20px">
										<table width="100%"
										       style="border-spacing:0!important;font-family:sans-serif;color:#333333">
											<tbody>
											<tr>
												<td align="center">
													<span style="font-size:18px;color:#000000">Any questions or concerns? </span>
												</td>
											</tr>
											<tr>
												<td align="center">
													<p style="font-size:24px;margin-top:10px;margin-bottom:10px;font-family:'Helvetica';color:#0161c7">
														(855) 256-3709
													</p>
												</td>
											</tr>
											<tr>
												<td align="center">
													<table
														style="border-spacing:0!important;font-family:sans-serif;color:#333333">
														<tbody>
														<tr>
															<td style="font-size:12pt">
																<span><a href="mailto:support@puls.com"
																         style="color:#0161c7;text-decoration:none"
																         target="_blank">Email us</a></span>
															</td>
														</tr>
														</tbody>
													</table>
												</td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td>
										<table cellpadding="0" cellspacing="0" align="center" bgcolor="#F6F6F6"
										       width="100%"
										       style="border-spacing:0;font-family:sans-serif;color:#333333">
											<tr>
												<td width="100%" bgcolor="#F6F6F6" align="center">
													<table
														style="border-spacing:0;font-family:sans-serif;color:#333333">
														<tbody>
														<tr>
															<td align="center">
																<span style="color:#9b9b9b;font-size:14px">© 2019 Puls Technologies Inc | All Rights Reserved.</span>
															</td>
														</tr>
														<tr>
															<td align="center">
																<span
																	style="color:#9b9b9b;font-size:14px;padding-top:5px">
																	444 Spear Street, San Francisco, CA, United States Ste: 210
																</span>
																<br>
															</td>
														</tr>
														<tr>
															<td align="center"
															    style="padding-top:10px;padding-bottom:20px">
<span style="color:#9b9b9b;font-size:14px">
<a href="#" style="color:#9b9b9b!important" target="_blank">Privacy Policy</a> |
	<a href="#" style="color:#9b9b9b!important" target="_blank">Terms and&nbsp;Conditions</a>
</span>
															</td>
														</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</td>
	</tr>
</table>
