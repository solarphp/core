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

// build the local vars
$this->_forward('user');

// explicitly use a different view
$this->_view = 'feed';

// assign vars
$this->feed['title'] = 'user';
$this->feed['descr'] = $this->owner_handle . '/' . $this->tags;
$this->feed['date'] = date(DATE_RSS);

$uri = Solar::factory('Solar_Uri_Action');
$uri->info[1] = 'user';
$this->feed['link'] = $uri->fetch(true);

// explicitly use a one-step view (no layout)
$this->_layout = false;

?>