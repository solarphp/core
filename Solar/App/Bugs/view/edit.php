<?php include $this->template('header.php') ?>

<h2>Bug Report <?php echo $this->formdata->elements['bugs[id]']['value'] ?></h2>
[ <a href="?action=list_open">Back to list</a> ]

<!-- enclose in table to collapse the div -->
<table><tr><td>

	<?php if ($this->formdata->feedback): ?>
		<div style="background: #eee; padding: 4px; border: 2px solid red;">
			<?php foreach ((array) $this->formdata->feedback as $text) {
				echo "<p>" . $this->scrub($text) . "</p>\n";
			} ?>
		</div>
	<?php endif ?>
	
	<?php
		$this->form('set', 'class', 'Savant3');
		echo $this->form('begin', $this->formdata->config);
		echo $this->form('hidden', 'op', Solar::locale('Solar', 'OP_SAVE'));
		echo $this->form('fullauto', $this->formdata->elements);
		echo $this->form('group', 'start');
		echo $this->form('submit', 'op', Solar::locale('Solar', 'OP_SAVE'));
		echo $this->form('submit', 'op', Solar::locale('Solar', 'OP_CANCEL'));
		echo $this->form('group', 'end');
		echo $this->form('end');
	?>
	
</td><tr></table>

<?php include $this->template('comments.php') ?>

<?php include $this->template('footer.php') ?>