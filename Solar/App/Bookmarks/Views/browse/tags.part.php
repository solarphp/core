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
            if ($this->owner_handle) {
                $action = "bookmarks/user/{$this->owner_handle}";
            } else {
                $action = "bookmarks/tag";
            }
        ?>
        <ul>
        <?php
            // build a series of links to tags
            $uri = Solar::factory('Solar_Uri_Action');
            $tmp = array();
            foreach ($this->tags_in_use as $tag => $count) {
                $uri->setPath("$action/$tag");
                $tmp[] = "<li>" . $this->action($uri, $tag) . " ($count)</li>";
            }
            echo implode("\n", $tmp);
        ?>
        </ul>
