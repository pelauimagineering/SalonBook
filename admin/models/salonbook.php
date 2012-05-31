<?php
// No direct access to this file
defined('_JEXEC') or die;
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * SalonBook Model
 */
class SalonBooksModelSalonBook extends JModelAdmin
{
 	protected $_data;
 	protected  $_id;
	
	/**
	 * Constructor that retrieves the ID from the request
	 * 
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();
		
		$array = JRequest::getVar('cid', 0, '', 'array');
		$this->setId((int)$array[0]);
	}
	
	/**
	 * Method to set the salonbook identifier
	 * 
	 * @access	public
	 * @param	int Salonbook indentifier
	 * @return	void
	 */
	function setId($id)
	{
		// set id and wipe data
		$this->_id = $id;
		$this->_data = null;
	}
	
	function &getData()
	{
		if ( empty( $this->_data ))
		{
			$query = $this->_db->getQuery(true);
			
			$query->select("COALESCE(U.firstName, U.userName) as clientName, A.*, S.name as stylistName, SERVICES.name as serviceName");
			$query->from('#__salonbook_appointments A join #__salonbook_users U on A.user = U.user_id JOIN #__users S on A.stylist = S.id join #__salonbook_services SERVICES on A.service = SERVICES.id');
			
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		
		if ( !$this->_data)
		{
			$this->_data = $this->getTable();
			$this->_data->client = NULL;
		}
	}
	
	function getListQuery()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
	
		$query->select("COALESCE(U.firstName, U.userName) as clientName, A.*, S.name as stylistName, SERVICES.name as serviceName");
		$query->from('#__salonbook_appointments A join #__salonbook_users U on A.user = U.user_id JOIN #__users S on A.stylist = S.id join #__salonbook_services SERVICES on A.service = SERVICES.id');
		
		return $query;
	}
	
