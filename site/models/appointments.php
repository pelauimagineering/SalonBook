<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * SalonBook Model
 */
class SalonBookModelAppointments extends JModelItem
{
        /**
         * @var string msg
         */
        protected $msg;
 		protected $cbProfile;
		protected $queryGoogleCalendarForAppointments;
		protected $busyTimes;
		protected $lastAppointmentCreated;
		public $rowCount;
		public $aNumber;
		
		/**
		 * Returns a reference to the SalonBook Table object, always creating it.
		 *
		 * @param	type	The table type to instantiate
		 * @param	string	A prefix for the table class name. Optional.
		 * @param	array	Configuration array for model. Optional.
		 * @return	JTable	A database object
		 * @since	1.6
		 */
		public function getTable($type = 'SalonBook', $prefix = 'SalonBookTable', $config = array()) 
		{
			return JTable::getInstance($type, $prefix, $config);
		}
		
		public function getNewAppointment($date, $startTime, $stylist_id, $service_id)
		{
			$user =& JFactory::getUser();
			
			$db = JFactory::getDBO();
			
			$durationQuery = "select durationInMinutes from #__salonbook_services where id = $service_id";
			$db->setQuery((string)$durationQuery);
			$db->query();
			$durationInMinutes = $db->loadResult();	//37
			
			$convertedTime =  date('H:i:s A', strtotime($startTime));
			$convertedDate =  date('Y-m-d', strtotime($date));
			
			// a status of `1` is 'In Progress'
			// do an insert query
			$insertAppointmentQuery = "INSERT into `#__salonbook_appointments`(appointmentDate, startTime,durationInMinutes,user,deposit_paid,stylist, service, status) values('$convertedDate','$convertedTime',$durationInMinutes,$user->id,0,$stylist_id,$service_id,1)";
			
			$log_info = "\ninsert SQL: ". $insertAppointmentQuery . " \n";
			error_log($log_info, 3, "logs/salonbook.log");
			
			$db->setQuery((string)$insertAppointmentQuery);
			$db->query();
			$this->lastAppointmentCreated = $db->insertid();
			
			return $this->lastAppointmentCreated;
		}
		
		public function getMarkAppointmentDepositPaid($orderNumber, $txn_id)
		{
			$db = JFactory::getDBO();
			$updateQuery = "UPDATE `#__salonbook_appointments` SET deposit_paid = '1', paypal_id = '$txn_id' WHERE id='$orderNumber'";
			
			error_log($updateQuery."\n", 3, "logs/salonbook.log");
			
			$db->setQuery((string)$updateQuery);
			$db->query();
			
			$this->rowCount = $db->getAffectedRows();
			return $this->rowCount;
		}

		public function getMarkAppointmentDepositPaidFromInternetSecure($orderNumber)
		{
			$db = JFactory::getDBO();
			$updateQuery = "UPDATE `#__salonbook_appointments` SET deposit_paid = '1' WHERE id='$orderNumber'";
			
			error_log($updateQuery."\n", 3, "logs/salonbook.log");
			
			$db->setQuery((string)$updateQuery);
			$db->query();
			
			$this->rowCount = $db->getAffectedRows();
			return $this->rowCount;
		}
		
		// returns an associative array of details about a particular appointment
		public function getAppointmentDetails()
		{
			// $orderNumber = JRequest::getInt('invoice', 0);
			$txn_id = JRequest::getVar('tx');
			
			$db = JFactory::getDBO();
			$appointmentQuery = "SELECT A.*, U.firstname FROM `#__salonbook_appointments` A join `#__salonbook_users` U ON A.stylist = U.user_id WHERE A.paypal_id LIKE '$txn_id'";
			$db->setQuery((string)$appointmentQuery);
			
			$appointmentData = $db->loadAssocList();
			
			return $appointmentData;
		}
		
		// @return an associative array with details about the appointment for a given id
		// @params: id - (int) primary key of the #__salonbook_appointments table
		public function getAppointmentDetailsForID($id = 0)
		{
			error_log("\nlooking up details for $id\n", 3, "logs/salonbook.log");
			
			// look up the details of the passed in appointment
			$db = JFactory::getDBO();
			$appointmentQuery = "SELECT A.*, concat(STYLIST.firstname,' ',STYLIST.lastname) as stylistName, U.name, STYLIST.firstname, S.name as serviceName, U.email, STYLIST.calendarLogin, STYLIST.calendarPassword FROM `#__salonbook_appointments` A join `#__users` U ON A.user = U.id join `#__salonbook_services` S ON A.service = S.id join `#__salonbook_users` STYLIST on A.stylist = STYLIST.user_id WHERE A.id = $id";
			
			error_log("Using this query: $appointmentQuery \n", 3, "logs/salonbook.log");
			
			$db->setQuery((string)$appointmentQuery);

			$appointmentData = $db->loadAssocList();	

			return $appointmentData;
		}
		
}