<?php $self = Solar::server['PHP_SELF'] ?>
<?php if (count($this->list)): ?>
	<?php foreach ($this->list as $item): ?>
		<p>
			<?php echo $this->ahref($item['uri'], $item['title']) ?><br />
			to <?php
				$tags = explode(' ', $item['tags']);
				foreach ($tags as $tag) {
					echo $this->ahref($self . "/tag/$tag", $tag) . '&nbsp;';
				}
			?>
			... by <?php echo $this->ahref($self . "user/{$item['user_id']}", $item['user_id']) ?>
			... on <?php echo $this->date($item['ts_new']) ?>
			<?php
				if (Solar::$shared->auth->username = $item['user_id']) {
					echo '... ';
					echo $this->ahref($self . "edit?id={$item['id']", 'edit');
				}
			?>
		</p>
	<?php endforeach ?>
<?php elseif ?>
	<p>No bookmarks found.</p>
<?php endif ?>