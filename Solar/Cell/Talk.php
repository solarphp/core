<?php

/**
* 
* Application component module for comment storage and retrieval.
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
* @version $Id$
* 
*/

/**
* Have the Entity class available for extension.
*/
Solar::autoload('Solar_Sql_Entity');


/**
* 
* Application component module for comment storage and retrieval.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Talk
* 
*/

class Solar_Cell_Talk extends Solar_Sql_Entity {
	
	/**
	* 
	* Additional config keys and values.
	* 
	*/
	
	public $config = array(
		'locale'         => 'Solar/Cell/Talk/Locale/',
		'notify_to'      => null,
		'notify_from'    => null,
		'notify_subj'    => null,
		'rules_callback' => array('Solar_Cell_Talk_Rules', 'apply'),
	);
	
	
	/**
	* 
	* Moderate one or more messages to the same status.
	* 
	* @access protected
	* 
	* @param int|array $id A single ID number or an array of them.
	* 
	* @param string $status A message status, such as 'spam'.
	* 
	* @return void
	* 
	*/
	
	public function moderate($id, $status)
	{
		// build the where clause
		if (is_array($id)) {
			$where = "id = 0";
			foreach ($id as $val) {
				$where .= " OR id = " . $this->quote($val);
			}
		} else {
			$where = 'id = ' . $this->quote($id);
		}
		
		// set the status
		$data = array('status' => $status);
		
		// do the update
		return $this->update($data, $where);
	}
	
	
	/**
	* 
	* Fetch one talk comment.
	* 
	* @access public
	* 
	* @param int $id The comment ID number.
	* 
	* @return array An array of info about the comment.
	* 
	*/
	
	public function fetchItem($id)
	{
		$data = $this->selectFetch('item', array('id' => $id));
		if (! $data) {
			return $this->error(
				'ERR_ID',
				array('id' => $id),
				E_USER_NOTICE
			);
		} else {
			return $data;
		}
	}
	
	
	/**
	* 
	* Fetch a list of all comments in a specific forum.
	* 
	* @access public
	* 
	* @return array An array of comments.
	* 
	*/
	
	public function fetchForum($forum, $order = null, $page = null)
	{
		return $this->selectFetch('forum', array('forum' => $forum),
			$order, $page);
	}
	
	
	/**
	* 
	* Search for messages with a specific word.
	* 
	* @todo Searches for one word now, should search for multiple.
	* 
	* @access protected
	* 
	* @return array
	* 
	*/
	
