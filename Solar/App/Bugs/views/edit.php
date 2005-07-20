<?php
/**
* 
* Savant3 template for editing a bug report.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Savant3 template for editing a bug report.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/
?>

<?php include $this->template('header.php') ?>

<h2><?php echo Solar::locale('Solar_App_Bugs', 'BUG_REPORT') . ' ' . $this->formdata->elements['bugs[id]']['value'] ?></h2>
<p>[ <?php echo $this->ahref('?action=listOpen', Solar::locale('Solar_App_Bugs', 'BACK_TO_LIST')) ?> ]</p>

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
		echo $this->form('begin', $this->formdata->attribs);
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