<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * SalonBookList Model
 */
class SalonBooksModelClients extends JModelList
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
		// Select some fields
		
		$query->select("concat(U.firstName,' ',U.lastName) as clientName, A.*, S.name as stylistName, SERVICES.name as serviceName, P.profile_value as phoneNumber");
		
		$query->from(	"#__salonbook_appointments A 
						JOIN #__salonbook_users U on A.user = U.user_id 
						JOIN #__salonbook_services SERVICES on A.service = SERVICES.id 
						JOIN #__users S on A.stylist = S.id 
						LEFT JOIN #__user_profiles P ON A.user = P.user_id 
							AND P.profile_key LIKE 'salonbookprofile.phone_mobile' 
						AND A.appointmentDate > now()
						WHERE A.user != $timeoffUser 
						ORDER BY clientName, A.appointmentDate ASC"
				
		);
		
		return $query;
	}
}
?>