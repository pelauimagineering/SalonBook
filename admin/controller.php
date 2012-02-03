<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
require_once (JPATH_ROOT.DS.'includes'.DS.'Zend'.DS.'Loader.php');
require_once 'models/users.php';

/**
 * General Controller of Salonbook component
 */
class SalonBooksController extends JController
{
	/**
	 * The default task is to display the view
	 *
	 *	@access public
	 *  @return void
	 */
	function display($cachable = false) 
	{
		error_log("called for task:  " . JRequest::getVar('task', 'unknown-task') . "\n" , 3, "../logs/salonbook.log");
		
 		// call parent behavior
		parent::display($cachable);
	}
	
	/**
	 * Individual changes to User data should be kept in synch via the Salonbook plugin, 
	 * but calling this code directly from the Tools tab in the admin interface will force all data back into synch
	 */
	function synchUsers()
	{
		error_log("\ninside synchUsers... BEFORE copyUsers\n", 3, "../logs/salonbook.log");
		
		$model = $this->getModel('users');
		$model->getCopyUsers();

		error_log("\ninside synchUsers... AFTER copyUsers\n", 3, "../logs/salonbook.log");
				
		$view = &$this->getView('Tools', 'html');
		$view->setModel($model, true);
		$view->display();		
		
	}
}
?>