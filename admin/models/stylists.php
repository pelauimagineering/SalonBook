<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * SalonBookStylist Model
 */
class SalonBookModelStylists extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	//TODO: duplicated in the SalonbookModelSalonbook class
	public function __getOptionListOfStylists()
	{
		// show all users between clients (group=2) and SuperAdmin (group=8)
		$queryString = "SELECT U.firstName as name, U.user_id as id FROM `#__salonbook_users` U JOIN `#__user_usergroup_map` G on U.user_id = G.user_id WHERE G.group_id > 2 AND G.group_id < 8";

		$db = JFactory::getDBO();
		$db->setQuery((string)$queryString);
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				$options[] = JHtml::_('select.option', $message->id, $message->name.'D');
			}
		}
		// $options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}