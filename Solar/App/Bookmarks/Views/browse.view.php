<?php
/**
 * 
 * Solar_View template for lists of bookmarks (in XHTML).
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
$uri = Solar::factory('Solar_Uri');
?>
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
            if ($this->owner_handle) echo $this->getText('USER') . ': ' . $this->escape($this->owner_handle);
            if ($this->owner_handle && $this->tags) echo "<br />\n";
            if ($this->tags) echo $this->getText('TAGS') . ': ' . $this->escape($this->tags);
        ?></h2>
    <?php endif ?>
    
    <!-- output the list of results -->
    <?php if ($this->list): ?>
        <?php foreach ($this->list as $item) {
            /** Each bookmark item on the page */
            include $this->template('browse/item.part.php');
        } ?>
        
        <!-- previous / page-count / next -->
        <hr />
        <p><strong>[ <?php
            $uri->import();
            $tmp = Solar::get('page', 1);
            $uri->setQuery('page', $tmp - 1);
            $prev = $uri->export();
            $uri->setQuery('page', $tmp + 1);
            $next = $uri->export();
            if ($this->page > 1) echo $this->anchor($prev, 'OP_PREVIOUS') . ' | ';
            echo $this->escape("Page {$this->page} of {$this->pages}");
            if ($this->page < $this->pages) echo ' | ' . $this->anchor($next, 'OP_NEXT');
        ?> ]</strong></p>
        
    <?php else: ?>
        <p><?php echo $this->getText('NO_BOOKMARKS_FOUND') ?></p>
    <?php endif ?>
    
    <?php if (Solar::registry('user')->auth->status == 'VALID'): ?>
        <hr />
        
        <!-- Add a new bookmark -->
        <p><?php
            echo $this->action("bookmarks/add", 'ADD_NEW_BOOKMARK');
        ?></p>
        
        <!-- QuickMark link -->
        <p><?php
            $uri->import();
            $scheme = $uri->scheme;
            $host = $uri->host;
            $path = $uri->path;
            $js = "javascript:location.href='$scheme://$host$path/bookmarks/quick?uri='+encodeURIComponent(location.href)+'&subj='+encodeURIComponent(document.title)";
            echo $this->getText('DRAG_THIS') . ': ';
            echo $this->anchor($js, 'QUICKMARK');
        ?></p>
    <?php endif ?>
</div>
