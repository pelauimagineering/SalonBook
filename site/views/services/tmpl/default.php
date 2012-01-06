<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$currentPage = JRequest::getInt('currentPage');
?>
<!-- functions -->
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>

<!-- Stylesheets -->
<link rel="stylesheet" href="/components/com_salonbook/pages/salon.css" type="text/css" />

<hr/>
<h1>Select the service you need</h1>
<form action="/index.php?option=com_salonbook&view=stylists&id=1&Itemid=579" method="POST" name="form_services" id="form_services">
	<select id="service_id" name="service_id" class="inputbox"> 
	<?php
		foreach ($this->servicelist as &$lineItem)
		{
			echo "<option value='" . $lineItem->value . "'>" . $lineItem->text . "</option>";
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
</form>

