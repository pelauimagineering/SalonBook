<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<form action="index.php" method="post" name="adminForm" id="salonbook-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_SALONBOOK_TIMEOFF_DETAILS' ); ?></legend>
		
		
		<?php 
		foreach($this->form->getFieldset() as $field): 
			echo $field->label;
			echo $field->input;
		endforeach;
		?>
		
	</fieldset>
	<div>
		<input type="hidden" name="option" value="com_salonbook" />
		<input type="hidden" name="controller" value="vacation" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo $this->vacation->id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>