	/**
	 * Method to get an appointment
	 * @return object with data
	 */
	function &getAppointment()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT A.*, A.user as client '.
					' FROM #__salonbook_appointments A '.	
					'  WHERE A.id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = $this->getTable();
			$this->_data->client = NULL;
		}
		return $this->_data;
	}
		
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_salonbook.salonbook', 'salonbook',
		                        array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_salonbook.edit.salonbook.data', array());
		if (empty($data)) 
		{
			$data = $this->getAppointment();
		}
		return $data;
	}
	
	/**
	 * Method to store an appointment record from the Admin panels
	 * 
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		// error_log("inside store() \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$row =& $this->getTable();
		
		// error_log("got a table\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		$data = JRequest::get('form');
		
		// lookup the duration for the selected service and store that in the appointment
		$db = JFactory::getDBO();
		
		$service_id = $data['service'];
		$durationQuery = "select D.durationInMinutes from #__salonbook_services S join #__salonbook_durations D on S.duration = D.id where S.id = $service_id";
		$db->setQuery((string)$durationQuery);
		$db->query();
		$durationInMinutes = $db->loadResult();
		$data['durationInMinutes'] = $durationInMinutes;
		
		// error_log("adding a DURATION of $durationInMinutes to the form data \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		//bind the form data to the table
		if (!$row->bind($data))
		{
			error_log("binding failed! \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// make sure the appointment record is valid
		if ( !$row->check())
		{
			error_log("bind check FAILED \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// store the data
		if ( !$row->store())
		{
			error_log("storing FAILED \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
 		// $to_print = var_export($data, true);
 		// error_log("FORM data:\n" . $to_print . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		// error_log("Saved in salonbook.php. The new appt # is: " . $row->get('id') . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		$this->_data = $row;
		$this->_id = $row->get('id');
		
		return true;
	}
	
	/**
	 * Method to delete appointment(s)
	 *
	 * @access	public
	 * @return	boolean		True on success
	 */
	function delete()
	{
		$cids = JRequest::getVar('cid', array(0), 'post', 'array');
		
		$to_print = var_export($cids, true);
		error_log("trying to DELETE IDs " . $to_print . " \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$row =& $this->getTable();
		
		// create a calendar object
		JLoader::register('SalonBookModelCalendar',  JPATH_COMPONENT_SITE.DS.'models'.DS.'calendar.php');
		$calendarModel = new SalonBookModelCalendar();
		
		JLoader::register('SalonBooksModelUsers', JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'users.php');
		$userModel = new SalonBooksModelUsers();
		
		error_log("looping... \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		foreach($cids as $cid)
		{
			$row->load($cid);
			
			// get the calendar info if needed
			$calendarEventURL = $row->get('calendarEventURL');

			// try to remove the Google Calendar event for this appointment
			if ( $calendarEventURL )
			{
				try {
					// lookup the calendar login info from the users table
					$stylist_id = $row->get('stylist');
					
					$userTable =& $userModel->getTable('users');
					$userTable->load(array('user_id' => $stylist_id));
					
					error_log("trying to delete a calendar event with a URI of: " . $calendarEventURL . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
					$calendarLogin = $userTable->get('calendarLogin');
					$calendarPassword = $userTable->get('calendarPassword');
					
					error_log("calendar name/password: " . $calendarLogin . "/" . $calendarPassword . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
					
					$calendar = $calendarModel->setupCalendarConnection($calendarLogin, $calendarPassword);
					
					error_log("trying to delete a calendar event\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
					$response = $calendarModel->deleteCalendarEntry($calendar, $calendarEventURL);
				}
				catch (Exception $e)
				{
					error_log("Deletion of the Google Calendar event for appointment # " . $cid . " has FAILED\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				}
			}
			
			if ( !$row->delete($cid))
			{
				error_log("Deletion of Appointment # " . $cid . " has FAILED\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				$this->setError( $row->getErrorMsg());
				return false;
			}
			
			error_log("successful deletion of Appointment # " . $cid . " \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
		}
	
		return true;
	}
	
	/*
	*	TODO:
	*	This function properly belongs inside the SalonBookModelStylists class of stylists.php file.
	*	The function IS in fact there right now, but is NOT being linked to properly as we still need to learn
	*	how to handle setting up and calling from multiple data models in a default view/controller in the Admin section.
	*
	*	All of that to say that this function should be identical to the copy over there, then finally removed from this class
	*	once the proper calling structure has been implemented.
	*/
	public function getOptionListOfStylists()
	{
		// show all users between clients (group=2) and SuperAdmin (group=8)
		$queryString = "SELECT U.firstName as name, U.user_id as id FROM `#__salonbook_users` U JOIN `#__user_usergroup_map` G on U.user_id = G.user_id WHERE G.group_id > 2 AND G.group_id < 8";

		$db = JFactory::getDBO();
		$db->setQuery((string)$queryString);
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				$options[] = JHtml::_('select.option', $message->id, $message->name);
			}
		}
		
		return $options;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	public function getOptionListOfServices()
	{
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$servicesQuery = "select S.id, S.name, D.displayName, D.durationInMinutes from #__salonbook_services S join #__salonbook_durations D on S.duration = D.id";
		$db->setQuery((string)$servicesQuery);
		$db->query();
		
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				$toDisplay = $message->name . "&nbsp; &nbsp; &nbsp; &nbsp;" . $message->displayName;
				$options[] = JHtml::_('select.option', $message->id, $toDisplay);
			}
		}

		return $options;
	}

	/**
	 * Return a list of clients for use in the backend management
	 *
	 * @return	string	An HTML option list
	 */
	public function getOptionListOfClients()
	{
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		// show all users between clients (group=2) and SuperAdmin (group=8)
		$clientQuery = "SELECT concat(U.firstName, ' ', U.lastName) as name, U.user_id as id FROM `#__salonbook_users` U JOIN `#__user_usergroup_map` G on U.user_id = G.user_id WHERE G.group_id = 2 ORDER BY U.firstName ASC";
		$db->setQuery((string)$clientQuery);
		$db->query();
		
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message) 
			{
				$options[] = JHtml::_('select.option', $message->id, $message->name);
			}
		}

		return $options;
	}
	
	/**
	 * Return the list of available Status names
	 *
	 * @return	string	An HTML option list
	 */
	public function getOptionListOfStatusNames()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
	
		$statusQuery = "select S.id, S.status from #__salonbook_status S";
		$db->setQuery((string)$statusQuery);
		$db->query();
	
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message)
			{
				$options[] = JHtml::_('select.option', $message->id, $message->status);
			}
		}
	
		return $options;
	}
	
	/**
	 * Return the list of available Status names for Admin Users
	 *
	 * @return	string	An HTML option list
	 */
	public function getOptionListOfStatusNamesForAdmin()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
	
		$statusQuery = "SELECT S.id, S.status FROM #__salonbook_status S WHERE id > 0";
		$db->setQuery((string)$statusQuery);
		$db->query();
	
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message)
			{
				$options[] = JHtml::_('select.option', $message->id, $message->status);
			}
		}
	
		return $options;
	}
	
}