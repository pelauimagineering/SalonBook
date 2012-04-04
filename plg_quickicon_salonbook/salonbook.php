<?php
/**
 * @copyright	Copyright (C) 2012 Darren Baptiste
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Salon Book Quick Icon for Joomla! Control Panel
 * Made for Joomla! version 2.5 and later
 *
 */
class plgQuickiconSalonbook extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 *
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Returns an icon definition for an the Salon Book component
	 *
	 * @param  $context  The calling context
	 *
	 * @return array A list of icon definition associative arrays, consisting of the
	 *				 keys link, image, text and access.
	 *
	 * @since       2.5
	 */
	public function onGetIcons($context)
	{
		if ($context != $this->params->get('context', 'mod_quickicon') || !JFactory::getUser()->authorise('core.manage', 'com_salonbook')) {
			return;
		}

		return array(array(
			'link' => 'index.php?option=com_salonbook',
			'image' => JURI::base().'../media/com_salonbook/images/sb_logo_48.png',
			'text' => 'Salon Book',
			'id' => 'plg_quickicon_salonbook'
		));
	}
}
