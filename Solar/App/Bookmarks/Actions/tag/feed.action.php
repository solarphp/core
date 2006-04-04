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

// build the local variables
$this->_forward('tag');

// explicitly pick a different view script
$this->_view = 'feed';

// assign to the view
$this->feed['title'] = 'tag';
$this->feed['descr'] = $this->tags;

$uri = Solar::factory('Solar_Uri_Action');
$uri->info[1] = 'tag';
$this->feed['link'] = $uri->fetch(true);

// explicitly use a one-step view (i.e., no layout)
$this->_layout = false;

?>