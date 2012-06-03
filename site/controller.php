<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
jimport('joomla.error.log');

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.DS.'models'.DS.'appointments.php');
JLoader::register('TableSalonBook', JPATH_COMPONENT_ADMINISTRATOR.DS.'tables'.DS.'salonbook.php');
JLoader::register('SalonBookModelEmail',  JPATH_COMPONENT_SITE.DS.'models'.DS.'email.php');

require_once (JPATH_SITE.DS.'includes'.DS.'Zend'.DS.'Loader.php');
require_once 'models/email.php';

/** TEST URL
 * http://localhost/index.php?option=com_salonbook&view=payment&task=showpaymentresult&tx=11364803SM584351M&st=Pending&amt=16.95&cc=CAD&cm=&item_number=
*/

/** response from Internet Secure
 * http://localhost/index.php?option=com_salonbook&view=payment&task=internetsecureconfirmation
*/

/**
 * reminder emails
 * http://localhost/index.php?option=com_salonbook&view=payment&task=sendReminderEmails
 * 
 * cron job
 * curl -F "option=com_salonbook" -F "view=payment" -F "task=sendReminderEmails" http://celebrityunisexsalon.com/index.php
 */

/**
 * reminder emails
 * http://localhost/index.php?option=com_salonbook&view=payment&task=sendReminderEmails
 */

/**
 * Salon Book Component Controller
 */
class SalonBookController extends JController
{
	protected $appointmentDetails;
	
	/**
	 * Searches for, and removes a value from a single-dimensional array
	 * 
	 * @param var $needle
	 * @param array $haystack
	 */
	function removeItemFromArray($needle, $haystack)
	{
		$key = array_search($needle, $haystack, true);
		if ( $key === false )
		{
			return $haystack;
		}
		else
		{
			unset($haystack[$key]);
			return $haystack;
		}
	}
	
