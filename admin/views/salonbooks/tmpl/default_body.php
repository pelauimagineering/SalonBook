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
	?>
	<tr class="row<?php echo $i % 2; echo " " . $class; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php echo $item->appointmentDate; ?>
		</td>
		<td>
			<?php echo $item->startTime;
			// if no deposit has been received
			if ( $item->deposit_paid < 1)
			{
				echo "&nbsp; &nbsp; [NO DEPOSIT]";
			}
			?>
		</td>
		<td>
			<?php echo $item->clientName; ?>
		</td>
		<td>
			<?php echo $item->serviceName; ?>
		</td>
		<td>
			<?php $names = explode( " ", $item->stylistName); echo $names[0]; ?>
		</td>
	</tr>
<?php endforeach; ?>


