<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
// require_once 'calendar.php';

/**
 * SalonBook Model
 */
class SalonBookModelConfirmation extends JModelItem
{
        /**
         * @var string msg
         */
        protected $msg;
 		protected $cbProfile;
		protected $queryGoogleCalendarForAppointments;
		protected $busyTimes;
		
		/**
		 * Returns a reference to the SalonBook Table object, always creating it.
		 *
		 * @param	type	The table type to instantiate
		 * @param	string	A prefix for the table class name. Optional.
		 * @param	array	Configuration array for model. Optional.
		 * @return	JTable	A database object
		 * @since	1.6
		 */
		public function getTable($type = 'SalonBook', $prefix = 'SalonBookTable', $config = array()) 
		{
			return JTable::getInstance($type, $prefix, $config);
		}
		
		public function getSelectedDate()
		{
			$theDate = JRequest::getString('selected_date', 1);
			return $theDate;
		}
		
		public function getSelectedTime()
		{
			$theTime = JRequest::getString('selected_startTime', 1);	
			return $theTime;
		}

        /**
         * Get the message
         * @return string The message to be displayed to the user
         */
        public function getMsg() 
        {
			if (!isset($this->msg)) 
			{
				$id = JRequest::getInt('id', 1);
				// Get a TableSalonBook instance
				$table = $this->getTable();

				// Load the message
				$table->load($id);

				// Assign the message
				$this->msg = $table->displayName;
			}
			return $this->msg;
        }

		/**
		 *	adds the list of time slots used by the passed-in event to a master list for the day
		 */
		/*
		public function parseTimeSlotsFromEvent()
		{
			return "<!-- empty list of time slots -->";
		}
		*/
		
		public function getCBProfile()
		{
			if (!isset($this->cbProfile))
			{
				// find the current users' hairstyle
				$user =& JFactory::getUser();

				// Create a new query object.		
				$this->user_id = $user->id;

				$db = JFactory::getDBO();

				// $query = $db->getQuery(true);
				$userPrefsQuery = "select * from #__comprofiler where user_id = $user->id";
				$db->setQuery((string)$userPrefsQuery);
				$this->cbProfile = $db->loadObject();
			}
			return $this->cbProfile;
		}
		
		// function: timeSlotsUsedByEvent
		// @params: calendar Event
		// @return: array of time slot numbers (0=12:00 - 12:30AM, 1= 12:30 AM - 1:00 AM)
		function timeSlotsUsedByEvent ($anEvent)
		{
			// open up the event to look at the startTime -> endTime to calculate timeslots used
			foreach ($anEvent->when as $when)
			{
				echo "Start: " . $when->startTime . "<br/>\n";
				$theStart =  strtotime($when->startTime);
				$minutes = idate('H', $theStart) * 60;
				$minutes += idate('i', strtotime($when->startTime));
				$startSlotNumber = intval($minutes / 30);

				echo "A start time of " . date("H:i", strtotime($when->startTime))  . " means the the event begins in slot # $startSlotNumber<br/>";

				// ======

				echo "End: " . $when->endTime . "<br/>\n";
				$theEnd =  strtotime($when->endTime);
				$minutes = idate('H', $theEnd) * 60;
				$minutes += idate('i', strtotime($when->endTime));
				// calculate the end time as if they finished a minute earlier
				// this allows an appointment from 2:00 to 2:30 to appear as (29 minutes) so it only occupies a single timeslot
				$endSlotNumber = intval(($minutes - 1) / 30);	

				echo "An end time of " . date("H:i", strtotime($when->endTime))  . " puts the end of the event in slot # $endSlotNumber<br/>";

				// calculate all of the slots used for this appointment. It will always be a simple sequence of integers from the first to the last slot
				for ( $newSlot=$startSlotNumber; $newSlot <= $endSlotNumber; $newSlot++)
				{
					$slotsArray[] = $newSlot;
				}

			}

			return $slotsArray;
		}
		
		// function: getAvailableSlotsQuery
		// @params: startDate, endDate
		// @return: array for slots available for a given set of (consecutive) days
		public function getAvailableSlotsQuery()
		{
			$startDate = strtotime('+1 day 00:00');
			$endDate = strtotime('+8 days 23:59');
			
			$db = JFactory::getDBO();
			$endOfDayOnLastDay = strtotime("23:59", $endDate);
			$busyTimesQuery = "SELECT * FROM #__salonbook_appointments WHERE date >= $startDate AND date <= $endOfDayOnLastDay ";	// AND (stylist == id_of_chosen_stylist OR user == $current_user_id)
			$db->setQuery((string)$busyTimesQuery);
			$this->busyTimes = $db->loadObjectList();
			
			return $this->busyTimes;
		}
		
