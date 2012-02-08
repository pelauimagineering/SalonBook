<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php 
foreach($this->items as $i => $item): 
	$link = JRoute::_( 'index.php?option=com_salonbook&view=worker&layout=edit&task=edit&cid[]='. $item->id );
?>
	
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<a href="<?php echo $link; ?>">
				<?php $names = explode( " ", $item->stylistName); echo $names[0]; ?>
			</a>
		</td>
		<td>
			<?php echo $item->calendarLogin; ?>
		</td>
		<td>
			<input type="password" value="<?php echo $item->calendarPassword ?>" disabled="disabled" />
		</td>
	</tr>
<?php 
endforeach; 
?>


