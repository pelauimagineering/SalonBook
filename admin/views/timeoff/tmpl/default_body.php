<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php 
foreach($this->items as $i => $item): 
	$link = JRoute::_( 'index.php?option=com_salonbook&view=vacation&layout=edit&task=edit&cid[]='. $item->id );
?>
	
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php echo $item->stylist; ?>
		</td>
		<td>
			<a href="<?php echo $link; ?>">
				<?php echo $item->theDate; ?>
			</a>
		</td>
		<td>
			<?php echo date("g:i a", strtotime($item->startTime)) ?>
		</td>
		<td>
			<?php 
				$endTimeString = "+ " . $item->duration . " minutes ";
				$formattedEndTime = date('g:i a', strtotime($endTimeString, strtotime($item->startTime)));
				echo $formattedEndTime;
			 ?>
		</td>
		</tr>
<?php 
endforeach;
?>


