<?php

/**
* 
* Application component module for bug tracking.
* 
* This only tracks the state of the bug; use the Talk module for
* recording the report and comments about the bug.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bugs
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Bugs.php,v 1.5 2005/02/08 03:02:58 pmjones Exp $
* 
*/

Solar::autoload('Solar_Sql_Entity');


/**
* 
* Application component module for bug tracking.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bugs
* 
*/

class Solar_Cell_Bugs extends Solar_Sql_Entity {
	
	protected $talk;
	
	public $config = array(
		
		// locale strings
		'locale' => 'Solar/Cell/Bugs/Locale/',
		
		// options for Solar_Cell_Talk
		'Solar_Cell_Talk' => null,
		
		// component parts to report on (default is blank)
		'pack' => array(''),
		
		// report type codes
		'type' => array(
			'bug',
			'critical',
			'example',
			'feature',
		),
			
		// status codes
		'status' => array(
			'new',
			'accept',
			'feedback',
			'fixed',
			'dupe',
			'bogus',
			'wontfix',
			'suspend',
			'reopen',
		),
		
		// which statuses are open?
		'status_open' => array('new', 'accept', 'feedback', 'reopen'),
		
		// which statuses are closed?
		'status_closed' => array('fixed', 'dupe', 'bogus', 'fixed', 'suspend'),
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* Sets up a Solar_Cell_Talk object.
	* 
	* @access public
	* 
	* @var object
	* 
	*/
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->talk = Solar::object(
			'Solar_Cell_Talk',
			$this->config['Solar_Cell_Talk']
		);
	}
	
	
	/**
	* 
	* Get one bug report.
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
	
	public function fetchComments($id)
	{
		$filter = array('forum' => "sc_bugs://$id");
		$order = 'dt DESC';
		return $this->talk->selectFetch('forum', $filter);
	}
	
	public function fetchOpen($order = null, $page = null)
	{
		$tmp = array();
		foreach ($this->config['status_open'] as $val) {
			$tmp[] = 'status = ' . $this->quote($val);
		}
		$where = implode(' OR ', $tmp);
		return $this->selectFetch('list', $where, $order, $page);
	}
	
	public function fetchList($order = null, $page = null)
	{
		return $this->selectFetch('list', null, $order, $page);
	}
	
	
	protected function getSchema()
	{
		// -------------------------------------------------------------
		// 
		// table name
		// 
		
		$schema['ent'] = 'sc_bugs';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// sequential id
		$schema['col']['id'] = array(
			'type'    => 'int',
			'seqname' => 'sc_bugs',
			'primary' => true,
			'require' => true,
		);
			
		// date-time when first reported
		$schema['col']['dt_new'] = array(
			'type'    => 'timestamp',
			'require' => true,
			'default' => array(array('self','defaultCol'), 'dt_new'),
		);
		
		// date-time when last modified
		$schema['col']['dt_mod'] = array(
			'type'    => 'timestamp',
			'require' => true,
			'default' => array(array('self','defaultCol'), 'dt_mod'),
		);
		
		// short description
		$schema['col']['descr'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'valid'   => array(
				array(
					'notBlank',
					$this->locale('VALID_DESCR')
				)
			),
		);
		
		// report type
		$schema['col']['type'] = array(
			'type'    => 'varchar',
			'size'    => 16,
			'require' => true,
			'valid'   => array(
				array(
					'inList',
					$this->locale('VALID_TYPE'),
					array_keys($this->config['type'])
				)
			),
		);
		
		// the affected component part
		$schema['col']['pack'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'valid'   => array(
				array(
					'inList',
					$this->locale('VALID_PACK'),
					$this->config['pack']
				)
			),
		);
		
		// operating system
		$schema['col']['os'] = array(
			'type'    => 'varchar',
			'size'    => 16,
			'valid'   => array(
				array(
					'notBlank',
					$this->locale('VALID_OS')
				)
			),
		);
		
		// version number, typically a PHP version number
		$schema['col']['ver'] = array(
			'type'    => 'varchar',
			'size'    => 16,
			'valid'   => array(
				array(
					'notBlank',
					$this->locale('VALID_VER')
				)
			),
		);
		
		// assigned to this user_id
		$schema['col']['user_id'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// work status
		$schema['col']['status'] = array(
			'type'    => 'varchar',
			'size'    => 16,
			'require' => true,
			'valid'   => array(
				array(
					'inList',
					$this->locale('VALID_STATUS'),
					array_keys($this->config['status'])
				),
			),
		);
		
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'id'      => 'unique',
			'dt_new'  => 'normal',
			'dt_mod'  => 'normal',
			'type'    => 'normal',
			'pack'    => 'normal',
			'user_id' => 'normal',
			'status'  => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// generic list of bugs
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => 'dt_new DESC',
			'fetch'  => 'All',
			'count'  => 'id',
		);
		
		// one bug
		$schema['qry']['item'] = array(
			'select' => '*',
			'where'  => 'id = :id',
			'fetch'  => 'Row'
		);
		
		// -------------------------------------------------------------
		// 
		// forms
		// 
		
		// build localized type options
		$type_opts = array();
		foreach ($this->config['type'] as $val) {
			$type_opts[$val] = $this->locale('TYPE_' . strtoupper($val));
		}
		
		// build localized status options
		$status_opts = array();
		foreach ($this->config['status'] as $val) {
			$status_opts[$val] = $this->locale('STATUS_' . strtoupper($val));
		}
		
		// combine the package options (keys same as values)
		$pack_opts = array_combine($this->config['pack'], $this->config['pack']);
		
		// full editor
		$schema['frm']['edit'] = array(
			'id'     => array(
				'type'    => 'hidden',
			),
			'descr' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_DESCR'),
				'attribs' => array('size' => '60'),
				'require' => true,
			),
			'dt_new' => array(
				'type'    => 'readonly',
				'label'   => $this->locale('LABEL_DT_NEW'),
			),
			'dt_mod' => array(
				'type'    => 'readonly',
				'label'   => $this->locale('LABEL_DT_MOD'),
			),
			'type' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_TYPE'),
				'options' => $type_opts,
				'require' => true,
			),
			'pack' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_PACK'),
				'options' => $pack_opts,
				'require' => true,
			),
			'os' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_OS'),
				'require' => true,
			),
			'ver' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_VER'),
				'require' => true,
			),
			'user_id' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_USER_ID'),
			),
			'status' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_STATUS'),
				'options' => $status_opts,
				'require' => true,
			),
			// these two will go to Solar_Cell_Talk
			'email' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_EMAIL'),
				'require' => true,
			),
			'body' => array(
				'type'    => 'textarea',
				'label'   => $this->locale('LABEL_BODY'),
				'require' => true,
				'attribs' => array('rows' => 15, 'cols' => '60'),
			)
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
		
		case 'dt_new':
		case 'dt_mod':
			return substr(date('c'), 0, 19);
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
		// force to the current date and time
		$data['dt_new'] = $this->defaultCol('dt_new');
		$data['dt_mod'] = $data['dt_new'];
	}


	/**
	* 
	* Pre-update data maniuplation.
	* 
	* @access protected
	* 
	* @param array &$data The data to be inserted.
	* 
	* @return void
	* 
	*/

