<?php
/**
 * Helper class for logging
 */
jimport('joomla.log.log');

class SalonBookHelperLog
{
	function __construct()
	{
		JLog::addLogger(
				array(	'text_file' => SALONBOOK_ERROR_LOG,
						'text_file_path' => JPATH_ROOT.DS."logs" )
		);
	}
	
}