<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

//echo JRoute::_('index.php?option=com_salonbook&layout=edit&id='.(int) $this->appointment->id); 
?>
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script>
function copyData()
{
	// move the selected date and user to the hidden fields needed to save the save
	dateSelected = $('#jform_appointmentDate').val();
	if ( dateSelected.length > 0 )
	{
		$('#hid_appointmentDate').val(dateSelected);
	}

	userSelected = $('#jform_client_id').val();
	if ( userSelected.length > 0 )
	{
		$('#hid_user').val(userSelected);
	}
	
	return true;
}
</script>
<form action="index.php" method="post" name="adminForm" id="salonbook-form" onsubmit="return copyData()">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_SALONBOOK_SALONBOOK_DETAILS' ); ?></legend>
		<ul class="adminformlist">
<?php foreach($this->form->getFieldset() as $field): ?>
			<li><?php 
			if ( $field->name == "jform[appointmentDate]" )
			{
				echo $field->label;
				echo $this->form->getInput('appointmentDate'); 
				// add a hidden field that will be the source during the actual bind process
				echo "<input type='hidden' id='hid_appointmentDate' name='appointmentDate' value='" . $this->appointment->appointmentDate . "' />";
			}
			else if ( $field->name == "jform[startTime]" )
			{
				echo $field->label;
				// format the time as HH:mm
				$startTime = strtotime($this->appointment->startTime);
				echo "<input type='text' name='startTime' value='" . date("H:i", $startTime) . "' />";
			}
			else if ( $field->name == "jform[stylist]" )
			{
				echo $field->label;
				echo "<select id='stylist' name='stylist' class='inputbox'>"; 
				foreach ($this->stylistList as &$lineItem)
				{
					if ( $lineItem->value == $this->appointment->stylist )
					{
						$selected = "selected";
					}
					else
					{
						$selected = "";
					}
					echo "<option value='" . $lineItem->value . "' " . $selected . " >" . $lineItem->text . "</option>";
				}
				echo "</select>";
			} 
			else if ( $field->name == "jform[service]" )
			{
				echo $field->label;
				echo "<select id='service' name='service' class='inputbox'>"; 
				foreach ($this->serviceList as &$lineItem)
				{
					if ( $lineItem->value == $this->appointment->service )
					{
						$selected = "selected";
					}
					else
					{
						$selected = "";
					}
					
					echo "<option value='" . $lineItem->value . "' " . $selected . " >" . $lineItem->text . "</option>";
									}
				echo "</select>";
			}	
			else if ( $field->name == "jform[client]" )
			{
				echo $field->label;
				echo $this->form->getInput('client');
				// add a hidden field that will be the source during the actual bind process
				echo "<input type='hidden' id='hid_user' name='user' value='" . $this->appointment->client . "' />";
				
			}	
			else if ( $field->name == "jform[depositPaid]" )
			{
				echo $field->label;
				echo "<select id='deposit_paid' name='deposit_paid' class='inputbox'>"; 
				for ($x=0; $x<2; $x++)
				{
					if ( $this->appointment->deposit_paid == 1 && $x==1)
					{
						$selected = "selected";
					}
					else
					{
						$selected = "";
					}
					
					$textLabel = ($x == 0) ? "NO" : "YES";
					echo "<option value='" . $x . "' " . $selected . ">" . $textLabel . "</option>";
				}
				echo "</select>";
			}
			else if ( $field->name == "jform[status]" )
			{
				echo $field->label;
				echo "<select id='status' name='status' class='inputbox'>";
				foreach ($this->statusList as &$lineItem)
				{
					if ( $lineItem->value == $this->appointment->status )
					{
						$selected = "selected";
					}
					else
					{
						$selected = "";
					}
			
					echo "<option value='" . $lineItem->value . "' " . $selected . " >" . $lineItem->text . "</option>";
				}
				echo "</select>";
			}
			else
			{
				echo $field->label;
				echo $field->input;
			}
			?></li>
<?php endforeach; ?>
		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="option" value="com_salonbook" />
		<input type="hidden" name="controller" value="salonbook" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo $this->appointment->id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

	
