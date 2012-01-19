<?php
// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * SalonBook Form Field class for the SalonBook component
 */
class JFormFieldStylists extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Stylists';
 
	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions() 
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		// show all users between clients (group=2) and SuperAdmin (group=8)
		$queryString = "SELECT U.firstName as name, U.user_id as id FROM `#__salonbook_users` U JOIN `#__user_usergroup_map` G on U.user_id = G.user_id WHERE G.group_id > 2 AND G.group_id < 8";
		
		$db->setQuery((string)$queryString);
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				$options[] = JHtml::_('select.option', $message->id, $message->displayName);
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}