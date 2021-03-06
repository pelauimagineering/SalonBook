<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

require_once (JPATH_ROOT.DS.'includes'.DS.'Zend'.DS.'Loader.php');

/**
 * SalonBook Controller
 */
class SalonBooksControllerVacation extends SalonBooksController
{
	function __construct()
	{
		parent::__construct();
		
		$this->registerTask('add', 'edit');
		$this->registerTask('delete', 'remove');
	}
	
	/**
	*	display the edit form
	*/
	function edit()
	{
		error_log("show the vacation EDIT form\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		JRequest::setVar('view','vacation');
		JRequest::setVar('layout','edit');
		JRequest::setVar('hidemanmenu',true);
		
		parent::display();
	}
	
	/**
	 * Override the default cancel behaviour. Send the user to view the list of scheduled vacations
	 */
	function cancel()
	{
		JRequest::setVar('view','timeoff');
		JRequest::setVar('hidemanmenu',false);
		
		parent::display();
	}
	
	function save()
	{
		JLog::add("Save the vacation data");

		$model = $this->getModel('vacation');
	
		$post = JInput::get('post');
	
		$appointmentID = $model->store($post);
		
		if ( $appointmentID > 0 )
		{
			JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.'/models/calendar.php');
			$calendarModel = new SalonBookModelCalendar();
			$calendarModel->saveAppointmentToGoogle($appointmentID);
			
			$link = 'index.php?option=com_salonbook&view=timeoff';
 			$this->setRedirect($link, $msg);
		}
	}
	
	function delete()
	{
		error_log("DELETE the vacation data\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
	}
}
?>