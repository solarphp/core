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

$this->_forward('user');
$this->_layout = false;
$this->_view = 'feed';
$this->feed['title'] = 'user';
$this->feed['descr'] = $this->owner_handle . '/' . $this->tags;
$this->feed['date'] = date(DATE_RSS);

$link = Solar::factory('Solar_Uri');
$link->setInfo(1, 'user');
$this->feed['link'] = $link->export();


?>