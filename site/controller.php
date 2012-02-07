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
	
	/** 
	 * Tasks
	 */
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
		error_log("inside updateAppointment()\n", 3, "logs/salonbook.log");

  		$model = new SalonBookModelAppointments();
		
		$appointment_id=JRequest::getVar('id', '0');
		
		$session = JFactory::getSession();
		$appointmentData =& $session->get('appointmentData', array(), 'SalonBook');
		
		if ( $appointment_id == 0 )
		{
			// we shouldn't be fetching data from the database AGAIN, only from the object we are passing around!!
			error_log("LOOKUP or CREATE an appointment object\n", 3, "logs/salonbook.log");
			
// 			$model = new SalonBookModelAppointments();
			error_log("passed-in ID:" . $appointment_id . "\n", 3, "logs/salonbook.log");
			
			if ( $appointmentData['user'] == 0 )			
			{
				// set the user
				$user =& JFactory::getUser();
				$appointmentData['user'] = $user->id;
				error_log("This is the NEW object\n" . var_export($appointmentData, true) ."\n", 3, "logs/salonbook.log");
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
				error_log("appointmentData FROM the model  \n[ " . var_export($appointmentData, true) . "]\n", 3, "logs/salonbook.log");
			}
			else 
			{
				error_log("appointmentData FROM PREVIOUS screen  \n[ " . var_export($appointmentData, true) . "]\n", 3, "logs/salonbook.log");
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
			error_log("SINGLE: setting " . $fieldName . " to " . $fieldValue . "\n", 3, "logs/salonbook.log");

			$appointmentData[$fieldName] = $fieldValue;
		}
		else 
		{
			error_log("field count = " . $fieldCount . "\n", 3, "logs/salonbook.log");
			
			// it's an array
			$fieldNameArray = JRequest::getVar('fieldName');
			$fieldValueArray = JRequest::getVar('fieldValue');
			$newDataArray = array();
			
			for ($x=0; $x < $fieldCount; $x++)
			{				
				error_log("MULTIPLE: setting " . $fieldNameArray[$x] . " to " . $fieldValueArray[$x] . "\n", 3, "logs/salonbook.log");
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

		error_log("saving the appointment details to the session object\n", 3, "logs/salonbook.log");
		$session->set('appointmentData', &$appointmentData, 'SalonBook');
		
		error_log("appointmentData sent to the next screen  \n[ " . var_export($appointmentData, true) . "]\n", 3, "logs/salonbook.log");
		
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
		error_log("inside showAppointmentToEdit()\n", 3, "logs/salonbook.log");
	
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
	
	function sendPaymentConfirmationEmail($success)
	{
		error_log("\ninside sendPaymentConfirmationEmail...\n", 3, "../logs/salonbook.log");
		
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
		error_log("inside addappointment\n", 3, "logs/salonbook.log");		
		
		date_default_timezone_set('America/Toronto');
		
		$country_id=JRequest::getVar( 'country_id');
		$date = JRequest::getVar('date');
		$startTime = JRequest::getVar('startTime');
		$stylist_id = JRequest::getVar('stylist_id');
		$service_id = JRequest::getVar('service_id');
		
		
		$model = $this->getModel('appointments');

		JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.'/models/appointments.php');
		$appointmentModel = new SalonBookModelAppointments();
		$success = $appointmentModel->store();
				
		$appointment_id = $appointmentModel->_id;
		error_log("ID: $appointment_id \n", 3, "logs/salonbook.log");		
		
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
		
		$view = &$this->getView('Salonbook', 'raw');
		$view->display();				
	}
}
?>