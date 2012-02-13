<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');
 
/**
 * SalonBook Table class
 */
class TableSalonBook extends JTable
{
	// properties
	// primary key
	var $id = null;
	
	var $created_by_staff = 0;
	var $appointmentDate = null;
	var $startTime = null;
	var $durationInMinutes = null;
	var $user = 0;
	var $deposit_paid = 0;
	var $balance_due = null;
	var $stylist = null;
	var $service = null;
	var $status = 0;
	var $calendarEventURL = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableSalonbook(&$db) 
	{
		parent::__construct('#__salonbook_appointments', 'id', $db);
	}
}