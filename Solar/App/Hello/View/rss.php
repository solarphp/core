<?php
/**
 * 
 * RSS 2.0 view.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: rss.php 1842 2006-09-24 18:05:07Z pmjones $
 * 
 */
header('Content-Type: text/xml; charset=iso-8859-1');
echo '<?xml version="1.0" encoding="iso-8859-1" ?>' . "\n";
$request = Solar::factory('Solar_Request');
$server = $request->server();
?>
<rss version="2.0">
    <channel>
        <title>Solar: Hello World</title>
        <link><?php echo $this->escape($server['REQUEST_URI']) ?></link>
        <description>Example hello world RSS feed</description>
        <pubDate><?php echo $this->date('', DATE_RFC822) ?></pubDate>
        <item>
            <category><?php echo $this->escape($this->code) ?></category>
            <title><?php echo $this->escape($this->text) ?></title>
            <pubDate><?php echo $this->date(time(), DATE_RFC822) ?></pubDate>
            <description><?php echo $this->escape($this->text) ?></description>
            <link><?php echo $this->escape($server['REQUEST_URI']) ?></link>
        </item>
    </channel>
</rss>