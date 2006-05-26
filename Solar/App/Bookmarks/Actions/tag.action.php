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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

// allow uri to set the "count" for each page (default 10)
$this->_bookmarks->setPaging($this->_query('paging', 10));

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
$this->list = $this->_bookmarks->fetchAll($tags, $owner_handle, $order, $page);

// get the total pages and row-count
$total = $this->_bookmarks->countPages($tags, $owner_handle);

// flash forward the backlink in case we go to edit
$this->_flash->set('backlink', Solar::server('REQUEST_URI'));

// assign everything else for the view
$this->pages        = $total['pages'];
$this->order        = Solar::get('order', 'created_desc');
$this->page         = $page;
$this->owner_handle = null; // requested owner_handle
$this->tags         = $tags; // the requested tags
$this->tags_in_use  = $this->_bookmarks->fetchTags($owner_handle); // all tags

// use the 'browse' view
$this->_view = 'browse';

// RSS feed link for the page
$uri = Solar::factory('Solar_Uri_Action');
$uri->path[1] = 'tag-feed';
$this->layout_link[] = array(
    'rel'   => 'alternate',
    'type'  => 'application/rss+xml',
    'title' => Solar::server('PATH_INFO'),
    'href'  => $uri->fetch(),
);
?>