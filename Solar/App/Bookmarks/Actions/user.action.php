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
 * @version $Id$
 * 
 */

// get standalone objects
$bookmarks = Solar::factory('Solar_Model_Bookmarks');

// allow uri to set the "count" for each page (default 10)
$bookmarks->paging($this->_query('paging', 10));

// the requested owner_handle
$owner_handle = $this->_info('owner_handle');

// what tags are we looking for?
$tags = $this->_info('tags');

// the requested ordering of list results
$order = $this->_getOrder();

// which page number?
$page = $this->_query('page', 1);

// assign data for the layout
$this->_layout['head']['title'] = 'Solar_App_Bookmarks';
$this->_layout['body']['title'] = $this->locale('BOOKMARKS');

// RSS link for the page
$link = Solar::factory('Solar_Uri');
$link->setInfo(1, 'userFeed');

if ($tags) {
    // there are tags requested, so the RSS should show all pages
    // (i.e., page zero) and ignore the rows-per-page settings.
    $link->setQuery('page', 'all');
    $link->clearQuery('rows_per_page');
}

$this->_layout['head']['link']['rss'] = array(
    'rel'   => 'alternate',
    'type'  => 'application/rss+xml',
    'title' => Solar::server('PATH_INFO'),
    'href'  => $link->export(),
);

// get the list of results
$this->list = $bookmarks->fetchList($owner_handle, $tags, $order, $page);

// get the total pages and row-count
$total = $bookmarks->countPages($owner_handle, $tags);

// flash forward the backlink in case we go to edit
$this->setFlash('backlink', Solar::server('REQUEST_URI'));

// set the view
$this->_view = 'browse';

// assign view vars
$this->pages        = $total['pages'];
$this->order        = $order;
$this->page         = $page;
$this->owner_handle = $owner_handle; // requested owner_handle
$this->tags         = $tags; // the requested tags
$this->tags_in_use  = $bookmarks->fetchTagList($owner_handle); // all tags for this user

?>