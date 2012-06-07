<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php 
foreach($this->items as $i => $item): 
	$link = JRoute::_( 'index.php?option=com_salonbook&view=worker&layout=edit&task=edit&cid[]='. $item->id );
	$calendarLink = JRoute::_( '/index.php?option=com_gcalendar&view=google&Itemid='. $item->calendar_id );
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
		<?php if ( $item->calendar_id > 0 )
		{
		?>
			<a href="<?php echo $calendarLink; ?>" onclick="displayCalendarPopup(this.href); return false;">
				Google calendar
			</a>
		<?php 
		}
		?>
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


