<?php

/**
 * 
 * Controller for viewing bookmarks by user (and optionally by tag).
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
 * @version $Id: user.php 576 2005-10-10 02:26:30Z pmjones $
 * 
 */

// get the shared user object
$user = Solar::shared('user');

// RSS link for the page (regardless of whether it's actually available)
$link = Solar::object('Solar_Uri');
$link->setQuery('rss', '1');

$this->rss = array(
    'avail' => false,
    'title' => Solar::server('PATH_INFO'),
    'descr' => 'Solar_App_Bookmarks',
    'date'  => date('c'), // should be latest mod date in the $this->list
    'link'  => $link->export(),
);

unset($link);

// get standalone objects
$bookmarks = Solar::object('Solar_Model_Bookmarks');

// allow uri to set the "count" for each page (default 10)
$bookmarks->paging($this->_query('paging', 10));

// the requested owner_handle
$owner_handle = $this->_info('owner_handle');

// what tags are we looking for?
$tags = $this->_info('tags');

// the requested ordering of list results
$order = $this->_getOrder();

// RSS or HTML? set up the page number accordingly (by default, RSS gets
// all bookmarks, not just one page).
$rss = $this->_query('rss', false);

if ($rss) {
    
    // looking at the RSS view.
    $this->_view = 'rss';
    if ($tags) {
        // if tags are requested, get all pages by default.
        $page = $this->_query('page', 0);
    } else {
        // otherwise it's all bookmarks for the user, get only page 1 by default.
        $page = $this->_query('page', 1);
    }
    
} else {

    $this->_view = 'list';
    $page = $this->_query('page', 1);
    
    // make sure the RSS link is available in the template
    $this->rss['avail'] = true;
    
    if ($tags) {
        // there are tags requested, so the RSS should show all pages
        // and ignore the rows-per-page settings.  build a custom
        // RSS link for this.
        $link = Solar::object('Solar_Uri');
        $link->setQuery('rss', '1');
        $link->clearQuery('page');
        $link->clearQuery('rows_per_page');
        $this->rss['link'] = $link->export();
        unset($link);
    }
}

// get the list of results
$this->list = $bookmarks->fetchList($owner_handle, $tags, $order, $page);

// get the total pages and row-count
$total = $bookmarks->countPages($owner_handle, $tags);

// assign everything else
$this->rss['avail'] = true;
$this->count        = $total['count'];
$this->pages        = $total['pages'];
$this->order        = $order;
$this->page         = $page;
$this->owner_handle = $owner_handle; // requested owner_handle
$this->tags         = $tags; // the requested tags
$this->tags_in_use  = $bookmarks->fetchTagList($owner_handle); // all tags for this user
?>