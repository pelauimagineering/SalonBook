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
		$recipients = array( $this->email );
		$this->mailer->addRecipient($recipients);
		
		$send =& $this->mailer->Send();
		
		if ( $send !== true ) {
		    // echo 'Error sending email: ' . $send->message;
			error_log("\n Error sending email:" . $send->message . "\n", 3, "logs/salonbook.log");
		} else {
		    // echo 'Mail sent';
		    $emailList = var_export($recipients,true);
			error_log("\n Email sent to:" . $emailList . "\n", 3, "logs/salonbook.log");
		}
	}
	
	function setSuccessMessage($appointmentData)
	{
		// look up the details
		$stylistName = $appointmentData[0]['stylistName'];
		$serviceName = $appointmentData[0]['serviceName'];
		$appointmentDate = $appointmentData[0]['appointmentDate'];
		$startTime = $appointmentData[0]['startTime'];
		$this->email = $appointmentData[0]['email'];
		
		$this->message = JText::sprintf('COM_SALONBOOK_EMAIL_BODY_SUCCESS', $serviceName, $stylistName, $appointmentDate, date("H:i", $startTime));
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
		
		$this->message = JText::sprintf('COM_SALONBOOK_EMAIL_BODY_DETAILS_UPDATED', $serviceName, $stylistName, $appointmentDate, date("H:i", $startTime));
		$this->mailer->setBody($this->message);		
	}
	
	function setFailureMessage($appointmentData)
	{
		$this->email = $appointmentData[0]['email'];
		
		$this->message = JText::_('COM_SALONBOOK_EMAIL_BODY_FAILURE');
		$this->mailer->setBody($this->message);		
	}
	
	function __construct()
	{
		error_log("\n constructing an email...\n", 3, "logs/salonbook.log");
		
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