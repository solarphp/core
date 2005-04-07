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

require_once 'Solar/App.php';


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

	protected $action_src = 'pathinfo';
	protected $action_var = 0;
	protected $action_default = 'tag';
	
	
	protected function getOrder()
	{
		// there are only some accepted orderings
		$tmp = strtolower(Solar::get('order'));
		switch ($tmp) {
		
		case 'ts':
		case 'ts_asc':
			$order = 'ts_new ASC';
			break;
		
		case 'ts_desc':
			$order = 'ts_new DESC';
			
		case 'title':
		case 'title_asc':
			$order = 'title ASC';
			break;
		
		case 'title_desc':
			$order = 'title DESC';
			break;
			
		case 'user':
		case 'user_asc':
			$order = 'user_id ASC';
			break;
			
		case 'user_desc':
			$order = 'user_id DESC';
			break;
			
		default:
			$order = 'ts_new DESC';
			break;
		
		}
		
		return $order;
	}
}
?>