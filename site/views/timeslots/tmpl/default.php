<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$currentPage = JRequest::getInt('currentPage');
$service_id = JRequest::getInt('service_id');
$stylist_id = JRequest::getInt('stylist_id');
?>
<link rel="stylesheet" href="/components/com_salonbook/salonbook.css" type="text/css" />

<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>

<!-- scripts -->
<script>
	function checkToEnableNextButton()
	{
		// if a timeslot has been chosen (i.e. the field is not empty), then enable the NEXT button
		dateSelected = $("#selected_startTime").val() + $("#selected_date").val() + $("#selected_timeslot").val();
		if ( dateSelected.length > 0 )
		{
			$('#nextButton').attr("disabled", false);
			$("#displayAreaSelectedTimeslot").css("display", "inline");
			
		}
		else
		{
			$('#nextButton').attr("disabled", true);
			$("#displayAreaSelectedTimeslot").css("display", "none");
		}
		
	}
	
	$(document).ready(function()
	{
		// disable moving on until something is selected
		checkToEnableNextButton();
		
		// hide the date value that has been properly formatted for the database, but will not be shown to the user in favour of the 'prettyDate' format
		$('.hiddenDate').css("display", "none");
		
		/* attach a submit handler to the form */
		$("#form_page01").submit(function(event) {

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

		/* handler for the select drop down */ 
		$('.time_selector').change(function()
		{			
			str = $(this).find("option:selected").text();
			
			strVal = $(this).find("option:selected").val();	// timeslot id
			
			// read the date from a div area with a matching id
			strTimestamp = this.id;
			
			dateSelector = 'date_selector_'+strTimestamp;
			dateValue = $('span[id="'+dateSelector+'"]').text();
			newDate = Date.parse(dateValue);
			dateName = newDate.toString('dd-mm-yyyy');
			dateName = dateName.substr(0,15);
			
			// output the selected timeslot so the user can easily see what was chosen			
			// if the user doesn't want one of the times shown... show nothing, and disable moving on
			
			if ( str.indexOf("--") >= 0 )
			{
				// hide the Next button and selected timeslot
				$("#displayAreaSelectedTimeslot").css("display", "none");
				
				$("#selectedTimeslot").text('');
				
				// clear these values in case they are accidentally passed into the shopping cart
				$("#selected_startTime").val( '' );
				$("#selected_date").val( '' );
				$("#selected_timeslot").val( '' );
			}
			else
			{
				// show the Next button and selected timeslot
				$("#displayAreaSelectedTimeslot").css("display", "inline");

				$("#selectedTimeslot").text( str + " " + dateName );
				
				// set the values to be passed into the shopping cart
				$("#selected_startTime").val( str );
				$("#selected_date").val( dateName );
				$("#selected_timeslot").val( strVal );
			}

			// copy to the Paypal button
			// $("#scheduledTimeForPayment").val( str + " " + dateName );
			
			// reset all of the others
			$('.time_selector').not('[id="' + strTimestamp + '"]').find("option[value='-1']").attr('selected', true);
			
			// test to see if the user should be able to continue
			checkToEnableNextButton();			
		});
		
		
	});
	
	function passthroughData()
	{
		appointment_id = '<?php echo JRequest::getVar(id); ?>'; 
		$('#id').val(appointment_id);

		// set the (newly?) selected date and time
		newTime = $('#selected_startTime').val();
		newDate = $('#selected_date').val();
		newParsedDate = Date.parse(newDate);
		formattedDate = newParsedDate.getFullYear() + "-" + (newParsedDate.getMonth() + 1) + "-" + newParsedDate.getDate();
	
		$('#fieldName0').val('appointmentDate');
		$('#fieldValue0').val(formattedDate);
		
		$('#fieldName1').val('startTime');
		$('#fieldValue1').val(newTime);
		
		$('#nextViewType').val('html');
		$('#nextViewModel').val('confirmation');
		$('#task').val('updateAppointment');

		return true;
	}
		
</script>

<!-- page content -->
<hr/>
<?php
echo "<form action='/index.php?Itemid=" . JRequest::getVar(Itemid) . "' method='POST' id='sb_main_form' onsubmit='return passthroughData()'>";

?>
<div id="stepHeaderTitle">
<h1>Choose an open time slot</h1>
</div>

<?php
	// show the next 7 days as the default, but allow the option of seeing more

	// Array: dailySlotsAvailable
	$dailySlotsAvailable = array();
	// read start-of-day and end-of-day times from the configuration file 
	// 8:00 AM = 16 , 7:00 PM = 38
	$firstSlot = 16;
	$lastSlot = 38;

	for ($slotPosition=$firstSlot; $slotPosition <= $lastSlot; $slotPosition++)
	{
		$dailySlotsAvailable[] = $slotPosition;
	}
	
	function slotNumber2Time ($slotNumber)
	{
		$theHour = intval($slotNumber / 2);
		if ( $theHour == $slotNumber / 2 )
		{
			$theMinute = "00";
		}
		else
		{
			$theMinute = "30";
		}
		
		if ($theHour > 12)
		{
			$theHour -= 12;
		}
		
		return $theHour . ":" . $theMinute;
	}
	
	
	// function: timeSlotsUsedByEvent
	// @params: calendar Event
	// @return: array of time slot numbers (0=12:00 - 12:30AM, 1= 12:30 AM - 1:00 AM)
	function timeSlotsUsedByEvent ($anEvent)
	{
		error_log("\n anEvent: " . $anEvent->startTime . "\n", 3, "../logs/salonbook.log");
		
		// open up the event to look at the startTime -> endTime to calculate timeslots used
		
		$theStart =  strtotime($anEvent->startTime);
		error_log("\n startTime of anEvent is " . $theStart . "\n", 3, "../logs/salonbook.log");
		
		$minutes = idate('H', $theStart) * 60;
		$minutes += idate('i', $theStart);
		$startSlotNumber = intval($minutes / 30);
		
		$duration = $anEvent->durationInMinutes;
		$theEnd = strtotime("+ $duration minutes", $theStart);
		
		error_log("\n endTime of anEvent is " . $theEnd . "\n", 3, "../logs/salonbook.log");
		
		$minutes = idate('H', $theEnd) * 60;
		$minutes += idate('i', $theEnd);
		// calculate the end time as if they finished a minute earlier
		// this allows an appointment from 2:00 to 2:30 to appear as (29 minutes) so it only occupies a single timeslot
		$endSlotNumber = intval(($minutes - 1) / 30);	
		
		// calculate all of the slots used for this appointment. It will always be a simple sequence of integers from the first to the last slot
		for ( $newSlot=$startSlotNumber; $newSlot <= $endSlotNumber; $newSlot++)
		{
			$slotsArray[] = $newSlot;
		}
	
		return $slotsArray;
	}
		
?>



<div id="main_display_area">
<?php

echo "<table border='10' colpadding='10' class='availabilityTable'>";
echo "<tr><th>Date</th><th>Choose a start time</th></tr>";

$start = strtotime('+1 day 00:00');
$end = strtotime('+8 days 23:59');
$currentDate = $start; 

while($currentDate < $end) 
{ 
	echo "<tr>";
	echo "<td>";
	
	// for each day, find all events
	// use $this->busySlots; then remove from the $dailySlotsAvailable array
	
	//$feed = $this->availableSlots;
	$feed = $this->busySlots;
	error_log("the busy feed has " . count($feed) . " items \n", 3, "logs/salonbook.log");
	$dailyResults = $feed;
	// echo " : " . count($dailyResults) > 0 ? " *" : " ";
	
	// set up the default available time slots
	$slotsOpenForBookingToday = $dailySlotsAvailable;
	$dailyUsedSlots = array();
		
	// if events were found, then caluate the timeslots used by each,
	// then calculate the available slots, and have them ready for display to the user
	if ( count($feed) > 0 )
	{
	    foreach ($feed as $event) 
		{
			// $id = substr($event->id, strrpos($event->id, '/')+1);
			$id = $event->id;
			// error_log("we found an appointment with ID $id in the feed \n", 3, "../logs/salonbook.log");
		
			// check that the event is for the currentDate
			if ( $event->appointmentDate == date('Y-m-d', $currentDate) )
			{
				// error_log("we found an event with a matching date: " . $event->appointmentDate . "\n", 3, "../logs/salonbook.log");
				
				// process each event looking for timeslots used
				$usedSlots = timeSlotsUsedByEvent( $event );				
			}
			else
			{
				// nothing was found 
				$usedSlots = array();
			}
		
			$dailyUsedSlots = array_merge($dailyUsedSlots, $usedSlots);
	    }
	}
	
	$slotsOpenForBookingToday = array_diff($dailySlotsAvailable, $dailyUsedSlots);
	// highlight dates with LIMITED availability
	$prettyDate = date("D M j", $currentDate);
	$thisDate = date("Y-m-j", $currentDate);
	echo "<span class='hiddenDate' id='date_selector_$currentDate'>$thisDate</span>";
	if ( count($dailyUsedSlots) > 0 )
	{
		echo "&nbsp;&nbsp;<b>" . $prettyDate . "</b>&nbsp;&nbsp;";
	}
	else
	{
		echo $prettyDate;
	}

	echo "</td>";
	echo "<td>";

	// now print a list of all available slots for that day
	// Choose a start time but only if there are indeed times availabe for that day, else show a 'Sorry..' message
	echo "<select class='time_selector' name='time_selector_$currentDate' id='$currentDate'>"; 
	echo "<option value='-1' name='-1'> -- </option>";
	foreach ($slotsOpenForBookingToday as $slotNumber)
	{
		$slotTime = slotNumber2Time($slotNumber);
		$ampm = ($slotNumber < 24) ? "am" : "pm";
		echo "<option value='$slotNumber' name='$slotNumber' ";
		$displayTime = $slotTime . ' ' . $ampm;
		$selectedTime = date('g:i a', strtotime($this->selectedStartTime));
		if ( $displayTime == $selectedTime && $thisDate == date("Y-m-j", strtotime($this->selectedDate)) )
		{
			echo " selected ";
		}
		echo ">$slotTime $ampm</option>";
		
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	
	// move on to the next date
	$currentDate = strtotime("+1 day", $currentDate);
   
}

echo "</table>";
?>
	<!-- 
		<tr><td colspan=2><input type='button' value='See more dates' disabled='disabled'></td></tr>
	-->


	<input type="hidden" name='currentPage' value='3'>
	<input type="button" value='< Back' class='goBackAPage' onclick="javascript:history.go(-1)"> &nbsp; &nbsp; 
	<!--input type=submit id="nextButton" value='Next >' class='goToNextPage' name="add"-->
	
	<input type="hidden" name="product_id" value="8" />
	<input type="hidden" name="add" value="1"/>
	<input type="hidden" name="ctrl" value="product"/>
	<input type="hidden" name="task" value="updatecart"/>
	
	<input type="hidden" name="selected_startTime" id="selected_startTime" value="<?php echo $this->selectedStartTime; ?>"/>
	<input type="hidden" name="selected_date" id="selected_date" value="<?php echo $this->selectedDate; ?>"/>
	<input type="hidden" name="selected_timeslot" id="selected_timeslot" value=""/>

	<input type="hidden" name="service_id" id="service_id" value="<?php echo $service_id; ?>" />
	<input type="hidden" name="stylist_id" id="stylist_id" value="<?php echo $stylist_id; ?>" />

	<input type="hidden" name="option" value="com_salonbook" />
	<input type="hidden" name="controller" value="salonbook" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="view" value="confirmation" />
	<input type="hidden" name="id" id="id" value="<?php echo JRequest::getVar(id); ?>" />
	<input type="hidden" name="fieldName[0]" id="fieldName0" value="" />
	<input type="hidden" name="fieldValue[0]" id="fieldValue0" value="" />
	<input type="hidden" name="fieldName[1]" id="fieldName1" value="" />
	<input type="hidden" name="fieldValue[1]" id="fieldValue1" value="" />
	<input type="hidden" name="nextViewType" id="nextViewType" value="" />
	<input type="hidden" name="nextViewModel" id="nextViewModel" value="" />	
		
	<?php echo JHtml::_('form.token'); ?>
	
</div>


<div id="displayAreaSelectedTimeslot">
	<h1><span id="selectedTimeslot"></span>&nbsp;&nbsp;
			<input type=submit id="nextButton" value='Next >' class='goToNextPage' name="add">
	</h1>
</div>

<?php echo "</form>" ?>
