<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

?>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<form action="index.php" method="post" name="adminForm" id="salonbook-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_SALONBOOK_SALONBOOK_DETAILS' ); ?></legend>
		
		
		<?php 
		foreach($this->form->getFieldset() as $field): 
			echo $field->label;
			echo $field->input;
		endforeach;
		?>
		
	</fieldset>
	<div>
		<input type="hidden" name="option" value="com_salonbook" />
		<input type="hidden" name="controller" value="worker" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="user_id" value="<?php echo $this->worker->user_id; ?>" />
		<input type="hidden" name="view" value="staff" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
