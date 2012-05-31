<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * ServicesList Model
 */
class SalonBookModelServices extends JModelList
{
	protected $serviceList;
	protected $darren;
	protected $dList;
	
	/**
	 * Method to build an SQL query to load the list data.
	 * We must avoid showing the 'Time Off' service stored at the beginning of the table
	 *
	 * @param	bool	should the duration of each service be displayed
	 * @return	string	An SQL query
	 */
	public function getListQuery($showDuration = null)
	{
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$servicesQuery = "select S.id, S.name, D.displayName, D.durationInMinutes from #__salonbook_services S join #__salonbook_durations D on S.duration = D.id where S.id > 1 order by S.name ASC";
		$db->setQuery((string)$servicesQuery);
		$db->query();
		
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				if ( $showDuration == true )
				{
					$toDisplay = $message->name . "&nbsp; &nbsp; &nbsp; &nbsp;" . $message->displayName; 
					$options[] = JHtml::_('select.option', $message->id, $toDisplay);
				}
				else 
				{
					$options[] = JHtml::_('select.option', $message->id, $message->name);
				}
			}
		}

		return $options;
	}
	
	/**
	 * Override used by the Admin-side displays to embed the duration of the service for staff to see
	 */
	public function getListWithDuration()
	{
		return $this->getListQuery(true);
	}
}
?>