<?php
header('Content-Type: text/xml; charset=iso-8859-1');
echo '<?xml version="1.0" encoding="iso-8859-1" ?>' . "\n";
?>
<rss version="2.0">
    <channel>
        <title>Solar: Hello World</title>
        <link><?php $this->eprint($_SERVER['REQUEST_URI']) ?></link>
        <description>Example hello world RSS feed</description>
        <pubDate><?php $this->eprint(date(DATE_RFC822)) ?></pubDate>
        <item>
            <category><?php $this->eprint($this->code) ?></category>
            <title><?php $this->eprint($this->text) ?></title>
            <pubDate><?php $this->eprint(date(DATE_RFC822)) ?></pubDate>
            <description><?php $this->eprint($this->text) ?></description>
            <link><?php $this->eprint($_SERVER['REQUEST_URI']) ?></link>
        </item>
    </channel>
</rss>