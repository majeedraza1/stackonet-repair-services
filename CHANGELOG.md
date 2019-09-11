#### 1.1.18 - September 11, 2019
* Add - Add tracking users dashboard tab page.
* Add - Add tracking users edit option.
* Add - Add tracking users add new option.
* Add - Add tracking users create option for not recorded users.

#### 1.1.17 - September 07, 2019
* Add - Add van location field for choosing base address on map.
* Add - Add actual and snap to road api view on single object tracker.
* Tweak - Update Single object tracker to reduce snap to road api.
* Add - Add distance and duration option on logs data.
* Add - Add addresses where user stay more than 4 seconds.
* Tweak - Update snapToRoad API function to reduce request quantity using cache api.
* Tweak - Update Snap to Road api functionality.
* Tweak - Update Object tracker background functionality to get more perfect map polyline.
* Add - Add new single object timeline UI.
* Add - Add timeline functionality on tracker.
* Tweak - Update street calculation on timeline.
* Add - Add Google Timeline Movement functionality.
* Add - Add Date choosing functionality on Google Timeline page.
* Fix - Fix additional address and customer issue note is not saving on checkout.
* Tweak - Update timeline scripts to include all places.
* Tweak - Update timeline script to merge multiple moving points into one.
* Tweak - Update timeline script to merge recursive street address and places.
* Add - Add functionality to save Google Place data.
* Tweak - Update functionality to check if place ID exists in database.
* Tweak - Update tracker frontend scrips to save 12 (in a minute for one object) Google Geocoding REST API request.
* Add - Add trackable object default avatar image. …
* Tweak - Update tracker script to re-draw polyline only if data changed.
* Tweak - Update tracker timeline and single object tracker to update map if only data changed.
* Fix - Fix Map Page choose Van option is not working.

#### 1.1.16 - August 09, 2019
* Add - Add option to view previous date activities for individual object.
* Add - Add option to zoom object on map when click on object card.
* Add - Add REST endpoint to create, update, delete trackable object.
* Add - Add button to toggle actual/snapped view for polyline.
* Add - Add scroll option on tracker van list.
* Add - Add 'Phone Repairs ASAP Canvas' template.
* Fix - Fix tracker status text is being hidden.
* Add - Add chunk functionality if logs items is more than 100 as Google support only 100 points per request.
* Add - Add REST endpoint to view logs and delete log by log id.
* Add - Add transient for 1 minute for snapped points data to reduce google api request.
* Tweak - Remove live Firebase sync with backend option for each 5 seconds.
* Add - Add functionality to get log data by time range of a day.
* Add - Add color option for each time frame.
* Add - Add timeline option on single object.
* Add - Add local timezone for trackable object timeline.
* Tweak - Disabled optimized view as it required some more tweak.

#### 1.1.15 - August 02, 2019
* Fix - Fix support ticket dropdown issue.
* Add - Add address re-arrange functionality based on google map provided order for edit/view map.
* Add - Add option to sort selected places based on map route.
* Add - Add tracker menu on admin dashboard.
* Add - Add live asset detection on google map.
* Add - Add functionality to latLan on database for later use.
* Add - Add moving and idle status on map card.
* Add - Add individual object activity view page.
* Add - Add cron job to sync data from Firebase evey minute.
* Tweak - Make layout header visible on small screen.
* Tweak - Update single tracker page polyline with live update.
* Fix - Fix support ticket city showing number.
* Fix - Fix map selected item become unselect as it reload after routing.
* Fix - Fix idle/moving status is not working perfectly.

#### 1.1.14 - July 24, 2019
* Add - Add Type and Search the filters in support.
* Tweak - Update option to show name for leads and orders.
* Feature - Add option to add support from checkout analysis.
* Tweak - Add default category for support ticket from both checkout analysis and map.
* Feature - Add Google Map waypoints optimization feature for getting better route.

