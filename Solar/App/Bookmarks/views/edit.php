<?php
/**
* 
* Savant3 template for editing a bookmark.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: edit.php 113 2005-03-28 17:54:33Z pmjones $
* 
*/

/**
* 
* Savant3 template for editing a bookmark.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmakrs
* 
*/
?>

<?php include $this->template('header.php') ?>
<?php $self = $_SERVER['PHP_SELF'] ?>

<h2><?php echo Solar::locale('Solar_App_Bookmarks', 'BOOKMARK') ?></h2>
<p>[ <?php echo $this->ahref($self, Solar::locale('Solar_App_Bookmarks', 'BACK_TO_LIST')) ?> ]</p>

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

<?php include $this->template('footer.php') ?>