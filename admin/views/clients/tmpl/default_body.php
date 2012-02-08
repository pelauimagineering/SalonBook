<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php 
foreach($this->items as $i => $item): 
	$link = JRoute::_( 'index.php?option=com_salonbook&view=salonbook&layout=edit&task=edit&cid[]='. $item->id );
?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php echo $item->clientName ?>
		</td>
		<td>
			<?php echo $item->phoneNumber ?>
		</td>
		<td>
			<a href='<?php echo $link ?>'>
				<span class="backendDateTime"><?php echo  date("D m d", strtotime($item->appointmentDate)) . " &nbsp; &nbsp; " . date("h:i a", strtotime($item->startTime)) ?></span>
			</a>
		</td>
		<td>
			<?php echo $item->serviceName; ?>
		</td>
		<td>
			<?php $names = explode( " ", $item->stylistName); echo $names[0]; ?>
		</td>
	</tr>
<?php 
endforeach; 
?>


