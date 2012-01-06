<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
jimport('joomla.error.log');

ini_set("include_path", "includes");  
require_once 'Zend/Loader.php';
require_once 'models/email.php';

/* TEST URL
http://celebrity3.pelau.com/index.php?option=com_salonbook&view=payment&task=showpaymentresult&tx=11364803SM584351M&st=Pending&amt=16.95&cc=CAD&cm=&item_number=
*/

/* response from Internet Secure
http://celebrity3.pelau.com/index.php?option=com_salonbook&view=payment&task=internetsecureconfirmation
*/

/**
 * Salon Book Component Controller
 */
class SalonBookController extends JController
{
	protected $appointmentDetails;
	
	function sendPaymentConfirmationEmail($success)
	{
		error_log("\ninside sendPaymentConfirmationEmail...\n", 3, "logs/salonbook.log");
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$mailer = new SalonBookEmailer();
		
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
	
	function setupCalendarConnection($calendarLogin, $calendarPassword)
	{
		error_log("trying to setupCalendarConnection [method call] \n", 3, "logs/salonbook.log");

		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		Zend_Loader::loadClass('Zend_Http_Client');

		error_log("Zend_Loader found and loaded\n", 3, "logs/salonbook.log");

		// create authenticated HTTP client for Calendar service
		$gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;

		error_log("prepare an entry for [ $calendarLogin ]\n", 3, "logs/salonbook.log");

		$client = Zend_Gdata_ClientLogin::getHttpClient($calendarLogin, $calendarPassword, $gcal);
		$calendar = new Zend_Gdata_Calendar($client);
		
		return $calendar;
	}

	function createCalendarEvent ($calendar, $customer, $service, $appointmentDate, $startTimestamp, $formattedEndDate, $formattedEndTime)	
	{
		error_log('createCalendarEvent', 3, "logs/salonbook.log");
		
		$newEvent = $calendar->newEventEntry();
		$eventTitle = "$customer ($service)";
		$newEvent->title = $calendar->newTitle($eventTitle);
		$newEvent->content = $calendar->newContent("$service");

		$startTime = date("H:i", $startTimestamp);
		$endDate = $formattedEndDate;

		$when = $calendar->newWhen();

		$when->startTime = "{$appointmentDate}T{$startTime}:00.000";
		$when->endTime = "{$endDate}T{$formattedEndTime}:00.000";
		$newEvent->when = array($when);

		// Upload the event to the calendar server
		// A copy of the event as it is recorded on the server is returned
		$createdEvent = $calendar->insertEvent($newEvent);

		$output = 'Calendar Event ID: ' . $createdEvent->id->text . '\n';
		error_log($output, 3, "logs/salonbook.log");
		
		return $output;
		
	}

	function saveAppointmentToGoogle($appointment_id)
	{
		error_log("\ninside saveAppointmentToGoogle...\n", 3, "logs/salonbook.log");


		$model = $this->getModel('appointments');
		$appointmentData = $model->getAppointmentDetailsForID($appointment_id);
		$this->appointmentDetails = $appointmentData;
		
		// to avoid conversion errors
		date_default_timezone_set('America/Toronto');

		// extract the interesting details needed to create a Google Calendar event
		$stylistName = $appointmentData[0]['stylistName'];
		error_log("Stylist: " . $stylistName . "\n", 3, "logs/salonbook.log");
		
		$customer = $appointmentData[0]['name'];
		$appointmentDate = $appointmentData[0]['appointmentDate'];
		$startTime = $appointmentData[0]['startTime'];
		$duration = $appointmentData[0]['durationInMinutes'];
		$service = $appointmentData[0]['serviceName'];
		
		// if we are missing login info for this user, default to the login for the admin account
		if ( $appointmentData[0]['calendarLogin'] == '' || $appointmentData[0]['calendarPassword'] == '' )
		{
			$calendarLogin = 'admin@celebrityunisexsalon.com';
			$calendarPassword = 'JKX9DuR7eyBCEXEj';					
		}
		else
		{
			$calendarLogin = $appointmentData[0]['calendarLogin'];
			$calendarPassword = $appointmentData[0]['calendarPassword'];					
		}

		error_log("go setup the connection...\n", 3, "logs/salonbook.log");
		// use the stylist data to figure out the correct calendar to post to
		
		$calendar = $this->setupCalendarConnection($calendarLogin, $calendarPassword);
		$calFeed = $calendar->getCalendarListFeed();
		$output = "here's the calendarFeed title [  " . $calFeed->title->text . "  ]\n";
		error_log($output, 3, "logs/salonbook.log");
		


		// calendar math
		$fullStartDateTimeString = "$appointmentDate"." "."$startTime";
		$startTimestamp = strtotime($fullStartDateTimeString);

		$endTime = strtotime("+$duration minutes", $startTimestamp);

		$formattedEndTime = date("H:i", $endTime);
		$formattedEndDate = date("Y-m-d", $endTime);

		// $fullEndDateTimeString = $formattedEndDate." ".$formattedEndTime;
		// $endTimestamp = strtotime($fullEndDateTimeString);

		$this->createCalendarEvent ($calendar, $customer, $service, $appointmentDate, $startTimestamp, $formattedEndDate, $formattedEndTime);
	}
	
	
	/**
	*	Saves a new Appointment given the passed in parameters.
	*	The deposit_paid flag will be set to false.
	*	The current user will be pulled from session values
	*	@Params: date, startTime, stylist
	*/
	function addappointment()
	{ 
		date_default_timezone_set('America/Toronto');
		
		// variables needed: date, startTime, stylist, service

		$country_id=JRequest::getVar( 'country_id');
		$date = JRequest::getVar('date');
		$startTime = JRequest::getVar('startTime');
		$stylist_id = JRequest::getVar('stylist_id');
		$service_id = JRequest::getVar('service_id');
		
		$model = $this->getModel('appointments');
		$appointment_id = $model->getNewAppointment($date, $startTime, $stylist_id, $service_id);

		echo $appointment_id;

		error_log("\n\nID: $appointment_id on date: $date\n", 3, "logs/salonbook.log");
		
	}
	
	function showpaymentresult()
	{
		error_log('inside showpaymentresult()\n', 3, "logs/salonbook.log");
		
		$model = $this->getModel('appointments');
		$appointmentData = $model->getAppointmentDetails();
		
		$view = &$this->getView('Payment', 'html');
		$view->assignRef("appointmentData", $appointmentData);
		$view->setModel($model, true);
		$view->display();		
	}

	function showpaymentsuccess()
	{
		error_log('inside showpaymentsuccess()\n', 3, "logs/salonbook.log");
		
		$invoice_id = JRequest::getVar('xxxVar1');
		
		$model = $this->getModel('appointments');
		$appointmentData = $model->getAppointmentDetailsForID($invoice_id);

		$view = &$this->getView('Processed', 'html');
		$view->assignRef("appointmentData", $appointmentData);
		
		// we could confirm we can read the appointment from the database, but we've already collected their money -- which is the client's main concern
		// for now, we will assume all is okay, and show them a success message. Otherwise e could show them a slightly modified success message: one
		// which does not rely on customizing data to be drawn from the database 
		$view->assign("paid", '1');
		
		$view->setModel($model, true);
		
		$view->display();		
	}

	function showpaymentcancelled()
	{
		error_log('inside showpaymentcancelled()\n', 3, "logs/salonbook.log");
		
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
		
		error_log("IPN start", 3, "logs/ipn.log");
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		// build the string we need to send back to Paypal for verification
		foreach ($_POST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			error_log("&$key=$value\n", 3, "logs/ipn.log");
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
			error_log("IPN mess up! An HTTP error occurred.\n", 3, "logs/ipn.log");
		} else 
		{
			error_log("IPN reposting SUCCESS!", 3, "logs/ipn.log");
			error_log("Invoice" . $invoice_number . "\n", 3, "logs/ipn.log");
			
			fputs ($fp, $header . $req);
			while (!feof($fp)) 
			{
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) 
				{
					error_log("VERIFIED\n", 3, "logs/ipn.log");
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
					
					$model = $this->getModel('appointments');
					
					$num_rows = $model->getMarkAppointmentDepositPaid($invoice_number, $txn_id);
					error_log("db_update rows $num_rows for invoice $invoice_number\n", 3, "logs/ipn.log");
					
					// update the Google Calendar if this was successful
					if ( $num_rows > 0 )
					{						
						$this->saveAppointmentToGoogle($invoice_number);

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
					error_log("INVALID", 3, "logs/ipn.log");
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
		error_log("Export Script data from InternetSecure\n", 3, "logs/salonbook.log");
		
		// read what was sent to us
		foreach ($_REQUEST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			error_log("&$key=$value\n", 3, "logs/salonbook.log");
		}
		
		//echo "hello";
		
		//xxxVar1
		$invoice_id = JRequest::getVar('xxxVar1');
		
		// we only get these messages after a successful transaction, so send an email to the client, and mark the database as DEPOSIT PAID
		$model = $this->getModel('appointments');
		
		// $num_rows = $model->getMarkAppointmentDepositPaid($invoice_number, $txn_id);
		$num_rows = $model->getMarkAppointmentDepositPaidFromInternetSecure($invoice_id);
		error_log("db_update rows $num_rows for invoice $invoice_id\n", 3, "logs/salonbook.log");
		
		// update the Google Calendar if this was successful
		if ( $num_rows > 0 )
		{						
			$appointmentData = $model->getAppointmentDetailsForID($invoice_id);
			$this->appointmentDetails = $appointmentData;
			
			$this->saveAppointmentToGoogle($invoice_id);

			// send an email to the client informing them of the transaction
			$this->sendPaymentConfirmationEmail(true);
		}
		
		// redirect to the home page
		// $message = "Payment made at Internet Secure";
		// $this->setRedirect('index.php', $message);
		
		$view = &$this->getView('Salonbook', 'raw');
		$view->display();				
	}
}
?>