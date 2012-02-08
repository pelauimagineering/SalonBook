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
	 * timeSlotsUsedByEvent
	 * 
	 * @param calendar Event
	 * @return array of time slot numbers (0=12:00 - 12:30AM, 1= 12:30 AM - 1:00 AM)
	 */
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
		
	/**
	 * getAvailableSlotsQuery
	 * @return  array  An array of slots available for a given set of (consecutive) days
	 */
	public function getAvailableSlotsQuery()
	{
		$startDate = strtotime('+1 day 00:00');
		$endDate = strtotime('+8 days 23:59');
		
		$db = JFactory::getDBO();
		$endOfDayOnLastDay = strtotime("23:59", $endDate);
		$busyTimesQuery = "SELECT * FROM #__salonbook_appointments WHERE date >= $startDate AND date <= $endOfDayOnLastDay ";
		$db->setQuery((string)$busyTimesQuery);
		$this->busyTimes = $db->loadObjectList();
		
		return $this->busyTimes;
	}
				
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