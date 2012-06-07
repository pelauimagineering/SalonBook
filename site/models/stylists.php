<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * StylistsList Model
 */
class SalonBookModelStylists extends JModelList
{
	protected $stylistsList;
	protected $hairstyle;
	protected $user;
		
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	public function getListQuery()
	{
		$hair = $this->getHairstyle();
		
		// bypass this check until version 2
		// a user SHOULD be logged in to even see this, but let's show everyone all stylists if something is wrong/missing in the User's Profile
		if ( $hair <> '' )
		{
			$queryString = "select C.firstname, U.id, U.name from #__users U JOIN #__comprofiler C on U.id = C.user_id where C.cb_stylesworked like '%$hair%'";
		}
		else
		{
			$queryString = "select C.firstname, U.id, U.name from #__users U JOIN #__comprofiler C on U.id = C.user_id where C.cb_stylesworked <> 'None'";
		}

		// show all users in the Staff group
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$stylists_group = $configOptions->get('stylists_group',0);
				
		$queryString = "SELECT U.firstName as name, U.user_id as id FROM `#__salonbook_users` U JOIN `#__user_usergroup_map` G on U.user_id = G.user_id WHERE G.group_id = $stylists_group";

		$db = JFactory::getDBO();
		$db->setQuery((string)$queryString);
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				$options[] = JHtml::_('select.option', $message->id, $message->name);
			}
		}
		// $options = array_merge(parent::getOptions(), $options);
		return $options;
	}
	
	public function getHairstyle()
	{
		// find the current users' hairstyle
		$user =& JFactory::getUser();
		
		// Create a new query object.		
		$this->user_id = $user->id;
		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$userPrefsQuery = "select cb_hairstyle from #__comprofiler where user_id = $user->id";
		$db->setQuery((string)$userPrefsQuery);
		$this->hairstyle = $db->loadResult();
		
		return $this->hairstyle;
	}
}
?>