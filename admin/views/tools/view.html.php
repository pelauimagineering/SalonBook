<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBooks View of all bookings created and managed by the system
 */
class SalonBookViewTools extends JView
{
	/**
	 * view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$model = new SalonBookModelUsers;
		$this->countUsersInserted = $model->countUsersInserted;
		$this->countUsersUpdated = $model->countUsersUpdated;

		$pagination = $this->get('Pagination');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		// $this->items = $items;
		$this->pagination = $pagination;
 
		// Display the template
		parent::display($tpl);
	}
	
}
?>