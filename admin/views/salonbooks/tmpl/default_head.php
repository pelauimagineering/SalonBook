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
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_DATE'); ?>
	</th>
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_TIME'); ?>
		<!-- Start Time -->
	</th>
	<th width="200">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_CLIENT'); ?>
		<!-- Client -->
	</th>
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_SERVICE'); ?>
		<!-- Service -->
	</th>
	<th width="100">
		<?php echo JText::_('COM_SALONBOOK_SALONBOOK_APPOINTMENTS_DETAIL_STYLIST'); ?>
		<!-- Stylist -->
	</th>
</tr>

<?php
?>