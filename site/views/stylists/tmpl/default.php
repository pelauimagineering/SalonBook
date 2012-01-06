<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$currentPage = JRequest::getInt('currentPage');
$service_id = JRequest::getInt('service_id');
?>
<!-- functions -->
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>

<!-- Stylesheets -->
<link rel="stylesheet" href="/components/com_salonbook/pages/salon.css" type="text/css" />

<hr/>
<h1>Who is your preferred stylist for this appointment?</h1>

<form action="/index.php?option=com_salonbook&view=timeslots&id=1&Itemid=579" method="POST">
	
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
			echo "<option value='" . $lineItem->value . "'>" . $lineItem->text . "</option>";
		}
	?>
	</select>
	<input type="hidden" name="service_id" id="service_id" value="<?php echo $service_id; ?>" />
<br/>
<br/>
<br/>
<br/>

<!-- form action="/index.php?option=com_salonbook&view=timeslots&id=1&Itemid=579" method="POST" -->
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
</form>