	public function search($word, $type = 'any', $exact = false)
	{
		// add the $word filter
		if ($exact) {
			// set up the filter spaces around the word; in the words
			// field, each word is delimited by a space, so this makes
			// sure we get the word itself, not a part of one.
			$word = "% $word %";
		} else {
			$word = "%$word%";
		}
		$filter = 'word LIKE ' . $this->quote(strtolower($word));
		
		// run the query
		return $this->selectFetch('list', $filter);
	}
	
	
	/**
	* 
	* Initialize the custom schema.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/
	
	protected function getSchema()
	{
		// -------------------------------------------------------------
		// 
		// table name
		// 
		
		$schema['tbl'] = 'sc_talk';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// sequential id
		$schema['col']['id'] = array(
			'type'    => 'int',
			'seqname' => 'sc_talk',
			'primary' => true,
			'require' => true,
		);
			
		// timestamp of message
		$schema['col']['ts'] = array(
			'type'    => 'timestamp',
			'require' => true,
			'default' => array(array('self','defaultCol'), 'ts'),
		);
		
		// logged IP address of the poster
		$schema['col']['ip_addr'] = array(
			'type'    => 'varchar',
			'size'    => 15,
			'default' => array(array('self','defaultCol'), 'ip_addr'),
		);
		
		// which "forum" this is attached to, whether a URL or a wiki page, or whatever
		$schema['col']['forum'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'default' => array(array('self','defaultCol'), 'forum'),
		);
		
		// username of the poster.
		$schema['col']['user_id'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// message status.
		// show: show this message
		// modr: requires moderation
		// deny: has been disapproved by moderator
		// spam: looks like spam
		$schema['col']['status'] = array(
			'type'    => 'char',
			'size'    => '4',
			'require' => false,
			'default' => 'show',
			'valid'   => array(
				array(
					'inList',
					$this->locale('VALID_STATUS'),
					array('show', 'modr', 'deny', 'spam')
				),
			),
		);
		
		// name of the person making the post
		$schema['col']['name'] = array(
			'type'    => 'varchar',
			'size'    => 64,
		);
		
		// email address of the person making the post
		$schema['col']['email'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'valid'   => array(
				// we allow blank emails here
				array(
					'email',
					$this->locale('VALID_EMAIL'),
					Solar_Valid::OR_BLANK
				)
			),
		);
		
		// whether or not to show the email address of the poster
		$schema['col']['email_pub'] = array(
			'type'    => 'bool',
			'default' => 0,
		);
		
		// website of the poster
		$schema['col']['website'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'valid'   => array(
				// we allow blank website here
				array(
					'uri',
					$this->locale('VALID_WEBSITE'),
					'http',
					Solar_Valid::OR_BLANK
				)
			),
		);
		
		// subject line
		$schema['col']['subj'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'valid'   => array(
				array('notBlank', $this->locale('VALID_SUBJ')),
			),
		);
		
		// body of the message
		$schema['col']['body'] = array(
			'type'    => 'clob',
			'valid'   => array(
				array('notBlank', $this->locale('VALID_BODY')),
			),
		);
		
		// searchable portion
		$schema['col']['words'] = array(
			'type'    => 'clob',
		);
		
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'id'     => 'unique',
			'ts'     => 'normal',
			'status' => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// generic list of entries
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => 'ts',
			'fetch'  => 'All',
			'count'  => 'id',
		);
		
		// one entry
		$schema['qry']['item'] = array(
			'select' => '*',
			'where'  => 'id = :id',
			'fetch'  => 'Row'
		);
		
		// list of entries in a given forum
		$schema['qry']['forum'] = array(
			'select' => '*',
			'where' => 'forum = :forum',
			'order' => 'ts',
			'fetch' => 'All'
		);
		
		// list of all forums
		$schema['qry']['forumList'] = array(
			'select' => 'DISTINCT forum',
			'order'  => 'forum',
			'fetch'  => 'Col'
		);
		
		
		// -------------------------------------------------------------
		// 
		// form hints
		// 
		
		// normal user entry form for a new post
		$schema['frm']['entry'] = array(
			'name' => array(
				'type'  => 'text',
				'label' => $this->locale('LABEL_NAME'),
				'attribs'  => array('size' => 64),
			),
			'email' => array(
				'type'  => 'text',
				'label' => $this->locale('LABEL_EMAIL'),
				'attribs'  => array('size' => 64),
			),
			'email_pub' => array(
				'type'  => 'checkbox',
				'label' => $this->locale('LABEL_EMAIL_PUB'),
			),
			'website' => array(
				'type'  => 'text',
				'label' => $this->locale('LABEL_WEBSITE'),
				'attribs'  => array('size' => 64),
			),
			'subj' => array(
				'type'  => 'text',
				'label' => $this->locale('LABEL_SUBJ'),
				'attribs'  => array('size' => 64),
				'require'  => true,
			),
			'body' => array(
				'type'  => 'textarea',
				'label' => $this->locale('LABEL_BODY'),
				'attribs'  => array('rows' => '16', 'cols' => '64'),
				'require'  => true,
			),
		);
		
		// minimal new post; you'll need to add a lot of stuff yourself
		$schema['frm']['mini'] = array(
			'email' => array(
				'type'  => 'text',
				'label' => $this->locale('LABEL_EMAIL'),
				'attribs'  => array('size' => 64),
			),
			'body' => array(
				'type'  => 'textarea',
				'label' => $this->locale('LABEL_BODY'),
				'attribs'  => array('rows' => '16', 'cols' => '64'),
				'require'  => true,
			),
		);
		
		return $schema;
	}
	
	
	/**
	* 
	* Returns the default value for a given column
	* 
	* @access public
	* 
	* @param string $col The column name to return a value for.
	* 
	* @return mixed A value for the column.
	* 
	*/
	
