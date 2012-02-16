<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
 
// load tooltip behavior
JHtml::_('behavior.tooltip');

// get data from the model
$insertCount = ($this->countUsersInserted > 0) ? $this->countUsersInserted : 0;
$updateCount = ($this->countUsersUpdated > 0) ? $this->countUsersUpdated : 0;

?>
<div class="salonbook_admin_tools">
	<div id="synch_users">
		<a href="<?php echo JRoute::_('index.php?option=com_salonbook&view=tools&task=synchUsers'); ?>">
			<?php echo JText::_('COM_SALONBOOK_SYNCHRONIZE_USERS')?>
		</a>
	</div>
	
	<br/>
	<?php
		if ( $insertCount > 0 || $updateCount > 0)
		{
			echo "(" . $insertCount . ") users were inserted. (" . $updateCount . ") users were updated.<br/>";
		}
	?>
	
	<!--  display link for manual cc processing and refunds -->
	<?php 
	$configOptions =& JComponentHelper::getParams('com_salonbook');
	$manualAddress = $configOptions->get('manual_processing_url','test');
	
	?>
	<div id="process_payment_manually">
		<a href="<?php echo $manualAddress ?>" target="_blank">
			<?php echo JText::_('COM_SALONBOOK_MANUAL_CC_LABEL')?>
		</a>
	</div>
	
	<!--  display link to send reminder emails -->
	<br/>
	<?php 
	$configOptions =& JComponentHelper::getParams('com_salonbook');
	$reminderDaysAhead = $configOptions->get('reminder_email_days_ahead','3');
	
	$reminderForDay = date('l',strtotime("+$reminderDaysAhead days"));
	$reminderEmailURL = "/index.php?option=com_salonbook&view=payment&task=sendReminderEmails";
	?>
	<div id="process_payment_manually">
		<a href="<?php echo $reminderEmailURL ?>" target="_blank">
			<?php echo JText::sprintf('COM_SALONBOOK_MANUAL_REMINDER_EMAIL_LABEL',$reminderDaysAhead, $reminderForDay)?>
		</a>
	</div>

</div>
