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

<h2><?php $this->eprint($this->locale('BUG_REPORT') . ' ' . $this->formdata->elements['bugs[id]']['value']) ?></h2>
<p>[ <?php echo $this->ahref('?action=listOpen', $this->locale('BACK_TO_LIST')) ?> ]</p>

<!-- enclose in table to collapse the div -->
<table><tr><td>

	<?php if ($this->formdata->feedback):  // show form feedback ?>
		<div style="background: #eee; padding: 4px; border: 2px solid red;">
			<?php foreach ((array) $this->formdata->feedback as $text) {
				echo "<p>" . $this->escape($text) . "</p>\n";
			} ?>
		</div>
	<?php endif ?>
	
	<?php
		// build the editing form
		$this->form('set', 'class', 'Savant3');
		$this->form('set', 'freeze', false);
		echo $this->form('begin', $this->formdata->attribs);
		
		// default op is to save
		echo $this->form('hidden', 'op', $this->locale('Solar::OP_SAVE'));
		
		// the form elements
		echo $this->form('auto', $this->formdata->elements);
		
		// the op group (save/cancel)
		echo $this->form('group', 'begin');
		echo $this->form('submit', 'op', $this->locale('Solar::OP_SAVE'));
		echo $this->form('submit', 'op', $this->locale('Solar::OP_CANCEL'));
		echo $this->form('group', 'end');
		echo $this->form('end');
	?>
	
</td><tr></table>

<?php include $this->template('comments.php') ?>

<?php include $this->template('footer.php') ?>