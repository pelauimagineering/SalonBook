<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// visual calendar
require_once JPATH_COMPONENT_SITE . DS. 'calendar'.DS.'calendar.php';

$service_id = JRequest::getInt('service_id');
$stylist_id = JRequest::getInt('stylist_id');
?>
<link rel="stylesheet" href="/components/com_salonbook/salonbook.css" type="text/css" />
<link rel="stylesheet" href="<?php echo 'components'.DS.'com_salonbook'.DS.'calendar'.DS.'style.css'?>" type="text/css" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<script type="text/javascript" src="/components/com_salonbook/salonui.js"></script>

<!-- scripts -->
<script>
	function setPreviouslySelectedDateAndTime()
	{
		previousDate = '<?php echo $this->selectedDate ?>';
		previousTime = '<?php echo $this->selectedStartTime ?>';
		
		// trigger the fetching of times for that date
		if ( previousDate )
		{
			calDateSelected(previousDate, previousTime);
		}
	}
	
	function checkToEnableNextButton()
	{
		// if a timeslot has been chosen (i.e. the field is not empty), then enable the NEXT button
		dateSelected = $('#hiddenSelectedDate').val();
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
		if ( 1 == <?php echo empty($this->selectedDate) ? 0 : 1; ?> )
		{
			setPreviouslySelectedDateAndTime();
		}
		else
		{
			$('#stepHeaderTitle').css("visibility", "hidden");
			$('#time_selector_control').css("display", "none");
		}
		
		// disable moving on until something is selected
		checkToEnableNextButton();
				
		// hide the date value that has been properly formatted for the database, but will not be shown to the user in favour of the 'prettyDate' format
		$('.hiddenDate').css("display", "none");
		
		/* handler for the select drop down */ 
		$('.time_selector').change(function()
		{					
			str = $(this).find("option:selected").text();
			
			strVal = $(this).find("option:selected").val();	// timeslot id
			
			dateValue = $('#hiddenSelectedDate').val();
			newDate = Date.parse(dateValue);
			dateName = newDate.toString('dd-mm-yyyy');
			dateName = dateName.substr(0,15);
			
			// output the selected timeslot so the user can easily see what was chosen			
			// if the user doesn't want one of the times shown... disable moving on
			
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
				$('#stepHeaderTitle').css("visibility", "visible");

				$("#selectedTimeslot").text( str + " " + dateName );
				
				// set the values to be passed into the shopping cart
				$("#selected_startTime").val( str );
				$("#selected_date").val( dateName );
				$("#selected_timeslot").val( strVal );
			}

			// allow the user should be able to continue
			checkToEnableNextButton();
					
		});
		
	});
	
	function passthroughData()
	{
		appointment_id = '<?php echo JRequest::getVar("id"); ?>'; 
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

		// clear the data, in case we return to this page, the user will simply have to re-select a date and time
		$('#hiddenSelectedDate').val('');
		checkToEnableNextButton();
		
		return true;
	}

	// highlight the date selected by the user on the calendar, then call the server for a list of timeslots for that day
	function calDateSelected(theDate, theTime)
	{	
		//remove the highlighting class from all calendar elements
		$("div#calendar ul li").removeClass("working");

		// find the correct calendar pane
		calendarDateID = "#li-"+theDate;

		// set the highlight
		$(calendarDateID).addClass("working");
		
		$('#hiddenSelectedDate').val(theDate);
		
		ajxFetchAvailableTimes(theDate, theTime);
	}

	/**
	 *	Used when updating details of an existing appointment
	 */
	function ajxFetchAvailableTimes(theDate, theTime)
	{
		$("div#loading").text("Loading...");
		
		$.post("index.php", { option:"com_salonbook", task:"availabletimes", view: "timeslots", format: "raw", aDate: theDate, aTime: theTime  },
			function(data)
			{
				// return values: an empty string indicates failure
				if ( data == null )
				{
					// no availability
					noAvailabilityMesage = "<?php echo JText::_('COM_SALONBOOK_TIMESLOTS_NO_AVAILABILITY'); ?>";
					$('#mainMessage').text(noAvailabilityMesage);
					$('#mainMessage').css("display", "inline");

					$('div#loading').text("failed");
					$('div#loading').css("display", "inline");
				}
				else
				{
					// remove the old list of selections
					$('#time_selector_control').html('');

					$('div#loading').text("");
					$('#stepHeaderTitle').css("display", "inline");

					
					// display the newly filled select box to the user
					$('#time_selector_control').append(data);
					$('#time_selector_control').css("display", "inline");

					$('.time_selector').trigger('change');
					
					successMesage = "<?php echo JText::_('COM_SALONBOOK_UPDATE_SUCCESS_MESSAGE'); ?>";
					$('#mainMessage').text(successMesage);
					$('#mainMessage').css("display", "inline");
					$('#updateSuccessMessage').css("display", "inline");
				}
		  	}, "text"
		);
		
	}

	/**
	 *	Display a different month on the calendar
	 */
	function ajxShowCalendarMonth(theMonth,theYear)
	{
		// the user can pass in 'next' or 'prev' to view another month
		// a call will be made to the server to fetch the new page in the <div id='calendar'></div> block

		// read the configOptions value to make sure the user is not booking an appointment too far in advance (switch option from weeks to months 1,2,3)
		$.post("index.php", { option:"com_salonbook", task:"showCalendar", view: "timeslots", format: "raw", theMonth: theMonth, theYear: theYear  },
				function(data)
				{
					// return values: an empty string indicates failure
					if ( data == "" )
					{
						// no availability
						noAvailabilityMesage = "<?php echo JText::_('COM_SALONBOOK_TIMESLOTS_NO_AVAILABILITY'); ?>";
						$('#mainMessage').text(noAvailabilityMesage);
						$('#mainMessage').css("display", "inline");

						$('div#loading').text("failed");
						$('div#loading').css("display", "inline");
					}
					else
					{
						$('div#loading').text("");
						$('#stepHeaderTitle').css("visibility", "hidden");
						// if everything is okay display the select box to the user
						$('#calendar').html(data);

						successMesage = "<?php echo JText::_('COM_SALONBOOK_UPDATE_SUCCESS_MESSAGE'); ?>";
						$('#mainMessage').text("some message");
						$('#mainMessage').css("display", "inline");
						$('#updateSuccessMessage').css("display", "inline");
					}
			  	}, "text"
			);
			
	}
