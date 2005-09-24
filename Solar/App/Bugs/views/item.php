<?php
/**
* 
* Savant3 template for showing a single bug report.
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
* Savant3 template for showing a single bug report.
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

<h2><?php $this->eprint($this->locale('BUG_REPORT') . ' ' . $this->item['id']['value']) ?></h2>

<p>[ <?php echo $this->ahref('?action=listOpen', $this->locale('BACK_TO_LIST')) ?> ]</p>

<!-- enclose in table to collapse the div -->
<table><tr><td>

	<?php
		// output the bug item as a frozen form
		$this->form('set', 'class', 'Savant3');
		$this->form('set', 'freeze', true);
		echo $this->form('begin');
		echo $this->form('auto', $this->item);
		echo $this->form('end');
	?>
	
</td><tr></table>

<?php if ($this->can_edit): ?>
	<p><?php echo $this->ahref('?action=edit&id=' . $this->item['id']['value'], $this->locale('EDIT_BUG_REPORT')) ?></p>
<?php endif; ?>

<?php include $this->template('comments.php') ?>

<table><tr><td>

	<?php if ($this->formdata->feedback): ?>
		<div style="background: #eee; padding: 8px;">
			<?php foreach ((array) $this->formdata->feedback as $text) {
				echo "<p>" . $this->escape($text) . "</p>\n";
			} ?>
		</div>
	<?php endif ?>
	
	<?php
		$this->form('set', 'freeze', false);
		echo $this->form('begin');
		echo $this->form('hidden', 'op', $this->locale('Solar::OP_SAVE'));
		echo $this->form('block', 'begin', $this->locale('ADD_COMMENT'));
		echo $this->form('auto', $this->formdata->elements);
		echo $this->form('group', 'begin');
		echo $this->form('submit', 'op', $this->locale('Solar::OP_SAVE'));
		echo $this->form('reset', 'op', $this->locale('Solar::OP_RESET'));
		echo $this->form('group', 'end');
		echo $this->form('end');
	?>

</td><tr></table>

<?php include $this->template('footer.php') ?>