<h3>Commentary</h3>

<?php if ($this->comments): ?>

	<table cellspacing="0" cellpadding="0">
	<?php foreach ($this->comments as $val): ?>
		<tr>
			<td style="border: 2px solid #bcd; background: #eee;">
				<p style="background: #bcd; padding: 4px; margin: 0px;"><strong>[ <?php
						echo $this->date($val['ts'], 'date') . ' | ';
						echo $this->date($val['ts'], 'time');
						if ($val['user_id']) {
							echo  ' | ' . $this->scrub($val['user_id']);
						} elseif ($val['email']) {
							echo ' | ' . $this->scrub($val['email']);
						}
				?> ]</strong></p>
				<pre style="padding: 8px;"><?php echo wordwrap($this->scrub($val['body'])) ?></pre>
			</td>
		</tr>
		<tr><td><br /></td></tr>
	<?php endforeach ?>
	</table>
	
<?php else: ?>

	<p><?php echo Solar::locale('Solar_App_Bugs', 'NO_COMMENTS') ?></p>
	
<?php endif; ?>