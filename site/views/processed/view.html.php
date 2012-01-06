<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the SalonBook Component
 */
class SalonBookViewProcessed extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
				// after we receive a callback from the payment processor (PayPal or InternetSecure), new data will be set here directly..
				
				if ( $this->paid > 0 )
				{
					$this->stylist_name = $this->appointmentData[0]['firstname'];
					$this->theDate = $this->appointmentData[0]['appointmentDate'];
					$this->theTime = $this->appointmentData[0]['startTime'];
					$this->invoice = $this->appointmentData[0]['id'];
					
					// Display the view
	                parent::display('success');
	                
				}
				else
				{
					parent::display('failure');	                
				}
				
                // Check for errors.
                if (count($errors = $this->get('Errors'))) 
                {
                        JError::raiseError(500, implode('<br />', $errors));
                        return false;
                }

        }
}
