<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.model');

ini_set("include_path", JPATH_ROOT.DS."includes");
require_once 'Zend/Loader.php';

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

	/**
	 * Delete a single entry from a Google Calendar
	 * 
	 * @param string $calendarEventURL
	 * 
	 * @return bool success
	 */
	function deleteCalendarEntry($calendar, $calendarEventURL)
	{
		error_log("deleting event at URI: " . $calendarEventURL . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		try {
			$response = $calendar->performHttpRequest('DELETE', $calendarEventURL);
			
		} catch (Zend_Gdata_App_Exception $e) {
			error_log("Error deleting a calendar entry: " . $e->getMessage() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			return false;
		}
		
		return true;
	}
	
	/**
	 * Instantiate and return a live connection to the Google Calendar service
	 * 
	 * @param string $calendarLogin
	 * @param string $calendarPassword
	 * 
	 * @return Zend_Gdata_Calendar 
	 */
	function setupCalendarConnection($calendarLogin, $calendarPassword)
	{
		error_log("trying to setupCalendarConnection \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		JLoader::register('Zend_Loader', JPATH_ROOT.DS.'includes'.DS.'Zend'.DS.'Loader.php');
		
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		Zend_Loader::loadClass('Zend_Http_Client');
	
		// create authenticated HTTP client for Calendar service
		$gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
		
		$client = Zend_Gdata_ClientLogin::getHttpClient($calendarLogin, $calendarPassword, $gcal);
		$calendar = new Zend_Gdata_Calendar($client);
		
		error_log("We have a calendar object for " . $calendarLogin . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		return $calendar;
	}
	
	/**
	 * Create and save a Google Calendar object
	 * If an existing calendar_id is passed in, then update instead of create the event
	 * @param unknown_type $calendar
	 * @param string $customer
	 * @param string $service
	 * @param string $appointmentDate
	 * @param datetime $startTimestamp
	 * @param datetime $formattedEndDate
	 * @param datetime $formattedEndTime
	 * @param int $event_to_update
	 */	
	function createCalendarEvent ($calendar, $customer_id, $customer_name, $service, $appointmentDate, $startTimestamp, $formattedEndDate, $formattedEndTime, $calendarEventURL = null)	
	{
		error_log("createCalendarEvent \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		if ( $calendarEventURL == null )
		{
			error_log("creating a brand new calendar event\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$newEvent = $calendar->newEventEntry();
		}
		else
		{
			// retrieve an existing event
			error_log("updating event at URI: " . $calendarEventURL . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			try {
				$newEvent = $calendar->getCalendarEventEntry($calendarEventURL);
			} catch (Zend_Gdata_App_Exception $e) {
				error_log("Error retrieving a calendar entry: " . $e->getMessage() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
		}
		
		$eventTitle = "$customer_name ($service)";
		$newEvent->title = $calendar->newTitle($eventTitle);
		
		// query the user_profiles for the phone #
		$user =& JFactory::getUser();
		$db = JFactory::getDBO();
		$phoneQuery = "SELECT profile_value FROM #__user_profiles WHERE user_id = $customer_id AND profile_key = 'salonbookprofile.phone_mobile'";
		error_log("phone # query: " . $phoneQuery . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$db->setQuery((string)$phoneQuery);
		$db->query();
		$phoneNumber = $db->loadResult();
		
		$newContent = $service . "\nPhone: " . $phoneNumber;
		error_log("content: " . $newContent . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		$newEvent->content = $calendar->newContent("$newContent");
			
		$startTime = date("H:i", $startTimestamp);
		$endDate = $formattedEndDate;
	
		$when = $calendar->newWhen();
	
		// to avoid conversion errors
		date_default_timezone_set('America/Toronto');
		
		$timezoneToronto = new DateTimeZone('America/Toronto');
		$torontoTime = new DateTime();
		$timezoneOffset =( $timezoneToronto->getOffset($torontoTime) ) / 3600;
		
		$formattedTimezoneOffset = sprintf("%+03d",$timezoneOffset);
		
		$when->startTime = "{$appointmentDate}T{$startTime}:00.000{$formattedTimezoneOffset}:00";
		$when->endTime = "{$endDate}T{$formattedEndTime}:00.000{$formattedTimezoneOffset}:00";
		
		$newEvent->when = array($when);
	
		// Upload the event to the calendar server
		// A copy of the event as it is recorded on the server is returned
		if ( $calendarEventURL == null )
		{
			error_log("inserting the calendar event..\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$createdEvent = $calendar->insertEvent($newEvent);
			
			$output = "Calendar Event ID: " . $createdEvent->id->text . " EditLink: " . $createdEvent->getEditLink()->href . "\n";
			error_log($output, 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			return $createdEvent->getEditLink()->href;
		}
		else
		{	
			error_log("updating the calendar event..\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			// if this is an update
			try 
			{
				$newEvent->save();
			} 
			catch ( Zend_Gdata_App_Exception $e ) 
			{
				error_log("Error updating calendar entry: " . $e->getMessage() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
			
			return $newEvent->getEditLink()->href;
		}
	}
	
	function saveAppointmentToGoogle($appointment_id)
	{
		error_log("inside saveAppointmentToGoogle...\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	
		JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.DS.'models'.DS.'appointments.php');		
		$appointmentModel = new SalonBookModelAppointments();
		$appointmentData = $appointmentModel->getAppointmentDetailsForID($appointment_id);
	
		// to avoid conversion errors
		date_default_timezone_set('America/Toronto');
	
		// extract the interesting details needed to create a Google Calendar event
		$stylistName = $appointmentData[0]['stylistName'];
		error_log("Stylist: " . $stylistName . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$customer_name = $appointmentData[0]['name'];
		$appointmentDate = $appointmentData[0]['appointmentDate'];
		$startTime = $appointmentData[0]['startTime'];
		$customer_id = $appointmentData[0]['user'];
		
		//TODO: read the configurable default duration (that was set up by the Site Administrator during installation) - this is a fail-safe method, as this should have been caught long before here
		$duration = ($appointmentData[0]['durationInMinutes'] > 0) ? $appointmentData[0]['durationInMinutes'] : 90;
		$service = $appointmentData[0]['serviceName'];
		
		$calendarLogin = $appointmentData[0]['calendarLogin'];
		$calendarPassword = $appointmentData[0]['calendarPassword'];					
	
		// use the stylist data to figure out the correct calendar to post to		
		$calendar = $this->setupCalendarConnection($calendarLogin, $calendarPassword);
		$calFeed = $calendar->getCalendarListFeed();
		$output = "here's the calendarFeed title [  " . $calFeed->title->text . "  ]\n";
		error_log($output, 3, JPATH_ROOT.DS."logs".DS."salonbook.log");	
	
		// calendar math
		$fullStartDateTimeString = "$appointmentDate"." "."$startTime";
		$startTimestamp = strtotime($fullStartDateTimeString);
	
		$endTime = strtotime("+$duration minutes", $startTimestamp);
	
		$formattedEndTime = date("H:i", $endTime);
		$formattedEndDate = date("Y-m-d", $endTime);
	
		$fullEndDateTimeString = $formattedEndDate." ".$formattedEndTime;
		$endTimestamp = strtotime($fullEndDateTimeString);
	
		// pass in any existing calendar event for this appointment so it can be updated instead of creating a new one
		$calendarEventURL = $appointmentData[0]['calendarEventURL'];
		$returnedEventURL = $this->createCalendarEvent ($calendar, $customer_id, $customer_name, $service, $appointmentDate, $startTimestamp, $formattedEndDate, $formattedEndTime, $calendarEventURL);
		
		$appointmentData[0]['calendarEventURL'] = $returnedEventURL;
		
		$session = JFactory::getSession();
		$session->set('appointmentData', $appointmentData[0], 'SalonBook');
		$appointmentModel->store();
	}
}
?>