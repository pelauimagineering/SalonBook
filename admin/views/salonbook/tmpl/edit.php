<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_salonbook&layout=edit&id='.(int) $this->item->id); ?>"
      method="post" name="adminForm" id="salonbook-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_SALONBOOK_SALONBOOK_DETAILS' ); ?></legend>
		<ul class="adminformlist">
<?php foreach($this->form->getFieldset() as $field): ?>
			<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="salonbook.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>