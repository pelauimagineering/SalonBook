<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// $rowCount = JRequest::getInt('rowCount');
// $stylist = $this->stylistName;
// $her_name = $this->aData[0]['firstname'];
// 
// $her_name = JRequest::getVar('her_name');
// print_r($this->aData);

// 
// //$orderNumber = JRequest::getInt('orderNumber');
// $secretValue = "42";
// $model = $this->getModel('appointments');
// $rowCount = $model->getMarkAppointmentDepositPaid($orderNumber, $secretValue);

// kick off an update of the database

$this->her_name = "mary";
$this->theDate = "tomorrow";
$this->theTime = "noon";

?>
<!-- functions -->
<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>

<!-- Stylesheets -->
<link rel="stylesheet" href="/components/com_salonbook/pages/salon.css" type="text/css" />

<hr/>
==== R E M O V E  T H I S ======
<hr/>

<?php
// if ( $rowCount > 0)
if ( $this->paidRowCount > 0)
{
?>
	<h1>Thank you!</h1>
	<br/>
	<br/>
	<h2>The deposit for your appointment has been accepted.<br/><br/> [<?php echo "$this->her_name" ?>] is looking forward to seeing you on [<?php echo "$this->theDate" ?>] at [<?php echo "$this->theTime" ?>].</h2>
<?php
}
else
{
?>
	<h1>Sorry, my friend...</h1>
	<br/>
	<br/>
	<h2>The deposit payment was not processed successfully.</h2><br/>
	<p>No appointment has been booked. Please call us at 416-850-4085 for assistance.</p>
<?php
}

echo "<br/><h1>paidRowCount|".$this->paidRowCount."|</h1>";
?>