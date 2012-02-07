<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the User-initiated Cancellations
 */
class SalonBookViewCancellation extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
				if ( $this->success == true )
				{
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
