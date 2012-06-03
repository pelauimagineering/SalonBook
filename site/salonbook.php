<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import joomla controller library
jimport('joomla.application.component.controller');
// jimport('joomla.error.log');

// setup the location for our log files and create a contant to help us call it
define('SALONBOOK_ERROR_LOG', 'com_salonbook.log.'.date('Y_m_d').'.php');
require_once 'salonbookHelperLog.php';
$log = new SalonBookHelperLog();

// Get an instance of the controller prefixed by SalonBook
$controller = JController::getInstance('SalonBook');
 
// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();

/* Report all errors directly to the screen for simple diagnostics in the dev environment */ 
error_reporting(E_ALL); 
ini_set('display_startup_errors', 1); 
ini_set('display_errors', 1);

?>