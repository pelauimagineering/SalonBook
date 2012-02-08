<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBook View
 */
class SalonBooksViewSalonBook extends JView
{
	/**
	 * display method of SalonBook view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		
		// get the Data
		$form = $this->get('Form');
		$appointment = $this->get('Appointment');
 
		// get more data
		$stylists = $this->get('OptionListOfStylists');
		$services = $this->get('OptionListOfServices');
		$clients = $this->get('OptionListOfClients');
		$statusNames = $this->get('OptionListOfStatusNamesForAdmin');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->appointment = $appointment;
 		$this->stylistList = $stylists;
		$this->serviceList = $services;
		$this->clientList = $clients;
		$this->statusList = $statusNames;

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
		$isNew = ($this->appointment->id == 0);
		
		JToolBarHelper::title($isNew ? JText::_('COM_SALONBOOK_MANAGER_APPOINTMENTS_NEW')
		                             : JText::_('COM_SALONBOOK_MANAGER_APPOINTMENTS_EDIT'), 'salonbook');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel', $isNew ? 'JTOOLBAR_CANCEL'
		                                                   : 'JTOOLBAR_CLOSE');
	}
}
?>