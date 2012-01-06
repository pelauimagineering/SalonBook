<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of Salonbook component
 */
class com_salonBookInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_salonbook');
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SALONBOOK_UNINSTALL_TEXT') . '</p>';
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SALONBOOK_UPDATE_TEXT') . '</p>';
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_SALONBOOK_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// can we copy this file in before running this script?
		//require_once( JPATH_ROOT .DS. 'administrator'.DS.'components'.DS.'com_salonbook'.DS.'models'.DS.'users.php' );
		
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// we'll do this for all types of installs and updates
		// echo '<p>' . JText::_('COM_SALONBOOK_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
		
		// can we load the 'users' model and run the getCopyUsers() function???
		// $model = new SalonBookModelUsers;
		//$model->get('CopyUsers');
		
		//$model = $this->getModel('users');
		//$model->getCopyUsers();
		// return;
		
		////////////////////////////////////////////////
		// 	If we can't include other classes, then we
		//	must do it the ugly way.
		////////////////////////////////////////////////
		error_log("\ninside postFlight script .. AFTER  copyUsers...\n", 3, "../logs/salonbook.log");
		
		$db = JFactory::getDBO();
		
		$copyUsersQuery = "INSERT IGNORE INTO `#__salonbook_users` (`user_id`, `userName`) SELECT id, COALESCE(name, username) FROM `#__users` ON DUPLICATE KEY UPDATE `completed_parsing` = 1; ";
		$db->setQuery((string)$copyUsersQuery);
		$db->query();
		
		// loop through all entries and parse and update the name field into firstName and lastName
		$updateQuery = "UPDATE `#__salonbook_users` 
							SET firstName =  COALESCE(LEFT(userName, POSITION(' ' IN userName)), userName),
							    lastName = SUBSTRING(userName FROM POSITION(' ' IN userName)),
							    completed_parsing = 1
							WHERE completed_parsing = 0;"; 
		$db->setQuery((string)$updateQuery);
		$db->query();
		
	}
}