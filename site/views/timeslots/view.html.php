<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the SalonBook Component
 */
class SalonBookViewTimeslots extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
                // Assign data to the view
 				// $this->availableSlots = $this->get("AvailableSlotsQuery");
 				$this->busySlots = $this->get("BusySlotsQuery");
				
				$this->selectedDate = $this->appointmentData['appointmentDate'];
				$this->selectedStartTime = $this->appointmentData['startTime'];
				
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
