<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): ?>
	<?php
	if ( $item->deposit_paid < 1)
	{
		$class = "unpaid";
	}
	else
	{
		$class = "";
	}	
	
	$link = JRoute::_( 'index.php?option=com_salonbook&view=salonbook&layout=edit&task=edit&cid[]='. $item->id );
	?>
	<tr class="row<?php echo $i % 2; echo " " . $class; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); // creates a checkbox ?>
		</td>
		<td>
			<a href="<?php echo $link; ?>"><?php echo $item->appointmentDate; ?></a>
		</td>
		<td>
			<?php 
				$startTime = strtotime($item->startTime);
				echo date("H:i", $startTime);
				// if no deposit has been received
				if ( $item->deposit_paid < 1)
				{
					echo "&nbsp; &nbsp; [NO DEPOSIT]";
				}
			?>
		</td>
		<td>
			<?php echo $item->clientFullName; ?>
		</td>
		<td>
			<?php echo $item->serviceName; ?>
		</td>
		<td>
			<?php $names = explode( " ", $item->stylistName); echo $names[0]; ?>
		</td>
		<td>
			<?php echo $item->status; ?>
		</td>
		</tr>
<?php endforeach; ?>


