<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import joomla controller library
jimport('joomla.application.component.controller');
 
JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );

$view = JRequest::getCmd('view');

JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_APPOINTMENTS'), 'index.php?option=com_salonbook&view=salonbooks',$view == 'salonbooks');
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_CLIENTS'), 'index.php?option=com_salonbook&view=clients',$view == 'clients' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_STAFF'), 'index.php?option=com_salonbook&view=staff',$view == 'staff' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_TIMEOFF'), 'index.php?option=com_salonbook&view=timeoff',$view == 'timeoff' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_TOOLS'), 'index.php?option=com_salonbook&view=tools',$view == 'tools' );

require_once( JPATH_COMPONENT.DS.'controller.php' );
 
// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

// Create the controller
$classname	= 'SalonBooksController'.$controller;
$controller	= new $classname( );

$theTask = JRequest::getCmd('task');
// error_log("using controller: " . $classname . " and task: " . $theTask . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
?>