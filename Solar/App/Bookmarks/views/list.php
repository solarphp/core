<?php
/**
* 
* Savant3 template for lists of bookmarks (in XHTML).
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
* @version $Id$
* 
*/
?>
<?php
include $this->template('header.php');
$link = Solar::object('Solar_Uri');
?>
<div>
	<!-- ordering -->
	<div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
		<h2><?php $this->eprint($this->locale('ORDERED_BY')) ?></h2>
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
					echo "<strong>";
					$this->eprint($val);
					echo "</strong><br />\n";
				} else {
					$link->setQuery('order', $key);
					echo $this->ahref($link->export(), $val) . "<br />\n";
				}
			}
		?></p>
	</div>
	
	<!-- the list of tags for this user (if one is selected) -->
	<?php if (Solar::pathinfo(0) == 'user'): ?>
		<div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
			<h2><?php $this->eprint($this->locale('TAG_LIST')) ?></h2>
			<p><?php
				$link->import();
				$tmp = array();
				foreach ($this->user_tags as $tag) {
					// clear out pathinfo, but reset the page to 1
					$link->clearInfo();
					$link->setQuery('page', 1);
					$link->setInfoString("user/{$this->user_id}/$tag");
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
				if ($this->user_id) $this->eprint($this->locale('USER') . ': ' . $this->user_id);
				if ($this->user_id && $this->tags) echo "<br />\n";
				if ($this->tags) $this->eprint($this->locale('TAGS') . ': ' . $this->tags);
			?></h2>
		<?php endif ?>
		
		<!-- output the list of results -->
		<?php if (count($this->list)): ?>
			<?php foreach ($this->list as $item): ?>
			
				<!-- NEW ITEM -->
				<p>
					<!-- title -->
					<span style="font-size: 120%; font-weight: bold;"><?php
						echo $this->ahref($item['uri'], $item['title']);
					?></span>
					
					<!-- description -->
					<?php if (trim($item['descr']) != ''): ?>
					
					<br /><?php echo nl2br(wordwrap($this->escape($item['descr']), 72)) ?>
					<?php endif ?>
					
					<!-- rank and uri -->
					<br /><span style="font-size: 90%;"><?php
						// rank
						$this->eprint($this->locale('RANK') . ' ' . $item['rank']);
						
						// from uri
						$this->eprint(' ' . $this->locale('FROM') . ' ');
						$cut = $item['uri'];
						if (strlen($cut) > 72) {
							// if longer than 72 chars, only show 64 chars, cut in the middle
							$cut = substr($cut, 0, 48) . '...' . substr($cut, -16);
						}
						$this->eprint($cut);
					?>
					
					<!-- date added by user -->
					<br /><?php
						$this->eprint($this->locale('ON') . ' ' . $this->date($item['ts_new']) . ' ');
						$this->eprint($this->locale('BY') . ' ');
						$link->clearInfo();
						$link->clearQuery();
						$link->setInfo('0', 'user');
						$link->setInfo('1', $item['user_id']);
						echo $this->ahref($link->export(), $item['user_id']);
					?></span>
					
					<!-- tags and edit link -->
					<br /><?php
					
						// tags
						$this->eprint($this->locale('TAGGED'));
						$tags = explode(' ', $item['tags']);
						foreach ($tags as $tag) {
							echo '&nbsp;';
							$link->clearInfo();
							$link->clearQuery();
							$link->setInfo('0', 'tag');
							$link->setInfo('1', $tag);
							echo $this->ahref($link->export(), $tag);
						}
						
						// edit link
						if (Solar::shared('user')->auth->username == $item['user_id']) {
							$back_info = Solar::server('PATH_INFO');
							$back_qstr = Solar::server('QUERY_STRING');
							$link->clearInfo();
							$link->clearQuery();
							$link->setInfo('0', 'edit');
							$link->setQuery('id', $item['id']);
							$link->setQuery('info', $back_info);
							$link->setQuery('qstr', $back_qstr);
							
							echo '&nbsp;...&nbsp;' . $this->ahref($link->export(), 'edit');
						}
					?>
				</p>
			<?php endforeach ?>
			
			<!-- previous / page-count / next -->
			<hr />
			<p><strong>[ <?php
				$link->import();
				$tmp = Solar::get('page', 1);
				$link->setQuery('page', $tmp - 1);
				$prev = $link->export();
				$link->setQuery('page', $tmp + 1);
				$next = $link->export();
				if ($this->page > 1) echo $this->ahref($prev, $this->locale('Solar::OP_PREVIOUS')) . ' | ';
				$this->eprint("Page {$this->page} of {$this->pages}");
				if ($this->page < $this->pages) echo ' | ' . $this->ahref($next, $this->locale('Solar::OP_NEXT'));
			?> ]</strong></p>
			
		<?php else: ?>
			<p><?php $this->eprint($this->locale('NO_BOOKMARKS_FOUND')) ?></p>
		<?php endif ?>
		
		<?php if (Solar::shared('user')->auth->status_code == 'VALID'): ?>
			<hr />
			<p><?php
				$link->clearInfo();
				$link->clearQuery();
				$link->setInfo(0, 'edit');
				$link->setQuery('id', '0');
				echo $this->ahref($link->export(), $this->locale('ADD_NEW_BOOKMARK'))
			?></p>
			
			<p><?php
				$scheme = $link->scheme;
				$host = $link->host;
				$path = $link->path;
				$js = "javascript:location.href='$scheme://$host$path/edit?id=0&uri='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title)";
				$this->eprint($this->locale('DRAG_THIS') . ': ');
				echo $this->ahref($js, $this->locale('QUICKMARK'));
			?></p>
		<?php endif ?>
	</div>
</div>
<?php include $this->template('footer.php') ?>