<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.plugin.plugin');
 
require_once( JPATH_ROOT .DS. 'administrator'.DS.'components'.DS.'com_salonbook'.DS.'models'.DS.'users.php' );
		
class plgUserSalonbook extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	 function plgUserSalonbook( &$subject )
	 {
	    parent::__construct( $subject );
 
	    // load plugin parameters
	    $this->_plugin = JPluginHelper::getPlugin( 'User', 'Salonbook' );
		// JParameter removed in Joomla 1.7
	    //$this->_params = new JParameter( $this->_plugin->params );
	 }


	/**
	 * Method is called after user data is stored in the database
	 *
	 * @param	array		$user		Holds the new user data.
	 * @param	boolean		$isnew		True if a new user is stored.
	 * @param	boolean		$success	True if user was successfully stored in the database.
	 * @param	string		$msg		Message.
	 *
	 * @return	void
	 * @since	1.6
	 * @throws	Exception on error.
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		error_log("\ninside onUserAfterSave plugin... for user: " . $user['id'] . "\n", 3, "../logs/salonbook.log");

		$app = JFactory::getApplication();
		error_log("\ngot the Application object\n", 3, "../logs/salonbook.log");

		// the user parameters passed to the event:
		// $args = array();
		// $args['username']	= $user['username'];
		// $args['email']		= $user['email'];
		// $args['fullname']	= $user['name'];
		// $args['password']	= $user['password'];

		$uid = $user['id'];
		
		$model = new SalonBooksModelUsers;
		$model->getCopyUsers($uid);
		error_log("ran the copyUsers method now \n", 3, "../logs/salonbook.log");
	}

	/**
	 * Example store user method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data.
	 * @param	boolean		$success	True if user was successfully stored in the database.
	 * @param	string		$msg	Message.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		$app = JFactory::getApplication();

		// only the $user['id'] exists and carries valid information
		$model = new SalonBooksModelUsers;
		$model->getRemoveUser($user['id']);
	}
}
?>