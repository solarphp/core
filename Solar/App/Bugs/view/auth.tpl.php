<div style="float: right; border: 1px solid gray; margin: 12px; background: #eee; text-align: center"</div>
	<?php if (Solar::$shared->user->auth->status_code == 'VALID'): ?>
		<p>Signed in as <?php echo Solar::$shared->user->auth->username ?></p>
	<?php else: ?>
		<?php
			$this->form('begin');
			$this->form('text', 'username', '', 'Username:');
			$this->form('password', 'password', '', 'Password:');
			$this->form('submit', 'op', 'login', 'Sign In');
			$this->form('end');
		?>
		<p><?php
			echo Solar::$shared->user->auth->status_text;
			Solar::$shared->user->auth->reset();
		?></p>
	<?php endif; ?>
</div>