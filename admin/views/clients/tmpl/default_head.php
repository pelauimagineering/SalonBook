<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="5">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_ID'); ?>
	</th>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>			
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_CLIENT'); ?>
	</th>
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_PHONE'); ?>
	</th>
	<th width="200">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_NEXT_APPOINTMENT'); ?>
	</th>
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_SERVICE'); ?>
	</th>
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_STYLIST'); ?>
	</th>
</tr>