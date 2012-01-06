<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the SalonBook Component
 */
class SalonBookViewStylists extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        {
                // Assign data to the view
 				$this->stylistlist = $this->get("ListQuery");
				$this->style = $this->get("Hairstyle");
				
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
