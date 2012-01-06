<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
// ini_set("include_path", "includes");  
// require_once 'Zend/Loader.php';
require_once 'models/users.php';
/**
 * General Controller of HelloWorld component
 */
class SalonBookController extends JController
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false) 
	{
		// set default view if not set
		JRequest::setVar('view', JRequest::getCmd('view', 'SalonBooks'));
 
		// call parent behavior
		parent::display($cachable);
	}
	
	function synchUsers()
	{
		error_log("\ninside synchUsers... BEFORE copyUsers\n", 3, "../logs/salonbook.log");
		
		$model = $this->getModel('users');
		$model->getCopyUsers();

		error_log("\ninside synchUsers... AFTER copyUsers\n", 3, "../logs/salonbook.log");
				
		$view = &$this->getView('Tools', 'html');
		// $view->assignRef("appointmentData", $appointmentData);
		$view->setModel($model, true);
		$view->display();		
		
	}
}
?>