<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SalonBook View
 */
class SalonBooksViewVacation extends JView
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
		$vacation = $this->get('VacationDetails');
 
		$this->vacation->returnTime = '17:00:00';
		// error_log("Vacation object: " . var_export($vacation, true) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->vacation = $vacation;
		

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
		JToolBarHelper::title(JText::_('COM_SALONBOOK_MANAGER_TIMEOFF_EDIT'), 'salonbook');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
	}
}
?>