<?php
/**
 * 
 * Partial template for the list of tag links.
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
        <h3><?php echo $this->getText('TAG_LIST') ?></h3>
        <?php
            $uri->setQuery('page', 1);
            if ($this->owner_handle) {
                $baseInfo = "bookmarks/user/{$this->owner_handle}";
            } else {
                $baseInfo = "bookmarks/tag";
            }
        ?>
        <table border="0" cellspacing="2" cellpadding="0"><?php
            // build a series of table rows as links to tags
            $tmp = array();
            foreach ($this->tags_in_use as $tag => $count) {
                $uri->setInfoString("$baseInfo/$tag");
                $tmp[] = "<tr><td align=\"right\">$count</td><td>" . $this->action($uri, $tag) . "</td></tr>";
            }
            echo implode("\n", $tmp);
        ?>
        </table>
