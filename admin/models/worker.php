<?php
// No direct access to this file
defined('_JEXEC') or die;
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * SalonBook Model
 */
class SalonBooksModelWorker extends JModelAdmin
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
	function &getWorkerDetails()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT A.* '.
					' FROM #__salonbook_users A '.	
					'  WHERE A.user_id = '.$this->_id;
			
			error_log("Worker query: " . $query . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = $this->getTable();
			
			error_log("Worker data: " . var_export($this->_data, true) . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
// 			$this->_data->client = NULL;
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
		$form = $this->loadForm('com_salonbook.worker', 'worker',
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
			$data = $this->getWorkerDetails();
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
		error_log("inside worker->store() \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$data = JRequest::get('form');
		$newData = $data['jform']; 
		
		$query = 	"UPDATE `#__salonbook_users` SET firstName =TRIM('" . $newData['firstName'] . "'), " .
					"lastName=TRIM('" . $newData['lastName'] ."'), " .
					"calendarLogin=TRIM('" . $newData['calendarLogin'] ."'), " .
					"calendarPassword=TRIM('" . $newData['calendarPassword'] ."') " .
					"WHERE id=" . $newData['id'];
		 
		error_log("Worker UPDATE query: " . $query . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
		$this->_db->setQuery( $query );
		$result = $this->_db->query();
		$updatedRows = $this->_db->getAffectedRows();
		
		if ( $updatedRows > 0 )
		{
			error_log("Worker UPDATE success\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			return true;
		}
		else
		{
			error_log("Worker UPDATE failure! Row count: $updatedRows AND error message " . $this->_db->getErrorMsg() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			return false;
		}
	}
}