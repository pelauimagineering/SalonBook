<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');
 
/**
 * Vacation Table class
 */
class TableVacation extends JTable
{
	// primary key
	var $id = null;
	
	// properties
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
	var $returnTime = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableVacation(&$db) 
	{
		parent::__construct('#__salonbook_appointments', 'id', $db);
	}
}