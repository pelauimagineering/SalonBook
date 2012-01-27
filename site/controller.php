<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
jimport('joomla.error.log');

require_once (JPATH_SITE.DS.'includes'.DS.'Zend'.DS.'Loader.php');
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
		error_log("\ninside sendPaymentConfirmationEmail...\n", 3, "../logs/salonbook.log");
		
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

		error_log("\n\nID: $appointment_id on date: $date\n", 3, "../logs/salonbook.log");
		
	}
	
	function showpaymentresult()
	{
		error_log('inside showpaymentresult()\n', 3, "../logs/salonbook.log");
		
		$model = $this->getModel('appointments');
		$appointmentData = $model->getAppointmentDetails();
		
		$view = &$this->getView('Payment', 'html');
		$view->assignRef("appointmentData", $appointmentData);
		$view->setModel($model, true);
		$view->display();		
	}

	function showpaymentsuccess()
	{
		error_log('inside showpaymentsuccess()\n', 3, "../logs/salonbook.log");
		
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
		error_log('inside showpaymentcancelled()\n', 3, "../logs/salonbook.log");
		
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
		JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.'/models/calendar.php');
		error_log("Completed registering Calendar class \n", 3, "../logs/salonbook.log");
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$calendarModel = new SalonBookModelCalendar();
		error_log("Got a Calendar class MODEL to work with \n", 3, "../logs/salonbook.log");
		
		error_log("IPN start", 3, "../logs/ipn.log");
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		// build the string we need to send back to Paypal for verification
		foreach ($_POST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			error_log("&$key=$value\n", 3, "../logs/ipn.log");
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
			error_log("IPN mess up! An HTTP error occurred.\n", 3, "../logs/ipn.log");
		} else 
		{
			error_log("IPN reposting SUCCESS!", 3, "logs/ipn.log");
			error_log("Invoice" . $invoice_number . "\n", 3, "../logs/ipn.log");
			
			fputs ($fp, $header . $req);
			while (!feof($fp)) 
			{
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) 
				{
					error_log("VERIFIED\n", 3, "../logs/ipn.log");
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
					
					$model = $this->getModel('appointments');
					
					$num_rows = $model->getMarkAppointmentDepositPaid($invoice_number, $txn_id);
					error_log("db_update rows $num_rows for invoice $invoice_number\n", 3, "../logs/ipn.log");
					
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
					error_log("INVALID", 3, "../logs/ipn.log");
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
		error_log("Export Script data from InternetSecure\n", 3, "../logs/salonbook.log");
		
		JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.'/models/calendar.php');
		error_log("Completed registering Calendar class \n", 3, "../logs/salonbook.log");
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$calendarModel = new SalonBookModelCalendar();
		error_log("Got a Calendar class MODEL to work with \n", 3, "../logs/salonbook.log");
		
		// read what was sent to us
		foreach ($_REQUEST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			error_log("&$key=$value\n", 3, "../logs/salonbook.log");
		}
		
		$invoice_id = JRequest::getVar('xxxVar1');
		
		// we only get these messages after a successful transaction, so send an email to the client, and mark the database as DEPOSIT PAID
		$model = $this->getModel('appointments');
		
		// $num_rows = $model->getMarkAppointmentDepositPaid($invoice_number, $txn_id);
		$num_rows = $model->getMarkAppointmentDepositPaidFromInternetSecure($invoice_id);
		error_log("db_update rows $num_rows for invoice $invoice_id\n", 3, "../logs/salonbook.log");
		
		// update the Google Calendar if this was successful
		if ( $num_rows > 0 )
		{						
			$appointmentData = $model->getAppointmentDetailsForID($invoice_id);
			$this->appointmentDetails = $appointmentData;
			
			$calendarModel->saveAppointmentToGoogle($invoice_id);

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