	/**
	 * Called after the User has any of the Appointment details
	 * 
	 * After each user selection, add the item to an array being built in the Session object
	 * Only bind this object to the database table when we are ready to store it [ $model->store() ] 
	 */
	function updateAppointment()
	{
		// error_log("inside updateAppointment()\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");

  		$model = new SalonBookModelAppointments();
		
		$appointment_id=JRequest::getVar('id', '0');
		
		$session = JFactory::getSession();
		$appointmentData =& $session->get('appointmentData', array(), 'SalonBook');
		
		if ( $appointment_id == 0 )
		{
			// we shouldn't be fetching data from the database AGAIN, only from the object we are passing around!!
			// error_log("LOOKUP or CREATE an appointment object\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			// error_log("passed-in ID:" . $appointment_id . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			if ( $appointmentData['user'] == 0 )			
			{
				// set the user
				$user =& JFactory::getUser();
				$appointmentData['user'] = $user->id;
				// error_log("This is the NEW object\n" . var_export($appointmentData, true) ."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
		}
		else
		{
			// check to determine if we have an empty shell of an appointment object
			// if so, fill it out with details from the model
			if ( $appointmentData['user'] == 0 )
			{
				$savedData = $model->getAppointmentDetailsForID($appointment_id);
				$appointmentData = $savedData[0];
				// error_log("appointmentData FROM the model  \n[ " . var_export($appointmentData, true) . "]\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
			else 
			{
				// error_log("appointmentData FROM PREVIOUS screen  \n[ " . var_export($appointmentData, true) . "]\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
		}
		
		// get the input from the last screen
		// there may be an array of fields being sent
		$fieldName = JRequest::getVar('fieldName');
		$fieldCount = count($fieldName);
		
		// the ignore list consists off all fields, except the one being added
		if ( $fieldCount == 1)
		{
			$fieldName = JRequest::getVar('fieldName');
			$fieldValue = JRequest::getVar('fieldValue');
			
			// update with the recently selected values
			// error_log("SINGLE: setting " . $fieldName . " to " . $fieldValue . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");

			$appointmentData[$fieldName] = $fieldValue;
		}
		else 
		{
			// error_log("field count = " . $fieldCount . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			// it's an array
			$fieldNameArray = JRequest::getVar('fieldName');
			$fieldValueArray = JRequest::getVar('fieldValue');
			$newDataArray = array();
			
			for ($x=0; $x < $fieldCount; $x++)
			{				
				// error_log("MULTIPLE: setting " . $fieldNameArray[$x] . " to " . $fieldValueArray[$x] . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				$appointmentData[$fieldNameArray[$x]] = $fieldValueArray[$x];
			}
		}
		
		// get the rest of the passed in vars
		$nextViewName = JRequest::getVar('view');
		$nextViewType = JRequest::getVar('nextViewType', 'html');
		$nextViewModel = JRequest::getVar('nextViewModel');
		
		$view = &$this->getView($nextViewName, $nextViewType);
		$view->assignRef("appointmentData", &$appointmentData);
		$view->setModel($model, false);

		// error_log("saving the appointment details to the session object\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		$session->set('appointmentData', &$appointmentData, 'SalonBook');
		
		// error_log("appointmentData sent to the next screen  \n[ " . var_export($appointmentData, true) . "]\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// load and add the next Model
		$nextModel = $this->getModel($nextViewModel);
		$view->setModel($nextModel, true);
		
		$view->display();
	}
	
	/**
	 * The user may cancel their appointment if is beyond the minimun cancellation-lead-time
	 */
	function cancelAppointment()
	{
		error_log("inside cancelAppointment()\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$model = new SalonBookModelAppointments();
		
		$appointment_id=JRequest::getVar('id', '0');
		
		if ( $appointment_id > 0 )
		{
			$success = $model->cancelAppointment($appointment_id);
		}
		
		error_log("did the appointment cancel properly? : |" . $success . "| \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$view = &$this->getView('Cancellation', 'html');
		
		if ( $success > 0 )
		{
			// show a success message
			$view->assign("success", true);		
		}
		else
		{
			// show a failure message
			$view->assign("success", false);
		}
		
		$view->display();
	}
	
	/**
	 * Called at the start of the Booking Wizard, if the user wants to Edit an existing appointment
	 */
	function showAppointmentToEdit()
	{
		// error_log("inside showAppointmentToEdit()\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		$appointment_id=JRequest::getVar( 'id');
		
		$model = $this->getModel('appointments');
		$this->appointmentDetails = $model->getAppointmentDetailsForID($appointment_id);
		
		$view = &$this->getView('Services', 'html');
		$view->assignRef("appointmentData", &$this->appointmentDetails);
		
		$view->setModel($model, false);
		
		// load and add the Services Model
		$servicesModel = $this->getModel('services');
		$view->setModel($servicesModel, true);
		
		$view->display();
	}
	
	
	/**
	 * This method can be called by an external process to send reminder messages. The 'daysAhead' parameter will be 
	 * used to calculate which appointments will have reminders sent.
	 * 
	 * If no parameter is sent, a default of 3 will be used.
	 *  
	 * @param int $daysAhead
	 */
	function sendReminderEmails()
	{
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$daysAhead = $configOptions->get('reminder_email_days_ahead',3);
				
		error_log("inside sendReminderEmails...Looking ahead " . $daysAhead . " days\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.'/models/appointments.php');		
		$appointmentModel = $this->getModel('appointments','SalonBookModel');
		
			$appointmentList = $appointmentModel->appointmentsScheduledAhead($daysAhead);
			if ( count($appointmentList) > 0 )
			{
				error_log("inside sendReminderEmails ... sending " . count($appointmentList) . " messages\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");

				foreach ($appointmentList as $appointment)
				{
					$mailer = new SalonBookModelEmail();
					if ( $mailer )
					{
						$apptList = array($appointment);
						$mailer->sendReminders($apptList);
					}
				}
			}
			else 
			{
				error_log("inside sendReminderEmails ... 0 messages to send\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
	}
	
	/**
	 * Send an email after the user attempts to pay the deposit
	 * @param boolean $success
	 */
	function sendPaymentConfirmationEmail($success)
	{
		error_log("\ninside sendPaymentConfirmationEmail...\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$mailer = new SalonBookModelEmail();
		
		if ( $mailer )
		{
			// check to see if the invoice is 'good' i.e. deposit was paid
			if ( $success )
			{
				$mailer->setSuccessMessage($this->appointmentDetails);
			}
			else
			{
				$mailer->setFailureMessage($this->appointmentDetails);
			}
			
			$mailer->sendMail();
		}
	}
	
	/**
	*	Saves a new Appointment given the passed in parameters.
	*	The deposit_paid flag will be set to false.
	*	The current user will be pulled from session values
	*	@Params: date, startTime, stylist
	*
	*	@return (via the ehco statement) 
	*			a NEGATIVE number indicates failure, 
	*			ZERO == a successful update and payment already made, 
	*			a POSITIVE number == successfully added a new appointment and direct user to pay deposit
	*
	*/
	function addappointment()
	{ 
		// error_log("inside addappointment\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");		
		
		date_default_timezone_set('America/Toronto');
		
		$country_id=JRequest::getVar( 'country_id');
		$date = JRequest::getVar('date');
		$startTime = JRequest::getVar('startTime');
		$stylist_id = JRequest::getVar('stylist_id');
		$service_id = JRequest::getVar('service_id');
		
		
		$model = $this->getModel('appointments');

		JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.DS.'models'.DS.'appointments.php');
		$appointmentModel = new SalonBookModelAppointments();
		$success = $appointmentModel->store();
				
		$appointment_id = $appointmentModel->_id;
		// error_log("ID: $appointment_id \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");		
		
		// get the details of the deposit status to determine what is returned here
		if ( $success === false ) 
		{
			echo '-1';
		}
		else 
		{
			if ( $appointmentModel->depositPaid == true )
			{
				echo '0';
			}
			else
			{
				echo $appointment_id;
			}
		}

	}
	
	function saveAppointment()
	{
		$this->appointmentDetails = &$_SESSION['apptData'][0];
		
	}
	
	function showpaymentresult()
	{
		error_log('inside showpaymentresult()\n', 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$model = $this->getModel('appointments');
		$appointmentData = $model->getAppointmentDetails();
		
		$view = &$this->getView('Payment', 'html');
		$view->assignRef("appointmentData", $appointmentData);
		$view->setModel($model, true);
		$view->display();		
	}

	/**
	 * showpaymentsuccess()
	 * 
	 * Display a success message to the user as confirmation of their appointment being booked
	 * 
	 * Another code path is used to actually record the payment detail into the database.
	 * That message is sent out of band to our server from the payment processor
	 * 
	 */
	function showpaymentsuccess()
	{
		JLog::add('inside showpaymentsuccess method');
		
		$invoice_id = JRequest::getVar('xxxVar1');
		
		$model = $this->getModel('appointments');
		$appointmentData = $model->getAppointmentDetailsForID($invoice_id);

		$view = &$this->getView('Processed', 'html');
		$view->assignRef("appointmentData", $appointmentData);
		
		// we could confirm we can read the appointment from the database, but we've already collected their money -- which is the client's main concern
		// for now, we will assume all is okay, and show them a success message. Otherwise we could show them a slightly modified success message: one
		// which does not rely on customizing data to be drawn from the database 
		$view->assign("paid", '1');
		
		$view->setModel($model, true);
		
		$view->display();		
	}

	function showpaymentcancelled()
	{
		error_log('inside showpaymentcancelled()\n', 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$view = &$this->getView('Processed', 'html');
		$view->assign("paid", '0');
		
		$view->display();		
	}

	function paymentcancelled()
	{
		$view = &$this->getView('Payment', 'html');
		$view->assignRef("paidRowCount", "0");
		
		$view->setModel($model, true);
		$view->display();
	}
	
	/**
	 *	Function: paypalconfirmation 
	 *	Called by the PayPal servers upon completion of a transaction (success or failure)
	 *	The database record for the appointment booking is updated here, and logged to the server.
	 *	A confirmation email is also sent out to the user.
	 */
	function paypalconfirmation()
	{
		// $log = &JLog::getInstance('logs/ipn_log.php');
		JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.DS.'models'.DS.'calendar.php');
		// error_log("Completed registering Calendar class \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$calendarModel = new SalonBookModelCalendar();
		// error_log("Got a Calendar class MODEL to work with \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		error_log("IPN start", 3, "../logs/ipn.log");
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		// build the string we need to send back to Paypal for verification
		foreach ($_POST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			error_log("&$key=$value\n", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
		}

		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$invoice_number = $_POST['option_selection2'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];

		if (!$fp) 
		{
			// HTTP ERROR
			error_log("IPN mess up! An HTTP error occurred.\n", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
		} else 
		{
			error_log("IPN reposting SUCCESS!", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
			error_log("Invoice" . $invoice_number . "\n", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
			
			fputs ($fp, $header . $req);
			while (!feof($fp)) 
			{
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) 
				{
					error_log("VERIFIED\n", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
					
					$model = $this->getModel('appointments');
					
					$num_rows = $model->getMarkAppointmentDepositPaid($invoice_number, $txn_id);
					// error_log("db_update rows $num_rows for invoice $invoice_number\n", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
					
					// update the Google Calendar if this was successful
					if ( $num_rows > 0 )
					{						
						$calendarModel->saveAppointmentToGoogle($invoice_number);

						// send an email to the client informing them of the transaction
						$this->sendPaymentConfirmationEmail(true);
					}
					else
					{
						// send an email to the client informing them of the transaction
						$this->sendPaymentConfirmationEmail(false);
					}
				}
				else if (strcmp ($res, "INVALID") == 0) 
				{
					// send an email to the client informing them of the transaction
					$this->sendPaymentConfirmationEmail(false);

					// log for manual investigation
					error_log("INVALID", 3, JPATH_ROOT.DS."logs".DS."ipn.log");
				}
			}
			fclose ($fp);
			
		}
	}

	/**
	 *	Function: internetsecureconfirmation 
	 *	Called by the InternetSecure servers upon completion of a transaction (success or failure)
	 *	The database record for the appointment booking is updated here, and logged to the server.
	 *	A confirmation email is also sent out to the user.
	 */
	function internetsecureconfirmation()
	{
		JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.DS.'models'.DS.'calendar.php');
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$calendarModel = new SalonBookModelCalendar();
		
		// read what was sent to us
		foreach ($_REQUEST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			// JLog::add("&$key=$value");
		}
		
		$invoice_id = JRequest::getVar('xxxVar1');
 		JLog::add("Received Export Script data from InternetSecure for invoice # " . $invoice_id);		
		
		// we only get these messages after a successful transaction, so send an email to the client, and mark the database as DEPOSIT PAID
		$model = $this->getModel('appointments');
		
		$num_rows = $model->getMarkAppointmentDepositPaidFromInternetSecure($invoice_id);
		// error_log("db_update rows $num_rows for invoice $invoice_id\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// update the Google Calendar if this was successful
		if ( $num_rows > 0 )
		{						
			$appointmentData = $model->getAppointmentDetailsForID($invoice_id);
			$this->appointmentDetails = $appointmentData;
			
			$calendarModel->saveAppointmentToGoogle($invoice_id);

			// send an email to the client informing them of the transaction
			$this->sendPaymentConfirmationEmail(true);
		}
		
		$view = &$this->getView('Salonbook', 'raw');
		$view->display();				
	}
	
	/**
	 * Timeslot calculations
	 */

	/**
	 * Helper function
	 *
	 * @param int $slotNumber
	 *
	 * @return string a date/time in the form "6:30"
	 */
	function slotNumber2Time ($slotNumber)
	{
		$theHour = intval($slotNumber / 2);
		if ( $theHour == $slotNumber / 2 )
		{
			$theMinute = "00";
		}
		else
		{
			$theMinute = "30";
		}
	
		if ($theHour > 12)
		{
			$theHour -= 12;
		}
	
		return $theHour . ":" . $theMinute;
	}
	
	/**
	 * Calculate the slots occupied by a known event
	 *
	 * @param int $anEvent
	 * @return array timeslots
	 */
	function timeSlotsUsedByEvent ($anEvent)
	{
		// error_log("\n anEvent: " . $anEvent->startTime . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		$theStart =  strtotime($anEvent->startTime);
		// error_log("\n startTime of anEvent is " . date('g:i a',$theStart) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		$minutes = idate('H', $theStart) * 60;
		$minutes += idate('i', $theStart);
		$startSlotNumber = intval($minutes / 30);
	
		// add the default break time to the end of the appointment to give the staff a chance to complete each customer and prepare for the next
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$breakTime = $configOptions->get('break_time', '17');	// in minutes		
		$duration = $anEvent->durationInMinutes + $breakTime;
		$theEnd = strtotime("+ $duration minutes", $theStart);
	
		// error_log("\n endTime of anEvent (includng break) is " . date('g:i a',$theEnd) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		$minutes = idate('H', $theEnd) * 60;
		$minutes += idate('i', $theEnd);
		// calculate the end time as if they finished a minute earlier
		// this allows an appointment from 2:00 to 2:30 to appear as (29 minutes) so it only occupies a single timeslot
		$endSlotNumber = intval(($minutes - 1) / 30);
	
		// calculate all of the slots used for this appointment. It will always be a simple sequence of integers from the first to the last slot
		for ( $newSlot=$startSlotNumber; $newSlot <= $endSlotNumber; $newSlot++)
		{
			$slotsArray[] = $newSlot;
		}
	
		return $slotsArray;
	}
	
	/**
	 * Build an array of available start times for the date passed in (and the service/stylist requested)
	 *
	 * @param string $aDate
	 * @return string An HTML selection object
	 */
	function availabletimes()
	{
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		
		$aDate = JRequest::getVar('aDate','');
		$aTime = JRequest::getVar('aTime','10:00 am');
		
		$defaultDuration = $configOptions->get('default_appointment_length', '90');
		$duration = JRequest::getVar('duration',$defaultDuration);	// in minutes
	
		// error_log("finding availabletimes for Time: $aTime \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		// error_log("finding availabletimes for Date: $aDate \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		// TODO: place this in a singleton. It need not run all the time.
		$dailySlotsAvailable = array();
		// read start-of-day and end-of-day times from the configuration file
		// 8:00 AM = 16 , 7:00 PM = 38
		
		$firstSlot = $configOptions->get('daily_start_timeslot', '16');	
		$lastSlot = $configOptions->get('daily_end_timeslot', '36');	
		
		for ($slotPosition=$firstSlot; $slotPosition <= $lastSlot; $slotPosition++)
		{
			$dailySlotsAvailable[] = $slotPosition;
		}
		
		$currentDate = strtotime($aDate);
		
		// for each day, find all events
		// use $this->busySlots; then remove from the $dailySlotsAvailable array
	
		$model = $this->getModel('timeslots');
		$feed = $model->getBusySlotsQuery($aDate);
		
		// error_log("the busy feed has " . count($feed) . " items \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		$dailyResults = $feed;
	
		// set up the default available time slots
		$slotsOpenForBookingToday = $dailySlotsAvailable;
		$dailyUsedSlots = array();
	
		// if events were found, then caluate the timeslots used by each,
		// then calculate the available slots, and have them ready for display to the user
		if ( count($feed) > 0 )
		{
			foreach ($feed as $event)
			{
				// $id = substr($event->id, strrpos($event->id, '/')+1);
				$id = $event->id;
	
				// check that the event is for the currentDate
				if ( $event->appointmentDate == date('Y-m-d', $currentDate) )
				{
					// process each event looking for timeslots used
					$usedSlots = $this->timeSlotsUsedByEvent( $event );
				}
				else
				{
					// nothing was found
					$usedSlots = array();
				}
	
				$dailyUsedSlots = array_merge($dailyUsedSlots, $usedSlots);
			}
		}
	
		$slotsOpenForBookingToday = array_diff($dailySlotsAvailable, $dailyUsedSlots);
	
		// now print a list of all available slots for that day
		// Choose a start time but only if there are indeed times availabe for that day, else show a 'Sorry..' message

		$returnValue = "<option value='-1' name='-1'> -- </option>\n";
		
		// error_log("insert " . $duration . " into this array\n " . var_export($slotsOpenForBookingToday, true) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$minutesPerTimeslot = $configOptions->get('default_timeslot_length', '30');	// in minutes
		
		$durationInSlots = ceil($duration / $minutesPerTimeslot);
		
		for ( $x=0; $x < count($slotsOpenForBookingToday); $x++ )
		{
			$thisSlotNumber = $slotsOpenForBookingToday[$x];
			// error_log("thisSlotNumber = " . $thisSlotNumber . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			
			// check to see if the next $durationInSlots are free
			$durationAvailable = false;
			for ( $test = 0; $test < $durationInSlots; $test++ )
			{
				try 
				{
					$nextSlotNumber = $slotsOpenForBookingToday[$x+$test+1];
					$durationAvailable = true;
				} 
				catch (Exception $e) 
				{
					// end of the day
					$nextSlotNumber = 0;
				}
				
				if ( $nextSlotNumber == 0 )
				{
					$durationAvailable = false;
					break;
				}
			}

			// error_log("thisSlotNumber = " . $thisSlotNumber . " NextSlotNumber = " . $nextSlotNumber . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			if ( $durationAvailable == true && $thisSlotNumber )
			{
				// show this timeslot
				$slotTime = $this->slotNumber2Time($thisSlotNumber);
				$ampm = ($thisSlotNumber < 24) ? "am" : "pm";
				$returnValue .= "<option value='$thisSlotNumber' name='$thisSlotNumber' ";
				$displayTime = $slotTime . ' ' . $ampm;
				$selectedTime = date('g:i a', strtotime($aTime));
				if ( $displayTime === $selectedTime )
				{
					$returnValue .= " selected ";
				}
				$returnValue .= ">$slotTime $ampm</option>\n";
				
			}
			
		}

		// error_log("returnValue = " . $returnValue . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		echo $returnValue;
	}	

	/**
	 * Display another month of the calendar
	 * 
	 * @param int theMonth
	 * @param int theYear
	 * 
	 * @return HTML
	 */
	public function showCalendar()
	{
		// get vars
		$theMonth = JRequest::getVar('theMonth',date('m'));	
		$theYear = JRequest::getVar('theYear', date('Y'));
		
		// error_log("theMonth/theYear" . $theMonth . "/" . $theYear . " \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// register Calendar class
		JLoader::register('Calendar',  JPATH_COMPONENT_SITE.DS.'calendar'.DS.'calendar.php');
		
		// display calendar
		$calendar = new Calendar();
		$calendar->showToday(true);
		
		$datesArray = array('type'=>array('link'=>array('href'=>'javascript:calDateSelected')));
		echo $calendar->show($theMonth, $theYear, $datesArray);
		
	}
}
?>