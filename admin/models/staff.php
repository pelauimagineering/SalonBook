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
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select("concat(U.firstName, ' ', U.lastName) as stylistName, U.calendarLogin, U.calendarPassword, JU.id");
		
		$query->from("	`#__salonbook_users` U
						JOIN `#__users` JU ON U.user_id = JU.id
						JOIN `#__user_usergroup_map` G on U.user_id = G.user_id WHERE G.group_id > 2 AND G.group_id < 8" );
		
		return $query;
	}	
}
?>