	protected function preUpdate(&$data)
	{
		// force to the current date and time
		$data['dt_mod'] = $this->defaultCol('dt_mod');
	}
	
	
	/**
	* 
	* Add a new bug report.
	* 
	* @access public
	* 
	* @param array &$data The data to be inserted.
	* 
	* @return void
	* 
	*/

	public function newReport(&$data)
	{
		// attempt the bug-report insert
		$result = $this->insert($data);
		
		// was it an error?
		if (Solar::isError($result)) {
			return $result;
		}
		
		// retain the bug id
		$id = $result;
		
		// add the related comment
		$result = $this->addComment($id, $data['email'], $data['descr'],
			$data['body']);
		
		// did the comment go OK?
		if (Solar::isError($result)) {
			/**
			* @todo If the comment part failed, we should report just
			* the 'email' and 'body' errors.  Also, delete the bug report
			* itself so that we have to continue entry.
			*/
			return $result;
		} else {
			return $id;
		}
	}
	
	
	/**
	* 
	* Modify an existing bug report (e.g., add comment or change status).
	* 
	*/
	
	public function modReport(&$data, $id)
	{
		// update the report itself
		$where = 'id = ' . $this->quote($id);
		$result = $this->update($data, $where);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// is there a comment to be added?
		if (trim($data['body']) != '') {
			// there is some comment text, add it in
			$result = $this->addComment($id, $data['email'],
				$data['descr'], $data['body']);
				
			if (Solar::isError($result)) {
				return $result;
			}
		}
	}
	
	/**
	* 
	* Adds a comment to an existing bug report.
	* 
	*/
	
	protected function addComment($id, $email, $subj, $body)
	{
		$data = array(
			'forum'     => "sc_bugs://$id",
			'name'      => $email,
			'email'     => $email,
			'email_pub' => 1,
			'subj'      => $subj,
			'body'      => $body
		);
		return $this->talk->insert($data);
	}
	
	
}
?>