<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

/**
 * SalonBookList Model
 */
class SalonBooksModelStaff extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 * We now link to a 3rd party extension - GCalendar (com_gcalendar) to provide visual calendars in the backend. We must test for its existence.
	 * 
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$stylists_group = $configOptions->get('stylists_group',0);
		
		if ( JFactory::getUser()->authorise('core.access', 'com_gcalendar') )
		{
			$query->select("concat(U.firstName, ' ', U.lastName) as stylistName, U.calendarLogin, U.calendarPassword, JU.id, U.calendarMenuItemId as calendar_id");
			
			$query->from("	`#__salonbook_users` U
							JOIN `#__users` JU ON U.user_id = JU.id
							JOIN `#__user_usergroup_map` G on U.user_id = G.user_id
							WHERE G.group_id = '$stylists_group' " );
		}
		else
		{
			$query->select("concat(U.firstName, ' ', U.lastName) as stylistName, U.calendarLogin, U.calendarPassword, JU.id, '0' as calendar_id");
			
			$query->from("	`#__salonbook_users` U
							JOIN `#__users` JU ON U.user_id = JU.id
							JOIN `#__user_usergroup_map` G on U.user_id = G.user_id 
							WHERE G.group_id = '$stylists_group' " );
		}
		return $query;
	}	
}
?>