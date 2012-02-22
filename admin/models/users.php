<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * SalonBook Users Model
 */
// class SalonBookModelUsers extends JModelList
class SalonBooksModelUsers extends JModelItem
{
	public $lastUserAdded;
	public $countUsersInserted;
	public $countUsersUpdated;
	
	/**
	 * Method to add a new user to the Salonbook users table
	 *
	 * @return	boolean	success/failure of add process
	 */
	protected function getAddUser($id, $name, $password, $isStaff)
	{
		// use only the id to look up and copy the data between the tables
		error_log("adding the latest site User to Salonbook. ID# " . $id, 3, "../logs/salonbook.log");
		$this->getCopyUsers($id);		
	}
	
	// remove user
	protected function getRemoveUser($id)
	{
		$db = JFactory::getDBO();
		
		$deleteUserQuery = "DELETE `#__salonbook_users` WHERE user_id = $id ";
		$db->setQuery((string)$deleteUserQuery);
		
		$db->query();
	}
	
	// update user
	protected function getUpdateUser($id)
	{
		//
	}

	// copy all current Joomla users (using insert or update) into the salonbook_users table
	// @params	$id	[Optional] id of a single user
	// 			If a valid id was sent, only copy the user specified.
	//			If none was sent, copy all users
	//
	// Modified to always update regardless of the 'completed_parsing' flag
	public function getCopyUsers($id=0)
	{
		error_log("\ninside users->getCopyUsers...\n", 3, "../logs/salonbook.log");
		
		$db = JFactory::getDBO();
		
		$copyUsersQuery = "INSERT IGNORE INTO `#__salonbook_users` (`user_id`, `userName`) SELECT id, COALESCE(name, username) FROM `#__users` ";
		if ( $id > 0 )
		{
			$copyUsersQuery .= "WHERE id = $id ";
		}
		$copyUsersQuery .= "ON DUPLICATE KEY UPDATE `completed_parsing` = 1; ";
		$db->setQuery((string)$copyUsersQuery);
		error_log("\ninside users->getCopyUsers..." . $copyUsersQuery . "\n", 3, "../logs/salonbook.log");
		
		$db->query();
		$this->countUsersInserted = $db->getAffectedRows();
		
		// loop through all entries and parse and update the name field into firstName and lastName
		$updateQuery = "UPDATE `#__salonbook_users` 
							SET firstName =  COALESCE(LEFT(userName, POSITION(' ' IN userName)), userName),
							    lastName = SUBSTRING(userName FROM POSITION(' ' IN userName)),
							    completed_parsing = 1
							WHERE ";
		if ( $id > 0 )
		{
// 			$updateQuery .= " (user_id = $id) AND ";
			$updateQuery .= " (user_id = $id)";
		}

// 		$updateQuery .= " (completed_parsing = 0);"; 
		$db->setQuery((string)$updateQuery);
		error_log("\ninside users->getCopyUsers..." . $updateQuery . "\n", 3, "../logs/salonbook.log");
		
		$db->query();
		$this->countUsersUpdated = $db->getAffectedRows();
		
	}	
}
?>