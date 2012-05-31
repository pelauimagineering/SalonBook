<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');

//echo JRoute::_('index.php?option=com_salonbook&layout=edit&id='.(int) $this->appointment->id); 
?>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<script>
function copyData()
{
	// move the selected date and user to the hidden fields needed to save list data types
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

	timeSelected = $('#jform_startTime').val();
	if ( timeSelected.length > 0 )
	{
		$('#hid_startTime').val(timeSelected);
	}
	
	return true;
}
</script>
<form action="index.php" method="post" name="adminForm" id="salonbook-form" onsubmit="return copyData()">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_SALONBOOK_SALONBOOK_DETAILS' ); ?></legend>
		<ul class="adminformlist">
		<?php
			echo "<input type='hidden' id='hid_startTime' name='startTime' value='" . $this->appointment->startTime . "' />";
		?>
		
<?php foreach($this->form->getFieldset() as $field): ?>
			<li><?php 
			if ( $field->name == "jform[appointmentDate]" )
			{
				echo $field->label;
				echo $this->form->getInput('appointmentDate'); 
				// add a hidden field that will be the source during the actual bind process
				echo "<input type='hidden' id='hid_appointmentDate' name='appointmentDate' value='" . $this->appointment->appointmentDate . "' />";
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
					
					// the default selection for new Appointments made by Staff is 'In Progress' (id=1)
					if ( $this->appointment->id == NULL && $lineItem->value == 1 )
					{
						$selected = "selected";
					}
			
					echo "<option value='" . $lineItem->value . "' " . $selected . " >" . $lineItem->text . "</option>";
				}
				echo "</select>";
			}
			else if ( $field->name == "jform[created_by_staff]" )
			{
				echo $field->label;
				echo "<select id='created_by_staff' name='created_by_staff' class='inputbox'>";
				for ($x=0; $x<2; $x++)
				{
					if ( $x == 0 )
					{
						if ( $this->appointment->id == NULL || $this->appointment->created_by_staff == 1  )
						{
							$selected = "";
						}
						else if ( $this->appointment->created_by_staff == 0 )
						{
							$selected = "selected";
						}
					}
					else
					{
						if ( $this->appointment->id == NULL || $this->appointment->created_by_staff == 1  )
						{
							$selected = "selected";
						}
						else if ( $this->appointment->created_by_staff == 0 )
						{
							$selected = "";
						}
					}
			
 				$textLabel = ($x == 0) ? "Customer" : "Staff";
				echo "<option value='" . $x . "' " . $selected . ">" . $textLabel . "</option>";
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

	
