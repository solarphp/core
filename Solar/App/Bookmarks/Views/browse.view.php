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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

?>
<!-- list of tags in use -->
<div id="taglist">
    <?php
        /** The list of tags in the system */
        include $this->template('browse/tags.part.php');
    ?>
</div>

<!-- ordering -->
<div id="ordering">
    <?php
        /** The order-by links */
        include $this->template('browse/order.part.php');
    ?>
</div>

<h1><?php echo $this->getText('BOOKMARKS') ?></h1>

<!-- results -->
<div id="bookmarks">
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
        
            // action uri processor
            $uri = Solar::factory('Solar_Uri_Action');
            
            // previous
            if ($this->page > 1) {
                $uri->query['page'] = $this->page - 1;
                echo $this->action($uri, 'SUBMIT_PREVIOUS') . ' | ';
            }
            
            // current
            echo $this->escape("Page {$this->page} of {$this->pages}");
            
            // next
            if ($this->page < $this->pages) {
                $uri->query['page'] = $this->page + 1;
                echo ' | ' . $this->action($uri, 'SUBMIT_NEXT');
            }
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
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set('bookmarks/quick');
            $href = $uri->fetch(true);
            $js = "javascript:location.href='$href?uri='+encodeURIComponent(location.href)+'&subj='+encodeURIComponent(document.title)";
            echo $this->getText('DRAG_THIS') . ': ';
            echo $this->anchor($js, 'QUICKMARK');
        ?></p>
    <?php endif ?>
</div>
