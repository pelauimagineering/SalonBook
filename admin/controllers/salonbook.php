<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

require_once (JPATH_ROOT.DS.'includes'.DS.'Zend'.DS.'Loader.php');

/**
 * SalonBook Controller
 */
class SalonBooksControllerSalonBook extends SalonBooksController
{
	function __construct()
	{
		parent::__construct();
		
		// Register extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('delete', 'remove');
	}
	
	/**
	*	display the edit form
	*/
	function edit()
	{
		error_log("show the EDIT form\n", 3, "../logs/salonbook.log");
		
		JRequest::setVar('view','salonbook');
		JRequest::setVar('layout','edit');
		JRequest::setVar('hidemanmenu',true);
		
		parent::display();
	}

	/**
	 * Save a record and return to the main page
	 * @return	void
	 */
	function save()
	{
		// error_log("tried to call SAVE() \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$model = $this->getModel('salonbook');
		
		$post = JInput::get('post');
		
		// is this new or an update
		if ( $post['id'] == 0 )
		{
			$transactionType = 'new';
		}
		else
		{
			$transactionType = 'update';
		}
		
		$success = $model->store($post);
		$newInvoiceNumber = $model->get('_id');

		error_log("Transaction type: " . $transactionType . "  New invoice number: " . $newInvoiceNumber .  "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		if ( $newInvoiceNumber > 0 )
		{
			$msg = JText::_('Appointment saved');
			
			// error_log("START calendar work\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.'/models/calendar.php');
			// error_log("Completed registering Calendar class \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			// look up details and decide the contents of the message based on success/failure of the payment
			$calendarModel = new SalonBookModelCalendar();
			// error_log("Got a Calendar class MODEL to work with \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
 			// error_log("Go add a cal event using the model... \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$calendarModel->saveAppointmentToGoogle($newInvoiceNumber);
			
			// error_log("END calendar imports\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.'/models/appointments.php');
			// error_log("Completed registering Appointments helper class \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$appointmentModel = $this->getModel('appointments','SalonBookModel');
			
			$appointmentData = $appointmentModel->getAppointmentDetailsForID($newInvoiceNumber);
			
			error_log("sending email to customer\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			// send an email to the client
			if ( $transactionType == 'new' )
			{
				$this->sendPaymentConfirmationEmail(true, $appointmentData);
			}
			else
			{
				$this->sendAppointmentDetailsEmail(true, $appointmentData);
			}
			
		}
		else 
		{
			$msg = JText::_('Error saving Appointment');
		}
		
		$link = 'index.php?option=com_salonbook';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		error_log("tried to REMOVE \n", 3, "../logs/salonbook.log");
		
		$model = $this->getModel('salonbook');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or more Appointments could not be deleted' );
		} else {
			$msg = JText::_( 'Appointment(s) Deleted' );
		}
	
		$this->setRedirect( 'index.php?option=com_salonbook', $msg );
	}
	
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_salonbook', $msg );
	}
	
	/**
	 * Send an email with appointment details to the customer after payment was successful
	 * 
	 * @param Boolean $success
	 * @param array $appointmentDetails
	 */
	function sendPaymentConfirmationEmail($success, $appointmentDetails)
	{
		error_log("inside sendPaymentConfirmationEmail...\n", 3, "../logs/salonbook.log");
	
		JLoader::register('SalonBookModelEmail',  JPATH_COMPONENT_SITE.'/models/email.php');
		error_log("Completed registering Email class \n", 3, "../logs/salonbook.log");
		
		// look up details and decide the contents of the message based on success/failure of the payment
		$mailer = new SalonBookModelEmail();
		error_log("Created new SalonBookModelEmail instance \n", 3, "../logs/salonbook.log");
		
		if ( $mailer )
		{
			// check to see if the invoice is 'good' i.e. deposit was paid
			if ( $success )
			{
				$mailer->setSuccessMessage($appointmentDetails);
			}
			else
			{
				$mailer->setFailureMessage($appointmentDetails);
			}
	
			$mailer->sendMail();
		}
	}

	/**
	 * Send an email with appointment details to the customer after any change was made to the details.
	 *
	 * @param Boolean $success
	 * @param array $appointmentDetails
	 */
	function sendAppointmentDetailsEmail($success, $appointmentDetails)
	{
		error_log("inside sendAppointmentDetailsEmail...\n", 3, "../logs/salonbook.log");
	
		JLoader::register('SalonBookModelEmail',  JPATH_COMPONENT_SITE.'/models/email.php');
		error_log("Completed registering Email class \n", 3, "../logs/salonbook.log");
	
		// look up details and decide the contents of the message based on success/failure of the payment
		$mailer = new SalonBookModelEmail();
		error_log("Created new SalonBookModelEmail instance \n", 3, "../logs/salonbook.log");
	
		if ( $mailer )
		{
			// check to see if the invoice is 'good' i.e. deposit was paid
			if ( $success )
			{
				$mailer->setDetailsUpdatedMessage($appointmentDetails);
			}
			else
			{
				$mailer->setFailureMessage($appointmentDetails);
			}
	
			$mailer->sendMail();
		}
	}
	
}
?>