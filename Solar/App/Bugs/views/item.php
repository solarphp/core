<?php include $this->template('header.php') ?>

<h2>Bug Report <?php echo $this->item['id']['value'] ?></h2>

<p>[ <a href="?action=list_open">Back to list</a> ]</p>

<!-- enclose in table to collapse the div -->
<table><tr><td>

	<?php
		// output the bug item as a frozen form
		$this->form('set', 'class', 'Savant3');
		$this->form('set', 'freeze', true);
		echo $this->form('begin');
		echo $this->form('fullauto', $this->item);
		echo $this->form('end');
		$this->form('set', 'freeze', false);
	?>
	
</td><tr></table>

<?php if ($this->can_edit): ?>
	<p><?php echo $this->ahref('?action=edit&id=' . $this->item['id']['value'], "Edit Bug Report") ?></p>
<?php endif; ?>

<?php include $this->template('comments.php') ?>

<table><tr><td>

	<?php if ($this->formdata->feedback): ?>
		<div style="background: #eee; padding: 8px;">
			<?php foreach ((array) $this->formdata->feedback as $text) {
				echo "<p>" . $this->scrub($text) . "</p>\n";
			} ?>
		</div>
	<?php endif ?>
	
	<?php
		echo $this->form('begin');
		echo $this->form('block', 'begin', 'Add Comment');
		echo $this->form('hidden', 'action', Solar::locale('Solar', 'OP_SAVE'));
		echo $this->form('fullauto', $this->formdata->elements);
		echo $this->form('group', 'begin');
		echo $this->form('submit', 'op', Solar::locale('Solar', 'OP_SAVE'));
		echo $this->form('reset', 'op', Solar::locale('Solar', 'OP_RESET'));
		echo $this->form('group', 'end');
		echo $this->form('end');
	?>

</td><tr></table>

<?php include $this->template('footer.php') ?>