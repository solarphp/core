<?php
	include $this->template('header.php');
	$link = Solar::object('Solar_Uri');
?>
<!-- output the user_id and tag-search, if any -->
<?php if ($this->user_id || $this->tags): ?>
	<h2><?php
		if ($this->user_id) echo "User: " . $this->scrub($this->user_id);
		if ($this->user_id && $this->tags) echo "<br />\n";
		if ($this->tags) echo "Tags: " . $this->scrub($this->tags);
	?></h2>
<?php endif ?>

<!-- output the list of results -->
<?php if (count($this->list)): ?>
	<?php foreach ($this->list as $item): ?>
		<p>
			<span style="font-size: 120%; font-weight: bold;"><?php echo $this->ahref($item['uri'], $item['title']) ?></span>
			<br /><span style="font-size: 90%;">from <?php echo $this->scrub($item['uri']); ?>
			<br />on <?php echo $this->date($item['ts_new']) ?>
			by <?php
				$link->clearInfo();
				$link->clearQuery();
				$link->info('set', '0', 'user');
				$link->info('set', '1', $item['user_id']);
				echo $this->ahref($link->export(), $item['user_id']);
			?></span>
			<br />tagged<?php
				$tags = explode(' ', $item['tags']);
				foreach ($tags as $tag) {
					echo '&nbsp;';
					$link->clearInfo();
					$link->clearQuery();
					$link->info('set', '0', 'tag');
					$link->info('set', '1', $tag);
					echo $this->ahref($link->export(), $tag);
				}
				
				if (Solar::$shared->user->auth->username == $item['user_id']) {
					$link->clearInfo();
					$link->clearQuery();
					$link->info('set', '0', 'edit');
					$link->query('set', 'id', $item['id']);
					echo '&nbsp;...&nbsp;' . $this->ahref($link->export(), 'edit');
				}
			?>
		</p>
	<?php endforeach ?>
<?php else: ?>
	<p>No bookmarks found.</p>
<?php endif ?>

<?php if (Solar::$shared->user->auth->status_code == 'VALID'): ?>
	<p><?php
		$link->clearInfo();
		$link->clearQuery();
		$link->info('set', 0, 'edit');
		$link->query('set', 'id', '0');
		echo $this->ahref($link->export(), 'Add new bookmark')
	?></p>
<?php endif ?>

<?php include $this->template('footer.php') ?>