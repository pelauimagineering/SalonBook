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
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$timeoffUser = $configOptions->get('timeoff_user',0);
		
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select("CONCAT(U.firstName, ' ', U.lastName) as clientFullName, A.*, CONCAT(S.firstName, ' ', S.lastName) as stylistName, SERVICES.name as serviceName, ST.status, A.created_by_staff");		
		$query->from("	#__salonbook_appointments A, #__salonbook_users U, #__salonbook_users S, #__salonbook_services SERVICES, #__salonbook_status ST ");
		$query->where(	"A.stylist = S.user_id AND A.user = U.user_id AND A.service = SERVICES.id AND A.status = ST.id ".
						"AND A.appointmentDate > DATE_SUB(now(), INTERVAL 7 DAY) " .
						"AND U.user_id <> " . $timeoffUser 
					);
		$query->order("A.appointmentDate ASC");
		
		
		return $query;
	}
}
?>