#### 1.1.13 - July 19, 2019
* Add - Add option to add note from support ticket list page.
* Dev - Add meta capability functionality for support ticket.
* Dev - Update dashboard user permission check.
* Dev - Update support ticket permission check functionality.
* Add - Add custom payment link generator on order support ticket.
* Fix - Fix support ticket is conflicting with meta table id.
* Add - Add message sending capability for agent.
* Add - Add note circle indicator on support ticket list table.
* Add - Add Map menu on dashboard with text search, marker, address list functionality.
* Add - Add agent filter option on support agent list page.
* Add - Add call action on support list table.
* Add - Add "Quick View" action on support list table.
* Fix - Fix checkbox is not showing on Dashboard SMS list for mobile view.
* Fix - Fix date filter is not working on SMS list page.
* Add - Add base address, radius option, map route, date and time field.
* Fix - Fix error on filter and also some improvement in design.
* Add - Add live distance option on map.
* Add - Update address-box component for updating estimated time.
* Add - Complete ETA and ETD functionality.
* Add - Add map list page.
* Add - Add map view functionality.
* Add - Add map edit functionality.
* Add - Add map direction functionality.
* Add - Add map to support ticket generation functionality.
* Add - Add functionality to change support agent from both support ticket and map.
* Add - Add option to view/edit map from support ticket.
* Add - Update support ticket content for map.
* Add - Add option to select place from map.
* Add - Add confirm option for address select on map.
* Add - Update address box design.
* Fix - Fix Graph data not showing perfectly for current month.

#### 1.1.12 - July 01, 2019
* Add - Add Square Payment integration for payment processing.
* Add - Add settings fields for square payment.
* Add - Add payment status widget on single support ticket page for order support ticket.
* Add - Add order discount functionality on support ticket.
* Add - Add Payment link send functionality over email and SMS from support ticket.
* Fix - Fix discount is calculating wrongly for order.
* Fix - Fix single support ticket Map zoom not working properly.
* Fix - Fix graph data not working for previous month.
* Tweak - Update payment form style.

#### 1.1.11 - Jun 26, 2019
* Add - Add new dashboard menu "SMS"
* Add - Add functionality to filter data from order, lead, support ticket.
* Add - Add functionality to send bulk SMS to selected list.
* Add - Add functionality to add SMS template and choose these templates when sending SMS.
* Add - Add pre and next navigation on support ticket detail page.
* Add - Make support ticket phone link as tel so it can use for giving call.
* Add - Add attachment upload functionality on support ticket.
* Add - Add calendar page on dashboard for order and lead (appointment).
* Add - Add Graph on dashboard page.
* Add - Add Google map with marker for report (order and lead) address position.
* Add - Add google map on single support ticket.
* Add - Add remember functionality on dashboard login form.
* Add - Changed "Appointment" to "Lead" on dashboard.
* Add - Add functionality to create order from lead support ticket.
* Add - Fix issues are not working on pricing page.
* Add - Add support ticket option to change order status with note (both support ticket and order).
* Dev - Update vue components dependencies.

#### 1.1.10 - Jun 15, 2019
* Add - Add login page on dashboard.
* Add - Make support ticket list table as default landing page.
* Add - Add user information step on checkout analysis report.
* Add - Fix dashboard responsive issue for laptop.
* Add - Add new Carrier Store form based on survey form and its admin list table.
* Add - Add location autocomplete feature on checkout user address step.
* Add - Change checkout/re-schedule time to 9am-12pm, 12pm-3pm, 3pm-6pm, 6pm-9pm.
* Add - Add loading status on dashboard and update vue directory structure.
* Add - Update support ticket agents sms functionality with ticket view url autocomplete feature.
* Add - Add functionality to add support ticket from carrier store.

#### 1.1.9 - Jun 11, 2019
* Add - Add table view on checkout analysis page.
* Add - Add pagination on checkout analysis page.
* Add - Add phone field on zip code not found checkout page.
* Add - Add new dashboard with support, survey, appointment and checkout analysis tab.
* Add - Add user info step before device step with user first name, last name and phone number.

