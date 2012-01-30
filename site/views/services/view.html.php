<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the SalonBook Component
 */
class SalonBookViewServices extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
                // Assign data to the view
                $this->msg = "(services) YAD | " . $this->get('Msg');
 				$this->servicelist = $this->get("ListQuery");
				
 				// load the selected service, if it exists in the model
 				$this->selectedService = $this->appointmentData['service'];
				
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
