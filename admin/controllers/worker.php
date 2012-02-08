<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

require_once (JPATH_ROOT.DS.'includes'.DS.'Zend'.DS.'Loader.php');

/**
 * SalonBook Controller
 */
class SalonBooksControllerWorker extends SalonBooksController
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	*	display the edit form
	*/
	function edit()
	{
		error_log("show the worker EDIT form\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		JRequest::setVar('view','worker');
		JRequest::setVar('layout','edit');
		JRequest::setVar('hidemanmenu',true);
		
		parent::display();
	}
	
	function save()
	{
		error_log("SAVE the worker data\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$model = $this->getModel('worker');
	
		$post = JInput::get('post');
	
		$success = $model->store($post);
		
		if ( $success )
		{
			$link = 'index.php?option=com_salonbook&view=staff';
			$this->setRedirect($link, $msg);
		}
	}
}
?>