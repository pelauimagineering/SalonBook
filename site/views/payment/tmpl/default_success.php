<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$prettyDate = date("l jS \of F, Y", strtotime($this->theDate));
$prettyTime = date("g:i A", strtotime($this->theTime)); 
?>
<!-- functions -->
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>

<!-- Stylesheets -->
<link rel="stylesheet" href="/components/com_salonbook/salonbook.css" type="text/css" />

<hr/>

<h1>Thank you!</h1>
<br/>
<br/>
<h3>The deposit for your appointment has been accepted.<br/><br/> <?php echo "$this->her_name" ?> is looking forward to seeing you on <?php echo $prettyDate ?> at <?php echo "$prettyTime" ?>.</h3>
<p>A receipt for your purchase has been emailed to you.</p>