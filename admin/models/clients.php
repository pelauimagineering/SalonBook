<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * SalonBookList Model
 */
class SalonBookModelClients extends JModelList
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
		// Select some fields
		
		// for each client we want 1) full name 2) date and time of next booking 3) next service 4) stylist name
		$query->select("concat(U.firstName,' ',U.lastName) as clientName, A.*, S.name as stylistName, SERVICES.name as serviceName");
		
		$query->from('#__salonbook_appointments A join #__salonbook_users U on A.user = U.user_id JOIN #__users S on A.stylist = S.id join #__salonbook_services SERVICES on A.service = SERVICES.id GROUP BY A.stylist ORDER BY A.appointmentDate DESC');
		return $query;
	}
}
?>