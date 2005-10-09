<?php

/**
* 
* Social bookmarking application.
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

/**
* Application controller class.
*/
Solar::loadClass('Solar_App');

/**
* 
* Social bookmarking application.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
* 
*/

class Solar_App_Bookmarks extends Solar_App {
	
	protected function setup()
	{
		 $this->action['src']     = 'pathinfo';
		 $this->action['var']     = 0;
		 $this->action['default'] = 'tag';
	}
	
	// there are only some accepted orderings.
	protected function getOrder()
	{
		$tmp = strtolower(Solar::get('order'));
		
		switch ($tmp) {
		
		// created timestamp
		case 'created':
		case 'created_asc':
		case 'ts':
		case 'ts_asc':
			$order = 'created ASC';
			break;
		
		case 'created_desc':
		case 'ts_desc':
			$order = 'created DESC';
			break;
			
		// title
		case 'subj':
		case 'subj_asc':
		case 'title':
		case 'title_asc':
			$order = 'LOWER(subj) ASC';
			break;
		
		case 'subj_desc':
		case 'title_desc':
			$order = 'LOWER(subj) DESC';
			break;
		
		// tags
		case 'tag':
		case 'tag_asc':
		case 'tags':
		case 'tags_asc':
			$order = 'LOWER(tags) ASC';
			break;
			
		case 'tag_desc':
		case 'tags_desc':
			$order = 'LOWER(tags) DESC';
			break;
		
		// rank
		case 'rank':
		case 'rank_asc':
			$order = 'rank ASC';
			break;
		
		case 'rank_desc':
			$order = 'rank DESC';
			break;
		
		// owner handle (username)
		case 'owner':
		case 'owner_asc':
		case 'user':
		case 'user_asc':
			$order = 'LOWER(owner_handle) ASC';
			break;
		
		case 'owner_desc':
		case 'user_desc':
			$order = 'LOWER(owner_handle) DESC';
			break;
		
		// default
		default:
			$order = 'created DESC';
			break;
		
		}
		
		return $order;
	}
}
?>