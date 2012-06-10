<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
 
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<script>
var newWindow;
function displayCalendarPopup(url)
{
  newWindow = window.open(url, 'name','height=600,width=800');
  
  if (window.focus)
  {
    newWindow.focus();
  }
}
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
	<table class="adminlist">
		<thead><?php echo $this->loadTemplate('head');?></thead>
		<tbody><?php echo $this->loadTemplate('body');?></tbody>
		<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
		</table>

		<div>
		<input type="hidden" name="option" value="com_salonbook" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="worker" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	
</form>

