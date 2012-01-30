<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$namesArray = split(" ",$this->loggedInUserName);
$firstName = $namesArray[0];

/*
 * // script
 $(document).ready(function(){
 		// attach a submit handler to the form
 		$("#sb_main_form").submit(function(event) {

 				// stop form from submitting normally
 				event.preventDefault();

 				// get some values from elements on the page:
 				var $form = $( this ),
 				term = $form.find( 'input[name="service_selection"]' ).val(),
 				url = $form.attr( 'action' );

 				// Send the data using post and put the results in a div
 				$.post( url, { s: term },
 						function( data ) {
 						// var content = $( data ).find( '#content' );
 						var content = $( data );
 						$( "#contentBody" ).empty().append( content );
 						}
 				);
 				});
 		});
// script
*/

?>

<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<link rel="stylesheet" href="/components/com_salonbook/salonbook.css" type="text/css" />

<script>
	
	function prepareToEdit(appointment_id)
	{
		$('#id').val(appointment_id);
		
		$('#fieldName').val('id');
		$('#fieldValue').val(appointment_id);
		$('#nextViewType').val('html');
		$('#nextViewModel').val('services');
		$('#task').val('updateAppointment');
		
		$("#sb_main_form").submit();
	}
		
</script>


<hr/>

<form action="/index.php?Itemid=<?php echo JRequest::getVar(Itemid); ?>" method="POST" id="sb_main_form">

<?php
	//TODO: $maxBookings should be pulled from an Admin-controllers config option
	$maxBookings = 2;
	
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
			
			if ( $interval->d > 2 && $now < $date2 )
			{
				$editButton = "<input type='button' name='edit' value='Change' onclick='prepareToEdit(" . $appointment['id'] . "); ' class='apptEditButton_change' />";
			}
			else
			{
				$editButton = "<input type='button' name='edit' value='Upcoming' class='apptEditButton_locked' disabled='disabled' />";
			}
			
			echo "<div class='apptDetail'>" . $editButton . $appointment['serviceName'] . " with " . $appointment['stylistName'] . " on " . date("D M j", strtotime($appointment['appointmentDate']) ) . " at " . date("g:i a",strtotime($appointment['startTime'])) . " </div>";
		}
		
		echo "<br/>";
	}
?>

	<input type="hidden" name="option" value="com_salonbook" />
	<input type="hidden" name="controller" value="salonbook" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" value="services" />
	<input type="hidden" name="id" id="id" value="0" />
	
	<input type="hidden" name="fieldName" id="fieldName" value="id" />
	<input type="hidden" name="fieldValue" id="fieldValue" value="" />
	<input type="hidden" name="nextViewType" id="nextViewType" value="" />
	<input type="hidden" name="nextViewModel" id="nextViewModel" value="" />	
	
	<?php echo JHtml::_('form.token'); ?>

</form>
