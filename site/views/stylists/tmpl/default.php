<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$service_id = $this->appointmentData['service'];

?>
<!-- functions -->
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>
<script>
function passthroughData()
{
	appointment_id = '<?php echo JRequest::getVar("id"); ?>'; 
	$('#id').val(appointment_id);
	
	stylist_id = $('#stylist_id').find("option:selected").val();
	
	$('#fieldName').val('stylist');
	$('#fieldValue').val(stylist_id);
	$('#nextViewType').val('html');
	$('#nextViewModel').val('stylists');
	$('#task').val('updateAppointment');

	return true;
}
</script>
<!-- Stylesheets -->
<link rel="stylesheet" href="/components/com_salonbook/pages/salon.css" type="text/css" />

<hr/>
<h1>Who is your preferred stylist for this appointment?</h1>

<form action="/index.php?Itemid=<?php echo JRequest::getVar('Itemid'); ?>" method="POST" id="sb_main_form" onsubmit="return passthroughData()">
	
	<select id="stylist_id" name="stylist_id" class="inputbox"> 
		<?php 
		// show extra options only when available
		$resultsArray = $this->stylistlist;
		if ( count($resultsArray) > 1 )
		{ ?>
			<!--
			<option value="0">Anyone</option>
			<option value="-1">------</option>
			-->
		<?php
		}
		
		foreach ($this->stylistlist as &$lineItem)
		{
			echo "<option value='" . $lineItem->value . "' ";
			if ( $lineItem->value == $this->selectedStylist )
			{
				echo " selected ";
			}
			echo ">" . $lineItem->text . "</option>";
		}
	?>
	</select>
	<input type="hidden" name="service_id" id="service_id" value="<?php echo $service_id; ?>" />
<br/>
<br/>
<br/>
<br/>

	<table id="navFooterTable">
		<tr>
			<td id="leftColumn">
				<input type=button name="backButton" id="backButton" value="< Back" onclick="javascript:history.back();" />
			</td>
			<td id="rightColumn">
				<input type=submit value="Select a date and time"/>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="option" value="com_salonbook" />
	<input type="hidden" name="controller" value="salonbook" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" value="timeslots" />
	<input type="hidden" name="id" id="id" value="<?php echo JRequest::getVar("id"); ?>" />
	<input type="hidden" name="fieldName" id="fieldName" value="" />
	<input type="hidden" name="fieldValue" id="fieldValue" value="" />
	<input type="hidden" name="nextViewType" id="nextViewType" value="" />
	<input type="hidden" name="nextViewModel" id="nextViewModel" value="" />	
			
	<?php echo JHtml::_('form.token'); ?>
	
</form>
