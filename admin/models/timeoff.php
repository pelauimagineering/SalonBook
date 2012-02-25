<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

/**
 * SalonBookList Model
 */
class SalonBooksModelTimeoff extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$timeoffUser = $configOptions->get('timeoff_user',0);
		
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select("A.id, concat(U.firstName, ' ', U.lastName) as stylist, A.appointmentDate as theDate, A.startTime, A.durationInMinutes as duration");
		$query->from("#__salonbook_appointments A JOIN #__salonbook_users U ON A.stylist = U.user_id");
		$query->where("A.user = $timeoffUser AND A.appointmentDate > now()");
		
		error_log("Vacation data: " . $query->dump() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		return $query;
	}
	
}