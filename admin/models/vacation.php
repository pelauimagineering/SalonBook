<?php
// No direct access to this file
defined('_JEXEC') or die;
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * SalonBook Model
 */
class SalonBooksModelVacation extends JModelAdmin
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
	 * Method to set the staff worker identifier
	 * 
	 * @access	public
	 * @param	int Staff worker User ID
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
	function &getVacationDetails()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = " SELECT A.*, " .
					" ADDTIME(A.startTime, SEC_TO_TIME(A.durationInMinutes*60)) as returnTime ".
					" FROM #__salonbook_appointments A ".	
					" WHERE A.id = ".$this->_id;
			
			// error_log("Vacation query: " . $query . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = $this->getTable();
			
			// error_log("Vacation data: " . var_export($this->_data, true) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
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
		$form = $this->loadForm('com_salonbook.vacation', 'vacation',
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
			$data = $this->getVacationDetails();
		}
		return $data;
		
		var_dump($data);
	}
	
	/**
	 * Method to store an appointment record from the Admin panels
	 * 
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		$configOptions =& JComponentHelper::getParams('com_salonbook');
		$timeoffUser = $configOptions->get('timeoff_user',0);
		
		error_log("inside vacation->store() \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");

		$data = JRequest::get('form');
		$newData = $data['jform']; 
		$apptID = $newData['id'];
		$apptDate = date("Y-m-d", strtotime($newData['appointmentDate']));
		$user = $timeoffUser;	//$newData['user'];
		$service = $newData['service'];
		$stylist = $newData['stylist'];
		$startTime = date('H:i', strtotime($newData['startTime']));
		$returnTime = date('H:i', strtotime($newData['returnTime']));
		$durationInMinutes = (strtotime($newData['returnTime']) - strtotime($newData['startTime'])) / 60;
		$createdByStaff = true;
		
		error_log("times:start $startTime, return $returnTime, duration $durationInMinutes  \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		if($apptID == 0)
		{
			$query = "INSERT #__salonbook_appointments (created_by_staff, appointmentDate, user, service, startTime, durationInMinutes, stylist) " .
					 "VALUES('$createdByStaff', '$apptDate', '$user', '$service', '$startTime', '$durationInMinutes', '$stylist') ";
		}
		else 
		{
			$query =	"UPDATE #__salonbook_appointments SET " .
						"	created_by_staff = '$createdByStaff', " .
						"	appointmentDate = '$apptDate', " .
						"	user = '$user', " .
						"	service = '$service', " .
						"	startTime = '$startTime', " .
						"	durationInMinutes = '$durationInMinutes', " .
						"	stylist = '$stylist' " .
						"WHERE id = '$apptID' ";
		}

		error_log("Vacation INSERT/UPDATE query: " . $query . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$this->_db->setQuery( $query );
		$result = $this->_db->query();
		$updatedRows = $this->_db->getAffectedRows();
		
		if ( $updatedRows > 0 )
		{
			error_log("Vacation INSERT/UPDATE success\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			return true;
		}
		else
		{
			error_log("Vacation INSERT/UPDATE failure! Row count: $updatedRows AND error message " . $this->_db->getErrorMsg() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			return false;
		}

		return true;
	}
}