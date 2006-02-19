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

// build the item list.  we do this here so we can catch
// the latest date in the list, and make the channel reflect
// that date.
$items = '';
$updated = ''; // last update
foreach ($this->list as $key => $val) {
    $category = $this->escape($val['owner_handle'] . '/' . str_replace(' ', '+', $val['tags']));
    $title = $this->escape($val['subj']);
    $pubDate = $this->escape(date(DATE_RSS, strtotime($val['updated'])));
    if ($val['updated'] > $updated) {
        $updated = $val['updated'];
    }
    $description = $this->escape($val['summ']);
    $link = $this->escape($val['uri']);
    
    $items .= <<<ITEM
        <item>
            <category>$category</category>
            <title>$title</title>
            <pubDate>$pubDate</pubDate>
            <description>$description</description>
            <link>$link</link>
        </item>

ITEM;
}
?>
<rss version="2.0">
    <channel>
        <title><?php $this->eprint($this->feed['title']) ?></title>
        <link><?php $this->eprint($this->feed['link']) ?></link>
        <description><?php $this->eprint($this->feed['descr']) ?></description>
        <pubDate><?php $this->eprint(date(DATE_RSS, strtotime($updated))) ?></pubDate>
        <?php echo $items ?>
    </channel>
</rss>