#### 1.1.8 - Jun 8, 2019
* Add - Add new admin number on twilio.
* Fix - Fix Dropbox error even it image upload to media but fail to upload dropbox.
* Add - Add functionality to add existing appointment to support ticket.
* Add - Add phone number and city field on support ticket.
* Add - Add city filtering option on support ticket list table.
* Add - Add phone number and city field on support ticket.
* Add - Add city filtering option on support ticket list table.
* Add - Update order to support ticket functionality to add phone and city value.
* Add - Update appointment to support ticket functionality to add phone and city value.
* Add - Add phone field on support ticket CSV export.
* Add - Add search functionality both on admin and frontend support ticket.
* Add - Add categories dropdown search both on admin and frontend support ticket.
* Add - Add send SMS functionality form support ticket.
* Add - Add checkout analysis functionality.
* Add - Add functionality for getting city and zip code from IP address.
* Add - Add dropdown info on every step.

#### 1.1.7 - Jun 1, 2019
* Feature - Add frontend support ticket functionality with list table, new ticket and view option.
* Tweak - Add functionality to make device sortable.
* Add - Add functionality to add support ticket from order.
* Add - Add functionality to add support ticket from contact form.
* Add - Add mdl data table component.

#### 1.1.6 - May 16, 2019
* Feature - Add spot appointment frontend and admin functionality.
* Feature - Add eSignature functionality on cart.
* Add - Add terms and conditions page with admin settings.
* Add - Add phone and email field on survey page.
* Tweak - Make time selection after the current time.
* Tweak - Remove strict address validation from checkout.
* Fix - Fix subtotal decimal depth more than two.

#### 1.1.5 - May 10, 2019
* Add - Add become a technician frontend and admin functionality with dropbox resume upload.

#### 1.1.4 - May 8, 2019
* Fix - Fix survey page active item not focusing on mobile device.
* Add - Add new field tips amount for survey.
* Tweak - Make survey page responsive.

#### 1.1.3 - May 7, 2019
* Feature - Add image upload functionality on survey page.
* Feature - Add functionality to add upload file to dropbox.
* Add - Add new survey table columns 'brand", 'gadget', 'model', 'images_ids'

#### 1.1.2 - May 3, 2019
* Fix - Fix re-schedule page not working.
* Fix - Disabled geolocation feature on zip and user address step.

#### 1.1.1 - May 2, 2019
* Add - Add auto complete zip from browser zip for phone repair.
* Add - Add "Front Glass" issue on pricing page.
* Add - Add discount and tax calculation on pricing page.
* Add - Add 15% discount functionality when user choosing multiple issue.

#### 1.1.0 - Apr 30, 2019
* Feature - Add pricing calculation page to pre estimate phone repair price.
* Feature - Add reschedule functionality for admin with sending SMS and Email.
* Feature - Add reminder message functionality.
* Feature - Add admin menu "Rent a Center" and my account menu Phones
* Feature - Add Track Status my account endpoint.
* Feature - Add survey form with GPS auto detection & Survey admin menu with list table.
* Add - Add agent role for survey data management.
* Add - Set cart device icon from device image.
* Add - Add manager role and manager register form.
* Add - Integrate with Twilio API to send SMS.
* Add - Add my account store address menu.
* Add - Add CSV export option for rent a center phones list page items.
* Add - Add option to add tax on cart page.
* Tweak - Add functionality to group device.
* Tweak - Update new order mail both for admin and customer.
* Tweak - Add help number on every steps.
* Tweak - Add Firebase Dynamic Links functionality for url shorten.
* Fix - Fix Cart amount is being zero on thank you page.
* Fix - Fix invoice amount is showing zero in email template.

#### 1.0.0 - Mar 4, 2019
* Initial release
