<?php
/**
 * 
 * Default controller; for viewing bookmarks by tag intersection.
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

// get the shared user object
$user = Solar::shared('user');

// RSS link for the page (regardless of whether it's actually available)
$link = Solar::object('Solar_Uri');
$link->setQuery('rss', '1');

$this->rss = array(
    'avail' => false,
    'title' => Solar::server('PATH_INFO'),
    'descr' => 'Solar_App_Bookmarks',
    'date'  => date('c'), // should be latest mod date in the $this->view->list
    'link'  => $link->export(),
);

unset($link);

// get standalone objects
$bookmarks = Solar::object('Solar_Model_Bookmarks');

// allow uri to set the "count" for each page (default 10)
$bookmarks->paging($this->_query('paging', 10));

// the requested owner_handle (none)
$owner_handle = null;

// what tags are we looking for?
$tags = $this->_info('tags');

// the requested ordering of list results
$order = $this->_getOrder();

// what page-number of the results are we looking for?
// (regardless of RSS or HTML)
$page = $this->_query('page', 1);

// get the list of results
$this->list = $bookmarks->fetchList($owner_handle, $tags, $order, $page);

// get the total pages and row-count
$total = $bookmarks->countPages($owner_handle, $tags);

// assign everything else for the view
$this->rss['avail'] = true;
$this->count        = $total['count'];
$this->pages        = $total['pages'];
$this->order        = $order;
$this->page         = $page;
$this->owner_handle = null; // requested owner_handle
$this->tags         = $tags; // the requested tags
$this->tags_in_use  = $bookmarks->fetchTagList($owner_handle); // all tags

// RSS or HTML view?
$rss = $this->_query('rss', false);
if ($rss) {
    $this->_view = 'rss';
} else {
    $this->_view = 'list';
}
?>