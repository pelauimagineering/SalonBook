<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * SalonBook Model
 */
class SalonBookModelTimeslots extends JModelItem
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
				$userPrefsQuery = "select * from `#__comprofiler` where user_id = $user->id";
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
				//echo "Start: " . $when->startTime . "<br/>\n";
				$theStart =  strtotime($when->startTime);
				$minutes = idate('H', $theStart) * 60;
				$minutes += idate('i', strtotime($when->startTime));
				$startSlotNumber = intval($minutes / 30);

				//echo "A start time of " . date("H:i", strtotime($when->startTime))  . " means the the event begins in slot # $startSlotNumber<br/>";

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
		
		// function: getAvailableSlotsQuery
		// @params: startDate, endDate
		// @return: array for slots available for a given set of (consecutive) days
		public function getAvailableSlotsQuery()
		{
			/*
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`date` DATE NOT NULL ,
			`startTime` TIME NULL ,
			`duration` INT NULL COMMENT  'foreign key',
			`user` INT NOT NULL COMMENT  'foreign key',
			`deposit_paid` BINARY NOT NULL DEFAULT  '0',
			`balance_due` FLOAT NULL ,
			`stylist` INT NULL COMMENT  'foreign key'
			*/
			
			$startDate = strtotime('+1 day 00:00');
			$endDate = strtotime('+8 days 23:59');
			
			// $startDate = date("H:i", strtotime('+1 day 00:00'));
			// $endDate = date("H:i", strtotime('+8 days 23:59'));
			 
			// appointmentDate
			// startTime
			// duraionInMinutes
			
			// $durationInMinutes = 90;
			// $appointmentStart = '';
			error_log("INSIDE: getAvailableSlotsQuery startDate:" . $startDate . " endDate: $endDate \n", 3, "../logs/salonbook.log");
			
			$db = JFactory::getDBO();
			$endOfDayOnLastDay = strtotime("23:59", $endDate);
			$busyTimesQuery = "SELECT * FROM `#__salonbook_appointments` WHERE strtotime(appointmentDate+'T'+startTime) >= $startDate AND strtotime(appointmentDate) <= $endOfDayOnLastDay ";	
			
			error_log("the busyTimesQuery sql: " . $busyTimesQuery . "\n", 3, "../logs/salonbook.log");
			
			$db->setQuery((string)$busyTimesQuery);
			$this->busyTimes = $db->loadObjectList();
			
			return $this->busyTimes;
		}

		// function: getBusySlotsQuery
		// @params: startDate, endDate
		// @return: array for slots already booked for a given set of (consecutive) days
		public function getBusySlotsQuery()
		{
			/*
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`date` DATE NOT NULL ,
			`startTime` TIME NULL ,
			`duration` INT NULL COMMENT  'foreign key',
			`user` INT NOT NULL COMMENT  'foreign key',
			`deposit_paid` BINARY NOT NULL DEFAULT  '0',
			`balance_due` FLOAT NULL ,
			`stylist` INT NULL COMMENT  'foreign key'
			*/
			
			// find the current users' hairstyle
			$user =& JFactory::getUser();
			$current_user_id = $user->id;
			
			// $startDate = strtotime('+1 day 00:00');
			// $endDate = strtotime('+8 days 23:59');
			
			$startDate = date("Y-m-d", strtotime('+1 day 00:00'));
			// $startTime = date("H:i", $startDate);
			
			$endDate = date("Y-m-d", strtotime('+8 days 23:59'));
			// $endTime = date("H:i", $endDate);
			 
			// appointmentDate
			// startTime
			// duraionInMinutes
			
			error_log("INSIDE: getAvailableSlotsQuery startDate:" . $startDate . " endDate: $endDate \n", 3, "../logs/salonbook.log");
			
			// $query->setStartMin($currentDate);
			// $endOfDay = strtotime("23:59", $currentDate);
			// $query->setStartMax($endOfDay);
			$db = JFactory::getDBO();
			// $endOfDayOnLastDay = strtotime("23:59", $endDate);
			$busyTimesQuery = "SELECT * FROM `#__salonbook_appointments` WHERE deposit_paid = '1' AND user = '$current_user_id' AND appointmentDate >= '$startDate' AND appointmentDate <= '$endDate' ";	
			
			error_log("the busyTimesQuery sql: " . $busyTimesQuery . "\n", 3, "../logs/salonbook.log");
			
			$db->setQuery((string)$busyTimesQuery);
			$this->busyTimes = $db->loadObjectList();
			
			return $this->busyTimes;
		}
		
}