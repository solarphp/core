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
			<?php echo $this->ahref($item['uri'], $item['title']) ?><br />
			to <?php
				$tags = explode(' ', $item['tags']);
				foreach ($tags as $tag) {
					echo $this->ahref($self . "tag/$tag", $tag) . '&nbsp;';
				}
			?>
			... by <?php echo $this->ahref($self . "user/{$item['user_id']}", $item['user_id']) ?>
			... on <?php echo $this->date($item['ts_new']) ?>
			<?php
				if (Solar::$shared->user->auth->username == $item['user_id']) {
					echo '... ';
					echo $this->ahref($self . "edit?id={$item['id']}", 'edit');
				}
			?>
		</p>
	<?php endforeach ?>
<?php else: ?>
	<p>No bookmarks found.</p>
<?php endif ?>

<p><?php echo $this->ahref($self . "edit?id=0", 'Add new bookmark') ?></p>
<?php include $this->template('footer.php') ?>