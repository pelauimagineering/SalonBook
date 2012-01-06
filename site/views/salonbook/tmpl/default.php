<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$firstName = $this->userCBProfile->firstname;
?>

<script type="text/javascript" src="/components/com_salonbook/jquery-1.6.2.min.js"></script>
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

<h2>Ok, <?php echo $firstName; ?>, let's get you started on booking an appointment.</h2>
<br/>
<br/>
<form action="/index.php?option=com_salonbook&view=services&id=1&Itemid=579" method="POST">
	<input type=submit value="Let's get started!"/>
</form>