	public function defaultCol($col)
	{
		switch ($col) {
		
		case 'ip_addr':
			return Solar::super('server', 'REMOTE_ADDR');
			break;
		
		case 'ts':
			return $this->timestamp();
			break;
		
		case 'forum':
			return $_SERVER['REQUEST_URI'];
			break;
			
		default:
			return parent::defaultCol($col);
			break;
		}
	}


	/**
	* 
	* Pre-insert data maniuplation.
	* 
	* @access protected
	* 
	* @param array &$data The data to be inserted.
	* 
	* @return void
	* 
	*/

	protected function preInsert(&$data)
	{
		// make sure we're using the current username
		if (empty($data['user_id'])) {
			$auth = Solar::super('session', 'Solar_User_Auth', array());
			if (empty($auth['username'])) {
				$data['user_id'] = '';
			} else {
				$data['user_id'] = $auth['username'];
			}
		}
		
		// do we have a searchable set of words?
		if (! isset($data['words']) || trim($data['words']) == '') {
		
			// create a text of the entire post
			$text = implode(
				"\n",
				array(
					$data['ip_addr'],
					$data['name'],
					$data['email'],
					$data['website'],
					$data['subj'],
					$data['body'],
				)
			);
			
			// do everything we can to make it plain ascii text
			$text = trim($text);
			$text = utf8_decode($text);
			$text = urldecode($text);
			$text = html_entity_decode($text);
			
			// find all words in the message, eliminate duplicates, and keep
			// them all in lower case delimited by spaces. surround in
			// spaces because seraches need to know where the words
			// begin and end.
			$words = preg_split('/[\n\r\s\t]/', $text, PREG_SPLIT_NO_EMPTY);
			$words = implode(' ', array_unique($words));
			$data['words'] = ' ' . strtolower($words) . ' ';
		
		}
		
		// force to the current timestamp
		$data['ts'] = $this->defaultCol('ts');
	}
	
	
	/**
	* 
	* Post-insertion tasks.
	* 
	* @access protected
	* 
	* @param array &$data The data to be inserted.
	* 
	* @return void
	* 
	*/

	protected function postInsert(&$data)
	{
		// run through the specified rule processor.
		if (! empty($this->config['rules_callback'])) {
		
			// if the callback is an array and the first element
			// is a string, it's a static call to a class method.
			// make sure that class file is loaded.
			if (is_array($this->config['rules_callback']) &&
				is_string($this->config['rules_callback'][0])) {
				Solar::autoload($this->config['rules_callback'][0]);
			}
			
			// now call the filter
			call_user_func($this->config['rules_callback'], $data);
			
			// re-moderate the status
			$this->moderate($data['id'], $data['status']);
		}
		
		// call notification
		$this->notify($data);
		
		// done!
		return $data['id'];
	}
	
	
	/**
	* 
	* Notify watchers by email.
	* 
	* @access protected
	* 
	* @param array &$data The data that was inserted.
	* 
	* @return void
	* 
	*/

	protected function notify(&$data)
	{
		// are we doing notification?
		if (! empty($this->config['notify_to'])) {
		
			// build the subject line
			$subj = '';
			
			if (! empty($this->config['notify_subj'])) {
				$subj .= '[' . $this->config['notify_subj'] . '] ';
			}
			
			if ($data['status'] == 'show') {
				$subj .= 'New comment: ';
			} else {
				$subj .= 'Please moderate: ';
			}
			
			$subj .= $data['subj'];
			
			// build the body content
			$body = implode(
				"\n\n",
				array(
					"Timestamp: {$data['ts']}",
					"IP:        {$data['ip_addr']}",
					"Status:    {$data['status']}",
					"Name:      {$data['name']}",
					"Email:     {$data['email']}",
					"Website:   {$data['web']}",
					"Subject:   {$data['subj']}",
					"Message:",
					$data['body']
				)
			);
			
			// set up the headers
			$headers = '';
			if (! empty($this->config['notify_from'])) {
				$headers .= "From: " . $this->config['notify_from'] . "\r\n";
			}
			
			if (! empty($data['email'])) {
			    // try to make the reply address safe
			    $reply = str_replace(
					array("\r", "\n"),
					array('', ''),
					$data['email']
				);
				$headers .= "Reply-To: " . trim($reply) . "\r\n";
			}
			
			// send the message
			@mail($this->config['notify_to'], $subj, $body, $headers);
		}
	}
}
?>