<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import joomla controller library
jimport('joomla.application.component.controller');
 
JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );

$view = JRequest::getCmd('view');

JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_APPOINTMENTS'), 'index.php?option=com_salonbook&view=salonbooks',$view == 'salonbooks');
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_CLIENTS'), 'index.php?option=com_salonbook&view=clients',$view == 'clients' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_STAFF'), 'index.php?option=com_salonbook&view=clients',$view == 'clients' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_TOOLS'), 'index.php?option=com_salonbook&view=tools',$view == 'tools' );
JSubMenuHelper::addEntry(JText::_('SALONBOOK_MENU_HELP'), 'index.php?option=com_salonbook&view=clients',$view == 'clients' );

// Get an instance of the controller prefixed by SalonBook
$controller = JController::getInstance('SalonBook');
 
// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
?>