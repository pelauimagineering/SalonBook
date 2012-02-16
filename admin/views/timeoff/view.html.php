<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBooks View of all bookings created and managed by the system
 */
class SalonBooksViewTimeoff extends JView
{
	/**
	 * View form
	 * @var form
	 */
	protected $form = null;
	
	/**
	 * view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$form = $this->get('Form');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;
		$this->form = $form;
		
		// Set the toolbar
		$this->addToolBar($tpl);
		
		// Display the template
		parent::display($tpl);
	}
	
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar($tpl = null) 
	{
		JHtml::stylesheet('com_salonbook/admin.stylesheet.css', array(), true, false, false);
		JToolBarHelper::title(JText::_('COM_SALONBOOK_MANAGER_TIMEOFF'),'salonbook');
		JToolBarHelper::editList('edit');
		JToolBarHelper::addNew('add');
		
		if (JFactory::getUser()->authorise('core.admin')) JToolBarHelper::preferences('com_salonbook');
	}
}
?>