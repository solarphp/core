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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

// allow uri to set the "count" for each page (default 10)
$this->_bookmarks->setPaging($this->_query('paging', 10));

// the requested owner_handle
$owner_handle = $this->_info('owner_handle');

// what tags are we looking for?
$tags = $this->_info('tags');

// the requested ordering of list results
$order = $this->_getOrder();

// which page number?
$page = $this->_query('page', 1);

// get the list of results
$this->list = $this->_bookmarks->fetchAll($tags, $owner_handle, $order, $page);

// get the total pages and row-count
$total = $this->_bookmarks->countPages($tags, $owner_handle);

// flash forward the backlink in case we go to edit
$this->setFlash('backlink', Solar::server('REQUEST_URI'));

// set the view
$this->_view = 'browse';

// assign view vars
$this->pages        = $total['pages'];
$this->order        = Solar::get('order', 'created_desc');
$this->page         = $page;
$this->owner_handle = $owner_handle; // requested owner_handle
$this->tags         = $tags; // the requested tags
$this->tags_in_use  = $this->_bookmarks->fetchTags($owner_handle); // all tags for this user

// set the RSS feed link for the layout
$uri = Solar::factory('Solar_Uri_Action');
$uri->path[1] = 'user-feed';

if ($tags) {
    // there are tags requested, so the RSS should show all pages
    // (i.e., page zero) and ignore the rows-per-page settings.
    $uri->query['page'] = 'all';
    unset($uri->query['rows_per_page']);
}

$this->layout_link[] = array(
    'rel'   => 'alternate',
    'type'  => 'application/rss+xml',
    'title' => Solar::server('PATH_INFO'),
    'href'  => $uri->fetch(),
);
?>