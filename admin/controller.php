<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
// ini_set("include_path", "includes");  
// require_once 'Zend/Loader.php';
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
		// set default view if not set
// 		JRequest::setVar('view', JRequest::getCmd('view', 'SalonBooks'));
		
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
		// $view->assignRef("appointmentData", $appointmentData);
		$view->setModel($model, true);
		$view->display();		
		
	}
	
	function TESTdisplay()
	{		
		/*
		// ====================
		// try incorporating multiple data models
		// ====================
		$view = &$this->getView(JRequest::getCmd('view', 'SalonBooks'), 'html');

		$stylistModel = $this->getModel('stylists');
		$view->setModel($stylistModel);

		// $view = & $this->getView( 'My3', 'html' );
		// $view->setModel( $this->getModel( 'My3' ), true );
		// $view->setModel( $this->getModel( 'My1' ) );
		// $this->setModel( $this->getModel( 'Stylists' ) );

		
		$defaultModel = $this->getModel('salonbooks');
		$view->setModel($defaultModel, true);	// 'true' in the second parameter sets this ad the default model for the view
		// 
		JRequest::setVar('view', $view);
		
		//TODO: handle the different layouts used for editing: delete, edit, add
		JRequest::setVar('layout', JRequest::getCmd('layout','add'));
		// JRequest::setVar('template', 'edit');
		// parent::display($cachable);
		$view->display();
		*/		
	}
}
?>