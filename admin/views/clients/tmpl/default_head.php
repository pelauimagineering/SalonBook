<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// for each client we want 0) full name 1) total # of bookings, 2) date and time of next booking 3) next service 4) stylist name

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