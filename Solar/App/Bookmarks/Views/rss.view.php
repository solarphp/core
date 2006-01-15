<?php
/**
 * 
 * Savant3 template for lists of bookmarks (in RSS).
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

// output the header this way so as not to let the XML
// tags interfere with PHP
header('Content-Type: text/xml; charset=iso-8859-1');
echo '<?xml version="1.0" encoding="iso-8859-1" ?>' . "\n";
?>
<rss version="2.0">
    <channel>
        <title><?php $this->eprint($this->rss['title']) ?></title>
        <link><?php $this->eprint($this->rss['link']) ?></link>
        <description><?php $this->eprint($this->rss['descr']) ?></description>
        <pubDate><?php $this->eprint($this->rss['date']) ?></pubDate>
<?php foreach ($this->list as $key => $val): ?>
        <item>
            <category><?php $this->eprint($val['owner_handle'] . '/' . str_replace(' ', '+', $val['tags'])) ?></category>
            <title><?php $this->eprint($val['subj']) ?></title>
            <pubDate><?php echo date('r', strtotime($val['updated'])) ?></pubDate>
            <description><?php $this->eprint($val['summ']) ?></description>
            <link><?php $this->eprint($val['uri']) ?></link>
        </item>
<?php endforeach; ?>
    </channel>
</rss>