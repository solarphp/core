<?php
// output the header this way so as not to let the XML
// tags interfere with PHP
header('Content-Type: text/xml; charset=iso-8859-1');
echo '<?xml version="1.0" encoding="iso-8859-1" ?>' . "\n";
?>
<rss version="2.0">
	<channel>
		<title><?php echo $this->scrub($this->rss['title']) ?></title>
		<link><?php echo $this->scrub($this->rss['link']) ?></link>
		<description><?php echo $this->scrub($this->rss['descr']) ?></description>
		<pubDate><?php echo date('r', $this->rss['date']) ?></pubDate>
<?php foreach ($this->list as $key => $val): ?>
		<item>
			<category><?php echo $this->scrub($val['user_id'] . '/' . str_replace(' ', '+', $val['tags'])) ?></category>
			<title><?php echo $this->scrub($val['title']) ?></title>
			<pubDate><?php echo date('r', strtotime($val['ts_mod'])) ?></pubDate>
			<description><?php echo $this->scrub($val['descr']) ?></description>
			<link><?php echo $this->scrub($val['uri']) ?></link>
		</item>
<?php endforeach; ?>
	</channel>
</rss>