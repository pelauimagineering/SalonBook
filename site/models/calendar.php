<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.model');

ini_set("include_path", JPATH_ROOT.DS."includes");
require_once 'Zend/Loader.php';
error_log("Zend Loader found and loaded\n", 3, "../logs/salonbook.log");

// GoogleAPI v3.0
// require_once 'google-api-php-client/src/apiClient.php';
// require_once 'google-api-php-client/src/contrib/apiPlusService.php';
// session_start();

/**
 * SalonBookModelCalendar model
 * Methods to communicate with the Google Calendar service
 */
class SalonBookModelCalendar extends JModel
{
	protected $type;
	protected $email;
	protected $mailer;

	function setupCalendarConnection($calendarLogin = 'admin@celebrityunisexsalon.com', $calendarPassword = 'JKX9DuR7eyBCEXEj')
	{
		error_log("trying to setupCalendarConnection \n", 3, "../logs/salonbook.log");
	
		error_log("loading Zend classes...\n", 3, "../logs/salonbook.log");
		
		JLoader::register('Zend_Loader', JPATH_PLATFORM.DS.'includes'.DS.'Zend'.DS.'Loader.php');	//includes
		error_log("loaded Zend_Loader...\n", 3, "../logs/salonbook.log");
		
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		Zend_Loader::loadClass('Zend_Http_Client');
	
		error_log("All required Zend classes found and loaded\n", 3, "../logs/salonbook.log");
	
		// create authenticated HTTP client for Calendar service
		$gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
		
		$client = Zend_Gdata_ClientLogin::getHttpClient($calendarLogin, $calendarPassword, $gcal);
		$calendar = new Zend_Gdata_Calendar($client);
		
		return $calendar;
	}
	
	function createCalendarEvent ($calendar, $customer, $service, $appointmentDate, $startTimestamp, $formattedEndDate, $formattedEndTime)	
	{
		error_log("createCalendarEvent \n", 3, "../logs/salonbook.log");
		
		$newEvent = $calendar->newEventEntry();
		$eventTitle = "$customer ($service)";
		$newEvent->title = $calendar->newTitle($eventTitle);
		$newEvent->content = $calendar->newContent("$service");
	
		$startTime = date("H:i", $startTimestamp);
		$endDate = $formattedEndDate;
	
		$when = $calendar->newWhen();
	
		$timezoneOffset = '-05';	// -05 during the summer, -04 during the winter		
		$when->startTime = "{$appointmentDate}T{$startTime}:00.000{$timezoneOffset}:00";
		$when->endTime = "{$endDate}T{$formattedEndTime}:00.000{$timezoneOffset}:00";
		$newEvent->when = array($when);
	
		// Upload the event to the calendar server
		// A copy of the event as it is recorded on the server is returned
		$createdEvent = $calendar->insertEvent($newEvent);
	
		$output = "Calendar Event ID: " . $createdEvent->id->text . "\n";
		error_log($output, 3, "../logs/salonbook.log");
		
		return $output;		
	}
	
	function saveAppointmentToGoogle($appointment_id)
	{
		error_log("\ninside saveAppointmentToGoogle...\n", 3, "../logs/salonbook.log");
	
		JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.'/models/appointments.php');		
		$appointmentModel = new SalonBookModelAppointments();
		$appointmentData = $appointmentModel->getAppointmentDetailsForID($appointment_id);
	
		// to avoid conversion errors
		date_default_timezone_set('America/Toronto');
	
		// extract the interesting details needed to create a Google Calendar event
		$stylistName = $appointmentData[0]['stylistName'];
		error_log("Stylist: " . $stylistName . "\n", 3, "../logs/salonbook.log");
		
		$customer = $appointmentData[0]['name'];
		$appointmentDate = $appointmentData[0]['appointmentDate'];
		$startTime = $appointmentData[0]['startTime'];
		
		//TODO: read the configurable default duration (that was set up by the Site Administrator during installation)
		$duration = ($appointmentData[0]['durationInMinutes'] > 0) ? $appointmentData[0]['durationInMinutes'] : 90;
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
	
		error_log("go setup the connection...\n", 3, "../logs/salonbook.log");
		// use the stylist data to figure out the correct calendar to post to
		
		$calendar = $this->setupCalendarConnection($calendarLogin, $calendarPassword);
		$calFeed = $calendar->getCalendarListFeed();
		$output = "here's the calendarFeed title [  " . $calFeed->title->text . "  ]\n";
		error_log($output, 3, "../logs/salonbook.log");	
	
		// calendar math
		$fullStartDateTimeString = "$appointmentDate"." "."$startTime";
		$startTimestamp = strtotime($fullStartDateTimeString);
	
		$endTime = strtotime("+$duration minutes", $startTimestamp);
	
		$formattedEndTime = date("H:i", $endTime);
		$formattedEndDate = date("Y-m-d", $endTime);
	
		$fullEndDateTimeString = $formattedEndDate." ".$formattedEndTime;
		$endTimestamp = strtotime($fullEndDateTimeString);
	
		$this->createCalendarEvent ($calendar, $customer, $service, $appointmentDate, $startTimestamp, $formattedEndDate, $formattedEndTime);
	}
}
?>