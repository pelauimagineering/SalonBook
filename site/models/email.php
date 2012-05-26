<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * SalonBookModelEmail
 * Usage: create a new instance of this class, then call either the setSuccessMessage or setFailureMessage
 * Then call sendMail()
 * @returns true/false to indicate success of sending
 */
class SalonBookModelEmail extends JModelItem
{
	protected $type;
	protected $email;
	protected $mailer;
	
	function sendMail()
	{
		$recipients = NULL;	// clear out whatever addresses may be left from the last batch sent out
		$recipients = array( $this->email );
		$this->mailer->addRecipient($recipients);
		// $this->mailer->addBCC("cron@pelau.com");
		
		$send =& $this->mailer->Send();
		
		if ( $send !== true ) {
		    // echo 'Error sending email: ' . $send->message;
			error_log("\n Error sending email:" . $send->message . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log"););
		} else {
		    // echo 'Mail sent';
		    // $emailList = var_export($recipients,true);
			$emailList = implode(",", $recipients);
			error_log("\n Email sent to:" . $emailList . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log"););
		}
	}
	
	function setSuccessMessage($appointmentData)
	{
		// look up the details
		$stylistName = $appointmentData[0]['stylistName'];
		$serviceName = $appointmentData[0]['serviceName'];
		$appointmentDate = date('l F j',strtotime($appointmentData[0]['appointmentDate']));
		$startTime = date('g:i a', strtotime($appointmentData[0]['startTime']));
		$this->email = $appointmentData[0]['email'];
		
		$this->message = JText::sprintf('COM_SALONBOOK_EMAIL_BODY_SUCCESS', $serviceName, $stylistName, $appointmentDate, $startTime);
		$this->mailer->setBody($this->message);		
	}
	
	function setDetailsUpdatedMessage($appointmentData)
	{
		// look up the details
		$stylistName = $appointmentData[0]['stylistName'];
		$serviceName = $appointmentData[0]['serviceName'];
		$appointmentDate = $appointmentData[0]['appointmentDate'];
		$startTime = $appointmentData[0]['startTime'];
		$this->email = $appointmentData[0]['email'];
		
		$this->message = JText::sprintf('COM_SALONBOOK_EMAIL_BODY_DETAILS_UPDATED', $serviceName, $stylistName, $appointmentDate, date('g:i a', $startTime));
		$this->mailer->setBody($this->message);		
	}
	
	function setFailureMessage($appointmentData)
	{
		$this->email = $appointmentData[0]['email'];
		
		$this->message = JText::_('COM_SALONBOOK_EMAIL_BODY_FAILURE');
		$this->mailer->setBody($this->message);		
	}
	
	
	/**
	 * Send reminder messages to the list of appointments that have been passed to us
	 * 
	 *  @param array $appointmentList
	 */
	function sendReminders($appointmentList)
	{
		// error_log("inside sendReminders...\nFor list " . var_export($appointmentList[0], true) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// get appointment details and email address
		JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.DS.'models'.DS.'appointments.php');
		$appointmentModel = new SalonBookModelAppointments();
		
		foreach ($appointmentList as $appointment)
		{
			$mailingInfo = $appointmentModel->detailsForMail($appointment['id']);
		
			//error_log("mailingInfo... " . var_export($mailingInfo, true) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$this->setSuccessMessage($mailingInfo);
		
			$this->sendMail();
		}
	}
	
	function __construct()
	{
		// error_log("\n constructing an email...\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$this->mailer =& JFactory::getMailer();
		
		$config =& JFactory::getConfig();
		$sender = array( 
		    $config->getValue( 'config.mailfrom' ),
		    $config->getValue( 'config.fromname' ) );

		$this->mailer->setSender($sender);
		
		$subject = JText::sprintf('COM_SALONBOOK_EMAIL_SUBJECT', $config->getValue('config.fromname'));
		
		$this->mailer->setSubject($subject);
		$this->mailer->isHTML(true);
		$this->mailer->Encoding = 'base64';
		
	}
}
?>