<?php include $this->template('header.php') ?>

<?php if (is_array($this->list)): ?>
	<?php $count = count($this->list) ?>
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
				'?action=item&id=' . $bug['id'],
				$this->scrub($bug['summ'])
			) ?></td>
			<td valign="top"><?php echo $bug['status'] ?></td>
		</tr>
		<?php endforeach ?>
	</table>
<?php else: ?>
	<p>No bugs in the system.</p>
<?php endif ?>

<p><?php echo $this->ahref('?action=list_open', 'List only "open" bugs.') ?></p>
<p><?php echo $this->ahref('?action=list_all', 'List all bugs (both open and closed).') ?></p>
<p><?php echo $this->ahref('?action=edit&id=0', 'Report a new bug.') ?></p>

<?php include $this->template('footer.php') ?>