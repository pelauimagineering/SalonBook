<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * SalonBook Controller
 */
class SalonBooksControllerSalonBook extends SalonBooksController
{
	function __construct()
	{
		parent::__construct();
		
		// Register extra tasks
		$this->registerTask('add', 'edit');
		$this->registerTask('delete', 'remove');
	}
	
	/**
	*	display the edit form
	*/
	function edit()
	{
		error_log("show the EDIT form\n", 3, "../logs/salonbook.log");
		
		JRequest::setVar('view','salonbook');
		JRequest::setVar('layout','edit');
		JRequest::setVar('hidemanmenu',true);
		
		parent::display();
	}

	/**
	 * Save a record and return to the main page
	 * @return	void
	 */
	function save()
	{
		error_log("tried to call SAVE() \n", 3, "../logs/salonbook.log");
		
		$model = $this->getModel('salonbook');
		
		if ($model->store($post))
		{
			$msg = JText::_('Appointment saved');
		}
		else 
		{
			$msg = JText::_('Error saving Appointment');
		}
		
		$link = 'index.php?option=com_salonbook';
		$this->setRedirect($link, $msg);
	}
	
	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		error_log("tried to REMOVE \n", 3, "../logs/salonbook.log");
		
		$model = $this->getModel('salonbook');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or more Appointments could not be deleted' );
		} else {
			$msg = JText::_( 'Appointment(s) Deleted' );
		}
	
		$this->setRedirect( 'index.php?option=com_salonbook', $msg );
	}
	
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_salonbook', $msg );
	}
}
?>