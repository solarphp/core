<style>
	body, p, div, td, th, .Savant3 {font-family: "Lucida Sans", Verdana; font-size: 12px;}
	table.Savant3 { border-spacing: 1px; }
	th.Savant3 { background: #bcd; text-align: right; vertical-align: top; padding: 4px; }
	td.Savant3 { background: #eee; text-align: left; vertical-align: top;  padding: 4px; }
	select, option { font-family: "Lucida Sans", Verdana; font-size: 12px; }
	input[type="text"], textarea { font-family: "Lucida Sans Typewriter", monospace; font-size: 12px; }
	
</style>

<h2>Bug Report <?php echo $this->formdata->elements['bugs[id]']['value'] ?></h2>
[ <a href="index.php">Back to list</a> ]

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

<?php include $this->template('comments.tpl.php') ?>
