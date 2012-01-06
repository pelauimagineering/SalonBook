<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBook View
 */
class SalonBookViewSalonBook extends JView
{
	/**
	 * display method of SalonBook view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		// get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
 
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
		// the stylesheet is need to display the header graphics properly in the backend Admin
		JHtml::stylesheet('com_salonbook/admin.stylesheet.css', array(), true, false, false);
		JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		
		JToolBarHelper::title($isNew ? JText::_('COM_SALONBOOK_MANAGER_APPOINTMENTS_NEW')
		                             : JText::_('COM_SALONBOOK_MANAGER_APPOINTMENTS_EDIT'), 'salonbook');
		JToolBarHelper::save('salonbook.save');
		JToolBarHelper::cancel('salonbook.cancel', $isNew ? 'JTOOLBAR_CANCEL'
		                                                   : 'JTOOLBAR_CLOSE');
	}
}
?>