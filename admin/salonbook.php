<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import joomla controller library
jimport('joomla.application.component.controller');

$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'salonbook.xml';
if(file_exists($path)){
	$manifest = simplexml_load_file($path);
	define('SALONBOOK_VERSION', $manifest->version);
	define('SALONBOOK_DATE', $manifest->creationDate);
}else{
	define('SALONBOOK_VERSION', '0.0');
	define('SALONBOOK_DATE', '');
	}

JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );

$view = JRequest::getCmd('view');

JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_APPOINTMENTS'), 'index.php?option=com_salonbook&view=salonbooks',$view == 'salonbooks');
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_CLIENTS'), 'index.php?option=com_salonbook&view=clients',$view == 'clients' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_STAFF'), 'index.php?option=com_salonbook&view=staff',$view == 'staff' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_TIMEOFF'), 'index.php?option=com_salonbook&view=timeoff',$view == 'timeoff' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_TOOLS'), 'index.php?option=com_salonbook&view=tools',$view == 'tools' );
JSubMenuHelper::addEntry('ver. '.SALONBOOK_VERSION);

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

// setup the location for our log files and create a contant to help us call it
define('SALONBOOK_ERROR_LOG', 'com_salonbook.log.'.date('Y_m_d').'.php');
require_once JPATH_COMPONENT_SITE.DS.'salonbookHelperLog.php';
$log = new SalonBookHelperLog();

// Create the controller
$classname	= 'SalonBooksController'.$controller;
$controller	= new $classname( );

$theTask = JRequest::getCmd('task');
// JLog::add("using controller: " . $classname . " and task: " . $theTask);

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
?>