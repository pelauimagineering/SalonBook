<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');
 
/**
 * SalonBookUsers Table class
 */
class TableUsers extends JTable
{
	// properties
	// primary key
	var $id = null;
	
	var $user_id = 0;
	var $fisrtName = null;
	var $lastName = null;
	var $userName = null;
	var $calendarLogin = null;
	var $calendarPassword = null;
	var $hairstyle = null;
	var $notes = null;
	var $completed_parsing = 0;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableUsers(&$db) 
	{
		parent::__construct('#__salonbook_users', 'id', $db);
	}
}
?>