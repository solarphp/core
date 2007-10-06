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
<?php
    /**
     * Add the RSS feed link to the layout_head array
     */
    $uri = Solar::factory('Solar_Uri_Action');
    $uri->format = 'rss';
    
    if ($this->tags) {
        // there are tags requested, so the RSS should show all pages
        // (i.e., page zero) and ignore the rows-per-page settings.
        $uri->query['page'] = 'all';
        unset($uri->query['rows_per_page']);
    }
    
    $this->layout_head['link'][] = array(
        'rel'   => 'alternate',
        'type'  => 'application/rss+xml',
        'title' => implode('/', $uri->path),
        'href'  => $uri->get(true),
    );
?>

<h1><?php echo $this->getText('HEADING_BOOKMARKS') ?></h1>

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
        
        <ul class="bookmark-list"><?php foreach ($this->list as $item) {
            echo $this->partial('_item.php', $item);
        } ?>
        
        </ul>
        
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

</div>
