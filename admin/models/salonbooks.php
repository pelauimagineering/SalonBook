<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * SalonBookList Model
 */
class SalonBooksModelSalonBooks extends JModelList
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
		
		$query->select("COALESCE(U.firstName, U.userName) as clientName, CONCAT(U.firstName, ' ', U.lastName) as clientFullName, A.*, S.name as stylistName, SERVICES.name as serviceName, ST.status, A.created_by_staff");
		
		$query->from('#__salonbook_appointments A join #__salonbook_users U on A.user = U.user_id JOIN #__users S on A.stylist = S.id join #__salonbook_services SERVICES on A.service = SERVICES.id JOIN #__salonbook_status ST on A.status = ST.id');
		
		$query->order("A.appointmentDate ASC");
		
		return $query;
	}
}
?>