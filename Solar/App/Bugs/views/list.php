<?php
/**
* 
* Savant3 template for showing a list of reports.
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
?>
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
	<p><?php echo Solar::locale('Solar_App_Bugs', 'NO_BUGS_LISTED') ?></p>
<?php endif ?>

<p><?php echo $this->ahref('?action=listOpen', Solar::locale('Solar_App_Bugs', 'SHOW_OPEN_BUGS')) ?></p>
<p><?php echo $this->ahref('?action=listAll', Solar::locale('Solar_App_Bugs', 'SHOW_ALL_BUGS')) ?></p>
<p><?php echo $this->ahref('?action=edit&id=0', Solar::locale('Solar_App_Bugs', 'REPORT_NEW_BUG')) ?></p>

<?php include $this->template('footer.php') ?>