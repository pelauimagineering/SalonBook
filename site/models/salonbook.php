<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * SalonBook Model
 */
class SalonBookModelSalonBook extends JModelItem
{
        /**
         * @var string msg
         */
        protected $msg;
 		protected $cbProfile;

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
		
        /**
         * Get the message
         * @return string The message to be displayed to the user
         */
        public function getMsg() 
        {
			if (!isset($this->msg)) 
			{
				$id = JRequest::getInt('id', 1);
				// Get a TableSalonBook instance
				$table = $this->getTable();

				// Load the message
				$table->load($id);

				// Assign the message
				$this->msg = $table->displayName;
			}
			return $this->msg;
        }

		/**
		 *	adds the list of time slots used by the passed-in event to a master list for the day
		 */
		/*
		public function parseTimeSlotsFromEvent()
		{
			return "<!-- empty list of time slots -->";
		}
		*/
		
		public function getCBProfile()
		{
			if (!isset($this->cbProfile))
			{
				// find the current users' hairstyle
				$user =& JFactory::getUser();

				// Create a new query object.		
				$this->user_id = $user->id;

				$db = JFactory::getDBO();

				$query = $db->getQuery(true);
				$userPrefsQuery = "select * from #__comprofiler where user_id = $user->id";
				$db->setQuery((string)$userPrefsQuery);
				$this->cbProfile = $db->loadObject();
			}
			return $this->cbProfile;
		}
}