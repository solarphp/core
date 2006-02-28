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

// a basic link object
$link = Solar::factory('Solar_Uri');

?>
<div>
    <!-- list of tags in use -->
    <div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
        <?php
            /** The list of tags in the system */
            include $this->template('browse/tags.part.php');
        ?>
    </div>
    
    <!-- ordering -->
    <div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
        <?php
            /** The order-by links */
            include $this->template('browse/order.part.php');
        ?>
    </div>
    
    <!-- results -->
    <div style="float: left;">
        <!-- output the owner_handle and tag-search, if any -->
        <?php if ($this->owner_handle || $this->tags): ?>
            <h2><?php
                if ($this->owner_handle) $this->eprint($this->locale('USER') . ': ' . $this->owner_handle);
                if ($this->owner_handle && $this->tags) echo "<br />\n";
                if ($this->tags) $this->eprint($this->locale('TAGS') . ': ' . $this->tags);
            ?></h2>
        <?php endif ?>
        
        <!-- output the list of results -->
        <?php if (count($this->list)): ?>
            <?php foreach ($this->list as $item) {
                /** Each bookmark item on the page */
                include $this->template('browse/item.part.php');
            } ?>
            
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
        
        <?php if (Solar::registry('user')->auth->status_code == 'VALID'): ?>
            <hr />
            
            <!-- Add a new bookmark -->
            <p><?php
                echo $this->actionLink("bookmarks/add", $this->locale('ADD_NEW_BOOKMARK'));
            ?></p>
            
            <!-- QuickMark link -->
            <p><?php
                $link->import();
                $scheme = $link->scheme;
                $host = $link->host;
                $path = $link->path;
                $js = "javascript:location.href='$scheme://$host$path/bookmarks/quick?uri='+encodeURIComponent(location.href)+'&subj='+encodeURIComponent(document.title)";
                $this->eprint($this->locale('DRAG_THIS') . ': ');
                echo $this->ahref($js, $this->locale('QUICKMARK'));
            ?></p>
        <?php endif ?>
    </div>
</div>
