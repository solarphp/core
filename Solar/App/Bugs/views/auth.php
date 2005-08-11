<?php
/**
* 
* Savant3 template for the user authentication form.
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
* Savant3 template for the user authentication form.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/
?>

<div style="float: right; border: 1px solid gray; margin: 12px; padding: 8px; background: #eee; text-align: center">
	<?php if (Solar::shared('user')->auth->status_code == 'VALID'): ?>
		<p>
			<?php $this->_($this->locale('Solar::TEXT_AUTH_USERNAME')) ?>
			<br /><strong><?php $this->_(Solar::shared('user')->auth->username) ?></strong>
		</p>
		<?php
			echo $this->form('begin');
			echo $this->form('block', 'begin', null, 'row');
			echo $this->form('hidden', 'op', 'logout');
			echo $this->form('submit', '', $this->locale('Solar::TEXT_LOGOUT'), '');
			echo $this->form('end');
		?>
	<?php else: ?>
		<?php
			echo $this->form('begin');
			echo $this->form('block', 'begin', null, 'row');
			echo $this->form('hidden', 'op', 'login');
			echo $this->form('text', 'username', '', $this->locale('Solar::LABEL_USERNAME'), array('size' => 10));
			echo $this->form('block', 'split');
			echo $this->form('password', 'password', '', $this->locale('Solar::LABEL_PASSWORD'), array('size' => 10));
			echo $this->form('block', 'split');
			echo $this->form('submit', '', $this->locale('Solar::TEXT_LOGIN'), '');
			echo $this->form('end');
		?>
		<p><?php
			echo nl2br(wordwrap(Solar::shared('user')->auth->status_text, 20));
			Solar::shared('user')->auth->reset();
		?></p>
	<?php endif; ?>
</div>