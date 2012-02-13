<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

/**
 * SalonBook Model
 */
class SalonBookModelAppointments extends JModelItem
{
        /**
         * @var string msg
         */
		protected $_appointmentData;
        protected $msg;
 		protected $cbProfile;
		protected $queryGoogleCalendarForAppointments;
		protected $busyTimes;
		protected $lastAppointmentCreated;
		public $rowCount;
		public $aNumber;
		public $depositPaid;
		public $_id;

		/**
		 * Create a new appointment in the system.
		 * When called by the front-end no status will be passed, so the default of 'Waiting for Deposit' will be used.
		 * This will then be updated later, once a payment has been made --OR-- deleted if too much time (admin-configurable) has passed
		 * From the back-end, pass in a status value = 1 to bypass the automatic culling of un-paid appointments
		 *
		 * @param string $date
		 * @param string $startTime
		 * @param int $stylist_id
		 * @param int $service_id
		 * @param int $status = 0
		 * 
		 * @return number appointment_id
		 */
		public function getNewAppointment($date, $startTime, $stylist_id, $service_id, $status = '0')
		{
			$user =& JFactory::getUser();
			
			$db = JFactory::getDBO();
			
			$durationQuery = "select durationInMinutes from #__salonbook_services where id = $service_id";
			$db->setQuery((string)$durationQuery);
			$db->query();
			$durationInMinutes = $db->loadResult();
			
			$convertedTime =  date('H:i:s A', strtotime($startTime));
			$convertedDate =  date('Y-m-d', strtotime($date));
			
			// a status of `1` is 'In Progress'
			// do an insert query
			$insertAppointmentQuery = "INSERT into `#__salonbook_appointments`(appointmentDate, startTime,durationInMinutes,user,deposit_paid,stylist, service, status) values('$convertedDate','$convertedTime',$durationInMinutes,$user->id,0,$stylist_id,$service_id,$status)";
			
			$log_info = "\ninsert SQL: ". $insertAppointmentQuery . " \n";
			error_log($log_info, 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$db->setQuery((string)$insertAppointmentQuery);
			$db->query();
			$this->lastAppointmentCreated = $db->insertid();
			
			return $this->lastAppointmentCreated;
		}
		
		/**
		 * Called by a Paypal script independently of the user clicking the 'Complete' button
		 * @param int $orderNumber
		 * @param string $txn_id
		 */
		public function getMarkAppointmentDepositPaid($orderNumber, $txn_id)
		{
			$db = JFactory::getDBO();
			$updateQuery = "UPDATE `#__salonbook_appointments` SET deposit_paid = '1', payment_id = '$txn_id' WHERE id='$orderNumber'";
			
			error_log($updateQuery."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$db->setQuery((string)$updateQuery);
			$db->query();
			
			$this->rowCount = $db->getAffectedRows();
			return $this->rowCount;
		}

		/**
		 * Called by a Paypal script independently of the user clicking the 'Complete Order' button
		 * 
		 * @param int $orderNumber
		 */
		public function getMarkAppointmentDepositPaidFromInternetSecure($orderNumber)
		{
			$db = JFactory::getDBO();
			$updateQuery = "UPDATE `#__salonbook_appointments` SET deposit_paid = '1' WHERE id='$orderNumber'";
			
			error_log($updateQuery."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$db->setQuery((string)$updateQuery);
			$db->query();
			
			$this->rowCount = $db->getAffectedRows();
			return $this->rowCount;
		}
		
		/** 
		 * Load details about a particular appointment
		 * 
		 * @params	searches the Request Object for 'tx'
		 * @returns an associative array of appointment data
		 */
		public function getAppointmentDetails()
		{
			$txn_id = JRequest::getVar('tx');
			
			$db = JFactory::getDBO();
			$appointmentQuery = "SELECT A.*, U.firstname FROM `#__salonbook_appointments` A join `#__salonbook_users` U ON A.stylist = U.user_id WHERE A.paypal_id LIKE '$txn_id'";
			$db->setQuery((string)$appointmentQuery);
			
			$appointmentData = $db->loadAssocList();
			
			return $appointmentData;
		}
		
		/**
		 * Look up and return details about a specific appointment, by id
		 * 
		 * @param int $id primary key of the #__salonbook_appointments table
		 * @return array appointment data
		 */
		public function getAppointmentDetailsForID($id = 0)
		{
			error_log("looking up details for $id\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			// Load the data
			if (empty( $this->_appointmentData ) && $id != 0 ) 
			{
				$appointmentQuery = "SELECT A.*, concat(STYLIST.firstname,' ',STYLIST.lastname) as stylistName, U.name, STYLIST.firstname, S.name as serviceName, U.email, STYLIST.calendarLogin, STYLIST.calendarPassword FROM `#__salonbook_appointments` A join `#__users` U ON A.user = U.id join `#__salonbook_services` S ON A.service = S.id join `#__salonbook_users` STYLIST on A.stylist = STYLIST.user_id WHERE A.id = $id";
				$this->_db->setQuery( $appointmentQuery );
				$this->_appointmentData = $this->_db->loadAssocList();
			}
			
			if (!$this->_appointmentData) 
			{
				$this->_appointmentData = $this->getTable();
				$this->_appointmentData->client = NULL;
			}
			return $this->_appointmentData;
		}

		/**
		 * Get appointment details
		 * 
		 *	@param: user_id - (int) user of the #__salonbook_appointments table
		 *	@return an associative array with details about the appointment for a given user
		 */
		public function getAppointmentDetailsForUser($user_id = 0)
		{
			if ( $user_id == 0 ) 
			{
				return 0;
			}
			
			error_log("\nlooking up details for user $user_id\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
			// look up the details of the passed in appointment
			$db = JFactory::getDBO();
			$appointmentQuery = "SELECT A.*, U.name, STYLIST.firstname as stylistName, S.name as serviceName, U.email FROM `#__salonbook_appointments` A join `#__users` U ON A.user = U.id join `#__salonbook_services` S ON A.service = S.id join `#__salonbook_users` STYLIST on A.stylist = STYLIST.user_id WHERE A.user = $user_id AND A.status = 1 AND A.deposit_paid = 1 ORDER BY A.appointmentDate ASC";

			$db->setQuery((string)$appointmentQuery);
		
			$appointmentData = $db->loadAssocList();
		
			return $appointmentData;
		}
		
		/**
		 * Method to store an appointment record from the front-end
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function store()
		{
			error_log("inside store \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
		
			$session = JFactory::getSession();
			$data =& $session->get('appointmentData', array(), 'SalonBook');
			
			if ( empty($data) )
			{	
				error_log("no data passed into the store function\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				return false;
			}
			
			$db = JFactory::getDBO();
			$service_id = $data['service'];
			$durationQuery = "select durationInMinutes from #__salonbook_services where id = $service_id";
			$db->setQuery((string)$durationQuery);
			$db->query();
			$durationInMinutes = $db->loadResult();
			
			$data['durationInMinutes'] = $durationInMinutes;
			
			error_log( "Trying to save the following to the database..\n" . var_export($data, true) ."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");

			if ( !$row = $this->getTable('SalonBook') )
			{
				error_log("Did NOT get a table!\n" . $this->_db->getErrorMsg() . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			
			// convert times, if necessary to 24-hour format
			$startTime = $data['startTime'];
			if ( substr_count($startTime, 'm') > 0 )
			{
				$convertedTime = date('H:i', strtotime($startTime));
				$data['startTime'] = $convertedTime;
				error_log("changed from " . $startTime . " to " . $convertedTime, 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			}
			
			error_log("attempting to bind \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			//bind the form data to the table
			//the object model has more fields on it than does the actual db table, so the bind command won't work
			$ignoreList = array('stylistName', 'name', 'firstname', 'serviceName', 'email', 'calendarLogin', 'calendarPassword');
			error_log( "Ignore these fields.. " . var_export($ignoreList, true) ."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			if (!$row->bind($data, $ignoreList))
			{
				error_log("binding failed! \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		
			error_log("attempting to check \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			// make sure the appointment record is valid
			if ( !$row->check())
			{
				error_log("bind check FAILED \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		
			error_log("attempting to store \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			// store the data
			if ( !$row->store())
			{
				error_log("storing FAILED \n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		
			error_log("Save worked. The new appointment # is: " . $row->get('id') . "\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$this->_data = $row;
			$this->_id = $row->get('id');
			$this->depositPaid = $row->get('deposit_paid');
		
			// cleanup old attempts
			$this->removeOldAppointmentsWithoutDeposits();
			
			return true;
		}

		/**
		 * Removing an appointment means to set its status to Cancelled, and remove it from the Google calendar
		 * 
		 * @return int Number of rows that were updated. Success == a positive integer
		 */
		function cancelAppointment($appointment_id = 0)
		{
			$db = JFactory::getDBO();
			$cancelQuery = "UPDATE `#__salonbook_appointments` A set status = ( SELECT S.id FROM `#__salonbook_status` S WHERE status LIKE 'Cancelled' ) WHERE A.id = '$appointment_id'";
			
			error_log($cancelQuery."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			
			$db->setQuery((string)$cancelQuery);
			$db->query();

			$this->rowCount = $db->getAffectedRows();
			return $this->rowCount;
			
		}
		
		/**
		 * Clean up the appointments table by removing appointments still stuck with the 'Waiting for Deposit' status
		 * after the time period specified in the config options.
		 * In this context, 'removal' means setting the status to 'Cancelled'
		 * This can be called every time a time a new appointment is added
		 */
		function removeOldAppointmentsWithoutDeposits()
		{
			$configOptions =& JComponentHelper::getParams('com_salonbook');
			$removalTime = $configOptions->get('remove_unpaid_after_minutes',0);
			
			error_log("Clean up unpaid: removalTime = " . $removalTime."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			if ( $removalTime > 0 )
			{
				$cancelQuery = "UPDATE `#__salonbook_appointments` A set status = ( SELECT S.id FROM `#__salonbook_status` S WHERE status LIKE 'Cancelled' ) WHERE A.status = '0' AND DATEDIFF(now(),A.created_when) > 0";
				
				error_log("Clean up unpaid: " . $cancelQuery."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
				
				$this->_db->setQuery((string)$cancelQuery);
				$this->_db->query();
			}
		}

		public function appointmentsScheduledAhead($numberOfDayAhead = 3)
		{
			$futureDate = date('Y-m-d', strtotime("+$numberOfDayAhead days 00:00"));
			// run a query
			$lookAheadQuery = "SELECT * FROM `#__salonbook_appointments` WHERE appointmentDate = '$futureDate' AND ( deposit_paid = '1' OR status='1' OR (status='0' AND created_by_staff='1') )";
			// return a list
			error_log("Look for future appointments: " . $lookAheadQuery."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			$this->_db->setQuery((string)$lookAheadQuery);
			$this->_db->query();
			
			$appointmentList = $this->_db->loadAssocList();
			error_log("List of appointments: " . $appointmentList."\n", 3, JPATH_ROOT.DS."logs".DS."salonbook.log");
			return $appointmentList;
		}
		
		function detailsForMail($appointment_id)
		{
			$detailQuery = "SELECT A.*, U.email, S.name as serviceName, SU.firstName as stylistFirstName
							FROM #__salonbook_appointments A, #__users U, #__salonbook_services S, #__salonbook_users SU
							WHERE A.user = U.id
							AND A.service = S.id
							AND A.stylist = SU.user_id
							AND A.id > 60
							AND ( deposit_paid = '1' OR status='1' OR (status='0' AND created_by_staff='1') )";
			$this->_db->setquery($detailQuery);
			$this->_db->query();
			
			$details = $this->_db->loadAssocList();
			return $details;
		}
}