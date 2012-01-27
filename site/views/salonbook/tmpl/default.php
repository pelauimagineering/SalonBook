<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$namesArray = split(" ",$this->loggedInUserName);
$firstName = $namesArray[0];
?>

<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<link rel="stylesheet" href="/components/com_salonbook/salonbook.css" type="text/css" />

<script>
	$(document).ready(function(){
		/* attach a submit handler to the form */
		$("#sb_main_form").submit(function(event) {

			/* stop form from submitting normally */
			event.preventDefault(); 

			/* get some values from elements on the page: */
			var $form = $( this ),
				term = $form.find( 'input[name="service_selection"]' ).val(),
				url = $form.attr( 'action' );

			/* Send the data using post and put the results in a div */
			$.post( url, { s: term },
			function( data ) {
				// var content = $( data ).find( '#content' );
				var content = $( data );
				$( "#contentBody" ).empty().append( content );
				}
			);
		});
});
</script>

<hr/>

<form action="/index.php?option=com_salonbook&view=services&id=1&Itemid=579" method="POST">

<?php 
	// $maxBookings should be pulled from an Admin-controllers config option
	$maxBookings = 20;
	
	$resultsArray = $this->appointmentsList;
	if ( count($resultsArray) < $maxBookings )
	{ 			
	?>
		<h2>Ok, <?php echo $firstName; ?>, let's get you started on booking a new appointment.</h2>
		<input type=submit value="Let's get started!"/>
		<br/>
		<br/>
<?php 
	}
	?>
<?php 
	if ( count($resultsArray) > 0 )
	{ 			
		echo "<h2>You may be able to make changes to an existing appointment.</h2>";
		
		foreach ($this->appointmentsList as &$appointment)
		{
			// allow edits if the appointment is more than 2 days from now
			$now = new DateTime();
			$date2 = new DateTime($appointment['appointmentDate']);
			$interval = $now->diff($date2);
			
			if ( $interval->d > 2 )
			{
				$editButton = "<input type='button' name='edit' value='Change' class='apptEditButton_change' />";
			}
			else
			{
				$editButton = "<input type='button' name='edit' value='Upcoming' class='apptEditButton_locked' disabled='disabled' />";
			}
			
			echo "<div class='apptDetail'>" . $editButton . $appointment['serviceName'] . " with " . $appointment['stylistName'] . " on " . date("D M j", strtotime($appointment['appointmentDate']) ) . " at " . date("H:i A",strtotime($appointment['startTime'])) . " </div>";
		}
		
		echo "<br/>";
	}
?>
</form>
