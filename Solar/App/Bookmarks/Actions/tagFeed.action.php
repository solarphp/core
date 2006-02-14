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
 * @version $Id: user.action.php 659 2006-01-15 19:46:43Z pmjones $
 * 
 */

$this->_forward('tag');
$this->_layout = false;
$this->_view = 'feed';
$this->feed['title'] = 'tag';
$this->feed['descr'] = $this->tags;

$link = Solar::factory('Solar_Uri');
$link->setInfo(1, 'tag');
$this->feed['link'] = $link->export();


?>