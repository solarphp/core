<?php if (is_array($this->list) && $count = count($this->list)): ?>
	<p><?php echo $count ?> bugs listed.</p>
	<table border="1" cellspacing="0" cellpadding="4">
		<tr>
			<th><?php echo Solar::locale('Solar_Cell_Bugs', 'LABEL_ID') ?></th>
			<th><?php echo Solar::locale('Solar_Cell_Bugs', 'LABEL_TS_NEW') ?></th>
			<th><?php echo Solar::locale('Solar_Cell_Bugs', 'LABEL_QUEUE') ?></th>
			<th><?php echo Solar::locale('Solar_Cell_Bugs', 'LABEL_TYPE') ?></th>
			<th><?php echo Solar::locale('Solar_Cell_Bugs', 'LABEL_SUMM') ?></th>
			<th><?php echo Solar::locale('Solar_Cell_Bugs', 'LABEL_STATUS') ?></th>
		</tr>
		<?php foreach ($this->list as $bug): ?>
		<tr>
			<td valign="top"><?php echo $bug['id'] ?></td>
			<td valign="top">
				<?php echo $this->date($bug['ts_new'], 'date') ?><br />
				<?php echo $this->date($bug['ts_new'], 'time') ?>
			</td>
			<td valign="top"><?php echo $bug['queue'] ?></td>
			<td valign="top"><?php echo $bug['type'] ?></td>
			<td valign="top"><?php echo $this->ahref(
				'view.php?id=' . $bug['id'],
				htmlspecialchars($bug['summ'])
			) ?></td>
			<td valign="top"><?php echo $bug['status'] ?></td>
		</tr>
		<?php endforeach ?>
	</table>
<?php else: ?>
	<p>No bugs in the system.</p>
<?php endif ?>

<p><a href="index.php">List open bugs.</a></p>
<p><a href="index.php?op=list">List all bugs</a> (both open and closed).</p>
<p><a href="edit.php?id=0">Report a new bug.</a></p>