</script>

<!-- page content -->
<div id="SalonBookContent">
<h1>Select a date, then an open timeslot</h1> 

<br/>
<form action='/index.php' method='POST' id='sb_main_form' onsubmit='return passthroughData()'>

<?php
	// display calendar
	$calendar = new Calendar();
	$calendar->showToday(true);
	
	$datesArray = array('type'=>array('link'=>array('href'=>'javascript:calDateSelected')));
	echo $calendar->show(null, null, $datesArray);
?>
	<div id="stepHeaderTitle">
		<h2>Choose an open slot</h2>
		<div id="loading"></div>
		<br/>
		<br/>
		<select class='time_selector' name='time_selector' id='time_selector_control'></select>
		
		<div id="displayAreaSelectedTimeslot">
			<h2><span id="selectedTimeslot"></span>&nbsp;&nbsp;
				<input type=submit id="nextButton" value='Next >' class='goToNextPage' name="add">
			</h2>
		</div>
		
	</div>

	<input type="button" value="&lt; Back" class="goBackAPage" id="timeslotBackButton" onclick="javascript:history.go(-1)"> &nbsp; &nbsp; 
	
	<div id="main_display_area">

	
		<input type="hidden" id="hiddenSelectedDate" name="hiddenSelectedDate" value="" />
	
		<input type="hidden" name='currentPage' value='3'>
		
		<input type="hidden" name="product_id" value="8" />
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="Itemid" value="<?php echo JRequest::getVar("Itemid"); ?>" />
		
		<input type="hidden" name="selected_startTime" id="selected_startTime" value="<?php echo $this->selectedStartTime; ?>"/>
		<input type="hidden" name="selected_date" id="selected_date" value="<?php echo $this->selectedDate; ?>"/>
		<input type="hidden" name="selected_timeslot" id="selected_timeslot" value=""/>
	
		<input type="hidden" name="service_id" id="service_id" value="<?php echo $service_id; ?>" />
		<input type="hidden" name="stylist_id" id="stylist_id" value="<?php echo $stylist_id; ?>" />
	
		<input type="hidden" name="option" value="com_salonbook" />
		<input type="hidden" name="controller" value="salonbook" />
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" value="confirmation" />
		<input type="hidden" name="id" id="id" value="<?php echo JRequest::getVar('id'); ?>" />
		<input type="hidden" name="fieldName[0]" id="fieldName0" value="" />
		<input type="hidden" name="fieldValue[0]" id="fieldValue0" value="" />
		<input type="hidden" name="fieldName[1]" id="fieldName1" value="" />
		<input type="hidden" name="fieldValue[1]" id="fieldValue1" value="" />
		<input type="hidden" name="nextViewType" id="nextViewType" value="" />
		<input type="hidden" name="nextViewModel" id="nextViewModel" value="" />	
			
		<?php echo JHtml::_('form.token'); ?>
		
	</div>
</form>
</div>
