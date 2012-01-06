<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the SalonBook Component
 */
class SalonBookViewConfirmation extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
                // Assign data to the view
 				$this->selectedDate = $this->get('SelectedDate');
 				$this->selectedTime = $this->get('SelectedTime');
				
				// get the chosen Stylist's ID (if one)
				// get the timeslot ID
				
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
