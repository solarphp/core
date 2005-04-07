<?php include $this->template('header.php') ?>
<?php $self = $_SERVER['PHP_SELF'] . '/'; ?>

<!-- output the user_id and tag-search, if any -->
<?php if ($this->user_id || $this->tags): ?>
	<h2><?php
		if ($this->user_id) echo $this->scrub($this->user_id);
		if ($this->user_id && $this->tags) echo ": ";
		if ($this->tags) echo $this->scrub($this->tags);
	?></h2>
<?php endif ?>

<!-- output the list of results -->
<?php if (count($this->list)): ?>
	<?php foreach ($this->list as $item): ?>
		<p>
			<span style="font-size: 120%; font-weight: bold;"><?php echo $this->ahref($item['uri'], $item['title']) ?></span>
			<br /><span style="font-size: 90%;"><?php echo $this->scrub($item['uri']) ?></span>
			<br />to<?php
				$tags = explode(' ', $item['tags']);
				foreach ($tags as $tag) {
					echo '&nbsp;' . $this->ahref($self . "tag/$tag", $tag);
				}
			?> by <?php echo $this->ahref($self . "user/{$item['user_id']}", $item['user_id']);
			?> on <?php echo $this->date($item['ts_new']) ?>
			<?php
				if (Solar::$shared->user->auth->username == $item['user_id']) {
					echo '... ';
					echo $this->ahref($self . "edit?id={$item['id']}", 'edit');
					echo ' (' . $item['id'] . ')';
				}
			?>
		</p>
	<?php endforeach ?>
<?php else: ?>
	<p>No bookmarks found.</p>
<?php endif ?>

<?php if (Solar::$shared->user->auth->status_code == 'VALID'): ?>
	<p><?php echo $this->ahref($self . "edit?id=0", 'Add new bookmark') ?></p>
<?php endif ?>

<?php include $this->template('footer.php') ?>