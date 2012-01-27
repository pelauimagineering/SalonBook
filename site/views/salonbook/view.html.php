<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'users.php');
require_once (JPATH_COMPONENT_SITE.DS.'models'.DS.'appointments.php');

/**
 * HTML View class for the SalonBook Component
 */
class SalonBookViewSalonBook extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
            // Assign data to the view
			$user =& JFactory::getUser();
			$user_name = $user->name;
			$this->loggedInUserName = $user_name;
			
			// load more data models
			JLoader::register('SalonBookModelAppointments',  JPATH_COMPONENT_SITE.'/models/appointments.php');
			$appointmentModel = new SalonBookModelAppointments();
			$user_id = $user->id;
			$appointmentData = $appointmentModel->getAppointmentDetailsForUser($user_id);
			$this->appointmentsList = $appointmentData;
			
             // Check for errors.
             if (count($errors = $this->get('Errors'))) 
             {
             	JError::raiseError(500, implode('<br />', $errors));
                return false;
             }

             // Display the view
             parent::display($tpl);
        }
}