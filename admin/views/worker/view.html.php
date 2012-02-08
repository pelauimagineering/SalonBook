<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBook View
 */
class SalonBooksViewWorker extends JView
{
	/**
	 * View form
	 * @var form
	 */
	protected $form = null;
	
	/**
	 * display method of SalonBook view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		
		// get the Data
		$form = $this->get('Form');
		$worker = $this->get('WorkerDetails');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->worker = $worker;

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
		JToolBarHelper::title(JText::_('COM_SALONBOOK_MANAGER_STAFF_CALENDAR_LOGIN_EDIT'), 'salonbook');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
	}
}
?>