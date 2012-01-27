<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

//get the hosts name
jimport('joomla.environment.uri' );
$host = JURI::root();

$currentPage = JRequest::getInt('currentPage');
$currentItemid = JRequest::getInt('Itemid', 123);
$service_id = JRequest::getInt('service_id');

$site_name = "";
?>
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>
<link rel="stylesheet" href="/components/com_salonbook/salonbook.css" type="text/css" />

<script>
	$(document).ready(function()
	{
		// hide Pay button at startup
		$('#paymentForm').css("display", "none");
		$('#confirmOrder').css("display", "inline");
	});

	function ajxSaveAppointment(apptDate, startTime, stylist_id, service_id)
	{
		$("div#loading").text("Loading...");
		
		if ( service_id < 1 ) 
		{
			service_id = 1;
		}
		
		// invoice
		$.post("index.php", { option:"com_salonbook", task:"addappointment", view: "confirmation", format: "raw", date: apptDate, startTime: startTime, stylist_id:stylist_id, service_id:service_id  },
			function(data)
			{
				// check that data is a positive integer before proceeding
				$("div#loading").text("");
				
				if ( data > 0 )
				{
					$("#invoice").val(data);
					$("#sbkInvoice").val(data);

					returnUrl = "<?php echo $host; ?>index.php?option=com_salonbook&view=payment&task=showpaymentsuccess&xxxVar1=" + data;
					$("#returnUrl").val(returnUrl);
					
					// if everything is ok, show Pay button to allow the Customer to begin the payment process
					$('#paymentForm').css("display", "inline");
					
					//... and hide the confirm button to avoid duplicates
					$('#confirmOrder').css("display", "none");
				}				
		  	}, "text"
		);
	}
	
	function enablePaymentButton()
	{
		// assemble the data needed to add this order to the database
		dateStr = "<?php echo JRequest::getString('selected_date', 1); ?>";
		timeStr = "<?php echo JRequest::getString('selected_startTime', 1); ?>";
		stylist_id = "<?php echo JRequest::getInt('stylist_id'); ?>";
		service_id = "<?php echo JRequest::getInt('service_id'); ?>";
		
		// kick off an AJAX command to save this to the database and retrieve an orderNumber
		// the AJAX response should write the order # directly into the form field to be sent to the payment processor
		ajxSaveAppointment(dateStr, timeStr, stylist_id, service_id);

		$("#scheduledTimeForPayment").val( timeStr + " " + dateStr );

		// setup the payment details to be passed to the payment processor
		productHeader = "Price::Qty::Code::Description::Flags";
		// remove colons from the time before passing on
		timeStr = timeStr.replace(":", " ");
		
		product1 = "15.00::1::001::Appointment Booking at Celebrity Unisex Salon at " + timeStr + " on " + dateStr +"::{TEST}";
		taxes = "1.95::1::tax::13% HST::{TEST}";
		$("#productString").val( productHeader + "|" + product1 + "|" + taxes);
	}
</script>


<div id="loading"></div>
<div id="mainContent">
<hr/>
<h1>Confirm appointment and pay deposit</h1>
<h3>
<?php
	$prettyDay = date("l", strtotime($this->selectedDate));
	$prettyDate = date("F j, Y", strtotime($this->selectedDate));
?>
<i>Day:</i> <?php echo $prettyDay; ?>
</br/>
<i>Date:</i> <?php echo $prettyDate; ?>
<br/>
<i>Time:</i> <?php echo $this->selectedTime; ?>
</h3>
<br/>
<br/>
<form>
	<input type=button value="< Back" id="cancelOrder" onclick="javascript:history.go(-1)" /> &nbsp; &nbsp; <input type=button value="Confirm" id="confirmOrder" onclick="javascript:enablePaymentButton()" />
</form>
</div>

<div id="paymentForm">
	<?php
	/*
	<!-- PRODUCTION 
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="G7WJABTGZ2792">
	<table>
	<tr><td><input type="hidden" name="on0" value="When"></td></tr><tr><td><input type="hidden" name="os0" id="scheduledTimeForPayment" maxlength="200"></td></tr>
	<tr><td><input type="hidden" name="on1" value="orderNumber"></td></tr><tr><td><input type="hidden" name="os1" id="invoice" maxlength="200"></td></tr>
	</table>
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>	
	-->
	
	<!-- DEVELOPMENT -->
	<!-- 
	<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="LP2GCTSKPU3GQ">
	<table>
	<tr><td><input type="hidden" name="on0" value="Time"></td></tr><tr><td><input type="hidden" name="os0" id="scheduledTimeForPayment" maxlength="200"></td></tr>
	<tr><td><input type="hidden" name="on1" value="Invoice"></td></tr><tr><td><input type="hidden" name="os1" id="invoice" maxlength="200"></td></tr>
	</table>
	<input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	-->
	*/
	?>
		
	<!-- InternetSecure -->
	<form action="https://secure.internetsecure.com/process.cgi" method="post"> 
		<input type="hidden" name="GatewayID" value="16079"> 
		<input type="hidden" name="language" value="English"> 
		<input type="hidden" name="ReturnURL" id="returnUrl" value=""> 
		<input type="hidden" name="xxxCancelURL" value="<?php echo $host; ?>index.php?option=com_salonbook&view=payment&task=showpaymentcancelled"> 
		<input type="hidden" name="xxxVar1" id="sbkInvoice">
		
		<input type=hidden name="Products" value="" id="productString">
		
		<input type="hidden" name="on0" value="Time"><input type="hidden" name="os0" id="scheduledTimeForPayment" maxlength="200">
		<input type="hidden" name="on1" value="Invoice"><input type="hidden" name="os1" id="invoice" maxlength="200">
	
		<input type="image" src="/media/com_salonbook/images/pay-now-button.png" name="submit" alt="You can pay securely via most Canadian credit or debit cards" />
	</form>
	
</div>

<!--
cancel
http://celebrity3.pelau.com/index.php?option=com_salonbook&view=payment&task=paymentcancelled

success
http://celebrity3.pelau.com/index.php?option=com_salonbook&view=payment&task=showpaymentresult
-->