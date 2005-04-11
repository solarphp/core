<?php
	include $this->template('header.php');
	$link = Solar::object('Solar_Uri');
?>
<div>
	<!-- ordering -->
	<div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
		<h2><?php echo Solar::locale('Solar_App_Bookmarks', 'ORDERED_BY') ?></h2>
		<p><?php
			$tmp = array(
				'rank'       => 'Rank',
				'rank_desc'  => 'Rank (desc)',
				'tags'       => 'Tags',
				'tags_desc'  => 'Tags (desc)',
				'title'      => 'Title',
				'title_desc' => 'Title (desc)',
				'ts'         => 'Timestamp',
				'ts_desc'    => 'Timestamp (desc)',
			);
			
			// refresh the base link
			$link->import();
			
			// add links
			foreach ($tmp as $key => $val) {
				if (Solar::get('order', 'ts_desc') == $key) {
					echo "<strong>$val</strong><br />\n";
				} else {
					$link->query('set', 'order', $key);
					echo $this->ahref($link->export(), $val) . "<br />\n";
				}
			}
		?></p>
	</div>
	
	<!-- the list of tags for this user (if one is selected) -->
	<?php if (Solar::pathinfo(0) == 'user'): ?>
		<div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
			<h2><?php echo Solar::locale('Solar_App_Bookmarks', 'TAG_LIST') ?></h2>
			<p><?php
				$link->import();
				$tmp = array();
				foreach ($this->user_tags as $tag) {
					$link->clearInfo();
					$link->info('setstr', "user/{$this->user_id}/$tag");
					$tmp[] = $this->ahref($link->export(), $tag);
				}
				echo implode("<br />\n", $tmp);
			?></p>
		</div>
	<?php endif ?>
	
	<!-- results -->
	<div style="float: left;">
		<!-- output the user_id and tag-search, if any -->
		<?php if ($this->user_id || $this->tags): ?>
			<h2><?php
				if ($this->user_id) echo Solar::locale('Solar_App_Bookmarks', 'USER') . ': ' . $this->scrub($this->user_id);
				if ($this->user_id && $this->tags) echo "<br />\n";
				if ($this->tags) echo Solar::locale('Solar_App_Bookmarks', 'TAGS') . ': ' . $this->scrub($this->tags);
			?></h2>
		<?php endif ?>
		
		<!-- output the list of results -->
		<?php if (count($this->list)): ?>
			<?php foreach ($this->list as $item): ?>
				<p>
					<!-- title -->
					<span style="font-size: 120%; font-weight: bold;"><?php
						echo $this->ahref($item['uri'], $item['title']);
					?></span>
					
					<!-- description -->
					<br /><?php echo nl2br(wordwrap($this->scrub($item['descr']), 72)) ?>
					
					<!-- rank and uri -->
					<br /><span style="font-size: 90%;"><?php
						// rank
						echo Solar::locale('Solar_App_Bookmarks', 'RANK') . ' ' . $this->scrub($item['rank']);
						
						// from uri
						echo ' ' . Solar::locale('Solar_App_Bookmarks', 'FROM') . ' ';
						$cut = $item['uri'];
						if (strlen($cut) > 72) {
							// if longer than 72 chars, only show 64 chars, cut in the middle
							$cut = substr($cut, 0, 48) . '...' . substr($cut, -16);
						}
						echo $this->scrub($cut);
					?>
					
					<!-- date added by user -->
					<br /><?php
						echo Solar::locale('Solar_App_Bookmarks', 'ON') . ' ' . $this->date($item['ts_new']) . ' ';
						echo Solar::locale('Solar_App_Bookmarks', 'BY') . ' ';
						$link->clearInfo();
						$link->clearQuery();
						$link->info('set', '0', 'user');
						$link->info('set', '1', $item['user_id']);
						echo $this->ahref($link->export(), $item['user_id']);
					?></span>
					
					<!-- tags -->
					<br /><?php
						echo Solar::locale('Solar_App_Bookmarks', 'TAGGED') . ' ';
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
							$back_info = Solar::super('server', 'PATH_INFO');
							$back_qstr = Solar::super('server', 'QUERY_STRING');
							$link->clearInfo();
							$link->clearQuery();
							$link->info('set', '0', 'edit');
							$link->query('set', 'id', $item['id']);
							$link->query('set', 'info', $back_info);
							$link->query('set', 'qstr', $back_qstr);
							echo '&nbsp;...&nbsp;' . $this->ahref($link->export(), 'edit');
						}
					?>
				</p>
			<?php endforeach ?>
			
		<?php else: ?>
			<p><?php echo Solar::locale('Solar_App_Bookmarks', 'NO_BOOKMARKS_FOUND') ?></p>
		<?php endif ?>
		
		<?php if (Solar::$shared->user->auth->status_code == 'VALID'): ?>
			<hr />
			<p><?php
				$link->clearInfo();
				$link->clearQuery();
				$link->info('set', 0, 'edit');
				$link->query('set', 'id', '0');
				echo $this->ahref($link->export(), 'Add new bookmark')
			?></p>
			
			<p><?php
				$scheme = $link->elem['scheme'];
				$host = $link->elem['host'];
				$path = $link->elem['path'];
				$js = "javascript:location.href='$scheme://$host$path/edit?id=0&uri='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title)";
				echo Solar::locale('Solar_App_Bookmarks', 'DRAG_THIS'). ': ';
				echo $this->ahref($js, Solar::locale('Solar_App_Bookmarks', 'QUICKMARK'));
			?></p>
		<?php endif ?>
	</div>
</div>
<?php include $this->template('footer.php') ?>