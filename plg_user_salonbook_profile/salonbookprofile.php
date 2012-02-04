<?php
 /**
  * @version		
  * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
  * @license		GNU General Public License version 2 or later; see LICENSE.txt
  */
 
 defined('JPATH_BASE') or die;
 
  /**
   * An custom profile plugin for the Salonbook
   *
   * @package		Joomla.Plugins
   * @subpackage	user.profile
   * @version		1.6
   */
  class plgUserSalonbookProfile extends JPlugin
  {
	/**
	 * @param	string	The context for the data
	 * @param	int		The user id
	 * @param	object
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile')))
		{
			return true;
		}
 
		$userId = isset($data->id) ? $data->id : 0;
 
		// Load the profile data from the database.
		$db = &JFactory::getDbo();
		$db->setQuery(
			'SELECT profile_key, profile_value FROM #__user_profiles' .
			' WHERE user_id = '.(int) $userId .
			' AND profile_key LIKE \'salonbookprofile.%\'' .
			' ORDER BY ordering'
		);
		$results = $db->loadRowList();
 
		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}
 
		// Merge the profile data.
		$data->salonbookprofile = array();
		foreach ($results as $v) {
			$k = str_replace('salonbookprofile.', '', $v[0]);
			$data->salonbookprofile[$k] = $v[1];
		}
 
		return true;
	}
 
	/**
	 * @param	JForm	The form to be altered.
	 * @param	array	The associated data for the form.
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		// Load user_profile plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_user_salonbookprofile', JPATH_ADMINISTRATOR);
 
		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}
		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_users.profile', 'com_users.registration','com_users.user','com_admin.profile'))) {
			return true;
		}
		if ($form->getName()=='com_users.profile')
		{
			// Add the profile fields to the form.
			JForm::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('profile', false);
 
			// Toggle whether each field is required in the backend
			if ($this->params->get('profile-require_phone_mobile', 1) > 0) {
				$form->setFieldAttribute('phone_mobile', 'required', $this->params->get('profile-require_phone_mobile') == 2, 'salonbookprofile');
			} else {
				$form->removeField('phone_mobile', 'salonbookprofile');
			}
			
			if ($this->params->get('profile-require_postalcode', 1) > 0) {
				$form->setFieldAttribute('postalcode', 'required', $this->params->get('profile-require_postalcode') == 2, 'salonbookprofile');
			} else {
				$form->removeField('postalcode', 'salonbookprofile');
			}
			
			if ($this->params->get('profile-require_gender', 1) > 0) {
				$form->setFieldAttribute('gender', 'required', $this->params->get('profile-require_gender') == 2, 'salonbookprofile');
			} else {
				$form->removeField('gender', 'salonbookprofile');
			}
			
		}
 
		// Toggle whether each field is required in the registration or front-end
		elseif ($form->getName()=='com_users.registration' || $form->getName()=='com_users.user' )
		{		
			// Add the registration fields to the form.
			JForm::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('profile', false);
 
			// Toggle whether each field is required in the backend
			if ($this->params->get('register-require_phone_mobile', 1) > 0) {
				$form->setFieldAttribute('phone_mobile', 'required', $this->params->get('profile-require_phone_mobile') == 2, 'salonbookprofile');
			} else {
				$form->removeField('phone_mobile', 'salonbookprofile');
			}

			if ($this->params->get('register-require_postalcode', 1) > 0) {
				$form->setFieldAttribute('postalcode', 'required', $this->params->get('profile-require_postalcode') == 2, 'salonbookprofile');
			} else {
				$form->removeField('postalcode', 'salonbookprofile');
			}
		
			if ($this->params->get('register-require_gender', 1) > 0) {
				$form->setFieldAttribute('gender', 'required', $this->params->get('profile-require_gender') == 2, 'salonbookprofile');
			} else {
				$form->removeField('gender', 'salonbookprofile');
			}
		}			
	}
 
	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');
 
		if ($userId && $result && isset($data['salonbookprofile']) && (count($data['salonbookprofile'])))
		{
			try
			{
				$db = &JFactory::getDbo();
				$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId.' AND profile_key LIKE \'salonbookprofile.%\'');
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
 
				$tuples = array();
				$order	= 1;
				foreach ($data['salonbookprofile'] as $k => $v) {
					$tuples[] = '('.$userId.', '.$db->quote('salonbookprofile.'.$k).', '.$db->quote($v).', '.$order++.')';
				}
 
				$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e) {
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
 
		return true;
	}
 
	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}
 
		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');
 
		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'salonbookprofile.%'"
				);
 
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
 
		return true;
	}
 
 
 }
 ?>