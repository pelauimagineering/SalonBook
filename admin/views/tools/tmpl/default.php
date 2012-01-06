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
	<div id="synch_users"><a href="<?php echo JRoute::_('index.php?option=com_salonbook&view=tools&task=synchUsers'); ?>">Synchronize Users</a></div>
	<br/>
	<?php
		if ( $insertCount > 0 || $updateCount > 0)
		{
			echo "(" . $insertCount . ") users were inserted. (" . $updateCount . ") users were updated.<br/>";
		}
	?>
</div>
