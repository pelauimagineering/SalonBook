<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$currentPage = JRequest::getInt('currentPage');
$stylist_id = $this->appointmentData[0]['stylist'];
?>
<!-- functions -->
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>
<script>
	function passthroughData()
	{
		appointment_id = '<?php echo JRequest::getVar(id); ?>'; 
		$('#id').val(appointment_id);
		
		service_id = $('#service_id').find("option:selected").val();
		
		$('#fieldName').val('service');
		$('#fieldValue').val(service_id);
		$('#nextViewType').val('html');
		$('#nextViewModel').val('stylists');
		$('#task').val('updateAppointment');

		return true;
	}
</script>

<!-- Stylesheets -->
<link rel="stylesheet" href="/components/com_salonbook/pages/salon.css" type="text/css" />

<hr/>
<h1>Select the service you need</h1>
<form action="/index.php?Itemid=<?php echo JRequest::getVar(Itemid); ?>" method="POST" onsubmit="return passthroughData()" name="form_services" id="sb_main_form">

	<select id="service_id" name="service_id" class="inputbox"> 
	<?php		
		foreach ($this->servicelist as &$lineItem)
		{
			echo "<option value='" . $lineItem->value . "' ";
			if ( $lineItem->value == $this->selectedService )
			{
				echo " selected ";
			}
			echo ">" . $lineItem->text . "</option>";
		}
	?>
	</select>

	<br/>
	<br/>
	<br/>
	<br/>

	<table id="navFooterTable">
		<tr>
			<td id="leftColumn">
				<input type="button" name="backButton" id="backButton" value="< Back" onclick="javascript:history.back();" />
			</td>
			<td id="rightColumn">
				<input type=submit value="Select a stylist"/>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="option" value="com_salonbook" />
	<input type="hidden" name="controller" value="salonbook" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" value="stylists" />
	<input type="hidden" name="id" id="id" value="<?php echo JRequest::getVar(id); ?>" />
	<input type="hidden" name="fieldName" id="fieldName" value="" />
	<input type="hidden" name="fieldValue" id="fieldValue" value="" />
	<input type="hidden" name="nextViewType" id="nextViewType" value="" />
	<input type="hidden" name="nextViewModel" id="nextViewModel" value="" />	
				
</form>

