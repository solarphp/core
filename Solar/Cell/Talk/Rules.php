<?php

/**
* 
* Applies filtering rules to posted messages.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Talk
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Rules.php,v 1.2 2005/02/08 02:04:51 pmjones Exp $
* 
*/

/**
* 
* Applies filtering rules to posted messages.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Talk
* 
*/

class Solar_Cell_Talk_Rules extends Solar_Base {
	
	
	/**
	* 
	* User-supplied configuration values.
	* 
	* Keys:
	* 
	* spam_words => (array) An array of individual spam words.
	* 
	* auto_status => (string) If a message is filtered as spam, the status to apply.
	* 
	* max_links => (int) If there are more than this many links in a post, flag as spam
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'spam_words'  => array(),
		'max_links'   => 3,
		'status_good' => 'show',
		'status_spam' => 'spam'
	);
	
	
	
	/**
	* 
	* Naive filter to look for spam words and count links in the message.
	* 
	* @access protected
	* 
	* @param array &$data The data to be inserted.
	* 
	* @return void
	* 
	*/
	
	public static function apply(&$data)
	{
		// if calling statically, create a temporary local object.
		if (! isset($this)) {
			// we're static
			$obj = Solar::object('Solar_Cell_Talk_Rules');
		} else {
			// we're an instance
			$obj = $this;
		}
		
		// loop through the word list. if we find one of the words,
		// mark as spam.
		foreach ($obj->config['spam_words'] as $word) {
			$found = strpos($data['words'], strtolower($word));
			if ($found !== false) {
				$data['status'] = $obj->config['status_spam'];
				return;
			}
		}
		
		// how many links are there?  more than the max?  probably spam.
		$count = substr_count($data['words'], '://');
		if ($count > $obj->config['max_links']) {
			$data['status'] = $obj->config['auto_status'];
			return;
		}
		
		// made it through OK, no changes needed.
		$data['status'] = $obj->config['status_good'];
	}
}
?>