		/*
		////////////////////////////
		//// from AJAX version /////
		////////////////////////////
		public function setupDailySlotsAvailable()
		{
			// show the next 7 days as the default, but allow the option of seeing more

			// Array: dailySlotsAvailable
			$dailySlotsAvailable = array();
			// read start-of-day and end-of-day times from the configuration file 
			// 8:00 AM = 16 , 7:00 PM = 38
			$firstSlot = 16;
			$lastSlot = 38;

			for ($slotPosition=$firstSlot; $slotPosition <= $lastSlot; $slotPosition++)
			{
				$dailySlotsAvailable[] = $slotPosition;
			}

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


			// function: timeSlotsUsedByEvent
			// @params: calendar Event
			// @return: array of time slot numbers (0=12:00 - 12:30AM, 1= 12:30 AM - 1:00 AM)
			function timeSlotsUsedByEvent ($anEvent)
			{
				// open up the event to look at the startTime -> endTime to calculate timeslots used
				foreach ($anEvent->when as $when)
				{
					//echo "Start: " . $when->startTime . "<br/>\n";
					$theStart =  strtotime($when->startTime);
					$minutes = idate('H', $theStart) * 60;
					$minutes += idate('i', strtotime($when->startTime));
					$startSlotNumber = intval($minutes / 30);

					//echo "A start time of " . date("H:i", strtotime($when->startTime))  . " means the the event begins in slot # $startSlotNumber<br/>";

					// ======

					//echo "End: " . $when->endTime . "<br/>\n";
					$theEnd =  strtotime($when->endTime);
					$minutes = idate('H', $theEnd) * 60;
					$minutes += idate('i', strtotime($when->endTime));
					// calculate the end time as if they finished a minute earlier
					// this allows an appointment from 2:00 to 2:30 to appear as (29 minutes) so it only occupies a single timeslot
					$endSlotNumber = intval(($minutes - 1) / 30);	

					//echo "An end time of " . date("H:i", strtotime($when->endTime))  . " puts the end of the event in slot # $endSlotNumber<br/>";

					// calculate all of the slots used for this appointment. It will always be a simple sequence of integers from the first to the last slot
					for ( $newSlot=$startSlotNumber; $newSlot <= $endSlotNumber; $newSlot++)
					{
						$slotsArray[] = $newSlot;
					}

				}

				return $slotsArray;
			}

		    // load library
			require_once 'Zend/Loader.php';

		    Zend_Loader::loadClass('Zend_Gdata');

		    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		    Zend_Loader::loadClass('Zend_Gdata_Calendar');
		    Zend_Loader::loadClass('Zend_Http_Client');

		    // create authenticated HTTP client for Calendar service
		    $gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
		    $user = "admin@celebrityunisexsalon.com";
		    $pass = "JKX9DuR7eyBCEXEj";
		    $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $gcal);
		    $gcal = new Zend_Gdata_Calendar($client);

		    // generate query to get event list
		    $query = $gcal->newEventQuery();
		    $query->setUser('default');
		    $query->setVisibility('private');
		    $query->setProjection('full');

			// more parameters
			$query->setOrderby('starttime');
			$query->setFutureEvents(FALSE);

		//	$start = urlencode(date(DATE_ATOM, strtotime('today 00:00')));
		//    $end = urlencode(date(DATE_ATOM, strtotime('+7 days 23:59')));
		//$start = date(DATE_ATOM, strtotime('today 00:00'));
		//$end = date(DATE_ATOM, strtotime('+3 months 23:59'));

		// use parameters to determine how much data we show. The default start date will be 'tomorrow' and will be 1 week and the default end is 'one week' //
			$start = strtotime('+1 day 00:00');

			$endDateParam = JRequest::getInt('endDateParam');
			if ( $endDateParam < 1)
			{
				$endDateParam = 8;
			}
			$end = strtotime('+' . $endDateParam . ' days 23:59');

		//echo "start time: $start<br/>\n";
		//echo date("j M Y H:i:s <br/>\n", $start);
		//echo "end time: $end<br/>\n";

		// if the user has previously selected a date within this time period, the entire row of the table will be given a highlight background colour

		// add a script to allow the selection of only a single timeslot at a time. On select, all others will be reset.
		// display the selected Date and Time in a separate div on the page
	}
	*/
		
	public function getCalendarDataFromGoogle()
	{
		if (!isset($this->queryGoogleCalendarForAppointments))
		{
			// create the query
			
			// cache for later
			$this->queryGoogleCalendarForAppointments = array();
		}
		
		return $this->queryGoogleCalendarForAppointments;
	}
}