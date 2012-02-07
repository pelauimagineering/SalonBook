<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBooks View of all bookings created and managed by the system
 */
class SalonBooksViewSalonBooks extends JView
{
	/**
	 * view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;
 
		// Set the toolbar
		$this->addToolBar();
		
		// Display the template
		parent::display($tpl);
	}
	
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JHtml::stylesheet('com_salonbook/admin.stylesheet.css', array(), true, false, false);
		JToolBarHelper::title(JText::_('COM_SALONBOOK_MANAGER_APPOINTMENTS'),'salonbook');
		JToolBarHelper::deleteList(JText::_('COM_SALONBOOK_MANAGER_CONFIRM_APPOINTMENT_DELETION'), 'delete');	
		JToolBarHelper::editList('edit');
		JToolBarHelper::addNew('add');
		
		if (JFactory::getUser()->authorise('core.admin')) JToolBarHelper::preferences('com_salonbook');
	}
}
?>