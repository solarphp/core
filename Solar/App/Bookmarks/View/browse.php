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
    <h3><?php echo $this->getText('HEADING_TAGLIST') ?></h3>
    <?php echo $this->partial('_tags.php') ?>
</div>

<!-- ordering -->
<div id="order">
    <h3><?php echo $this->getText('HEADING_ORDER') ?></h3>
    <?php echo $this->partial('_order.php') ?>
</div>

<h1><?php echo $this->getText('HEADING_BOOKMARKS') ?></h1>

<!-- results -->
<div id="bookmarks">
    <!-- output the owner_handle and tag-search, if any -->
    <?php if ($this->owner_handle || $this->tags): ?>
        <h2><?php
            if ($this->owner_handle) echo $this->getText('HEADING_USER') . ': ' . $this->escape($this->owner_handle);
            if ($this->owner_handle && $this->tags) echo "<br />\n";
            if ($this->tags) echo $this->getText('HEADING_TAGS') . ': ' . $this->escape($this->tags);
        ?></h2>
    <?php endif ?>
    
    <!-- output the list of results -->
    <?php if (count($this->list)): ?>
        <?php foreach ($this->list as $item) {
            echo $this->partial('_item.php', $item);
        } ?>
        
        <!-- previous / page-count / next -->
        <hr />
        <p><strong>[ <?php
        
            // action uri processor
            $uri = Solar::factory('Solar_Uri_Action');
            
            // previous
            if ($this->page > 1) {
                $uri->query['page'] = $this->page - 1;
                echo $this->action($uri, 'PROCESS_PREVIOUS') . ' | ';
            }
            
            // current
            echo $this->escape("Page {$this->page} of {$this->pages}");
            
            // next
            if ($this->page < $this->pages) {
                $uri->query['page'] = $this->page + 1;
                echo ' | ' . $this->action($uri, 'PROCESS_NEXT');
            }
        ?> ]</strong></p>
        
    <?php else: ?>
        <p><?php echo $this->getText('NO_BOOKMARKS_FOUND') ?></p>
    <?php endif ?>
    
    <?php if (Solar::registry('user')->auth->isValid()): ?>
        <hr />
        
        <!-- Add a new bookmark -->
        <p><?php
            echo $this->action("bookmarks/add", 'ACTION_ADD');
        ?></p>
        
        <!-- QuickMark link -->
        <p><?php
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set('bookmarks/quick');
            $href = $uri->fetch(true);
            $js = "javascript:location.href='$href?uri='+encodeURIComponent(location.href)+'&subj='+encodeURIComponent(document.title)";
            echo $this->getText('DRAG_THIS') . ': ';
            echo $this->anchor($js, 'ACTION_QUICK');
        ?></p>
    <?php endif ?>
</div>
