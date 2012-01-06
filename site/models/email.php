<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * SalonBookEmailer
 * Usage: create a new instance of this class, then call either the setSuccessMessage or setFailureMessage
 * Then call sendMail()
 * @returns true/false to indicate success of sending
 */
class SalonBookEmailer extends JModelItem
{
	protected $type;
	protected $email;
	protected $mailer;
	
	function sendMail()
	{
		$recipients = array( $this->email, 'darren@pelau.com' );
		$this->mailer->addRecipient($recipients);
		
		$send =& $this->mailer->Send();
		
		// $user =& JFactory::getUser();
		// $loggedInUser = $user->email;
		
		if ( $send !== true ) {
		    // echo 'Error sending email: ' . $send->message;
			error_log("\n Error sending email:" . $send->message . "\n", 3, "logs/salonbook.log");
		} else {
		    // echo 'Mail sent';
			error_log("\n Email sent to:" . $loggedInUser . "\n", 3, "logs/salonbook.log");
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
		
		$this->message = JText::sprintf('COM_SALONBOOK_EMAIL_BODY_SUCCESS', $serviceName, $stylistName, $appointmentDate, $startTime);
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
		
		// $user =& JFactory::getUser();
		// $loggedInUser = $user->email;

		// $recipients = array( $loggedInUser, 'darren@pelau.com' );
		// $this->mailer->addRecipient($recipients);
		
		$subject = JText::sprintf('COM_SALONBOOK_EMAIL_SUBJECT', $config->getValue('config.fromname'));
		
		$this->mailer->setSubject($subject);
		$this->mailer->isHTML(true);
		$this->mailer->Encoding = 'base64';
		
	}
}
?>