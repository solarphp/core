<?php

/**
* 
* Application component module for bug tracking.
* 
* This only tracks the state of the bug; use the Talk module for
* recording the report narrative and comments about the bug.
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
* @version $Id$
* 
* @todo Add a Solar_Cell_Bugs_Log module to track changes?
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
	
	/**
	* 
	* User-supplied configuration values.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		
		// directory for local strings
		'locale' => 'Solar/Cell/Bugs/Locale/',
		
		// queues to report on (default is blank)
		'queue' => array(''),
		
		// report type codes
		'type' => array(
			'bug',
			'example',
			'feature',
		),
			
		// work progress status codes
		'status' => array(
			'new',
			'confirmed',
			'assigned',
			'feedback',
			'resolved',
			'duplicate',
			'bogus',
			'wontfix',
			'suspended',
			'reopened',
		),
		
		// which status codes are logically open?
		'status_open' => array('new', 'confirmed', 'assigned', 'feedback', 'reopened'),
		
		// which status codes are logically closed?
		'status_closed' => array('duplicate', 'bogus', 'resolved', 'suspended', 'wontfix'),
		
		// priority descriptions
		'priority' => array(
			0 => 'Very low',
			1 => 'Low',
			2 => 'Moderate',
			3 => 'High',
			4 => 'Very high',
			5 => 'Very high (security)',
			6 => 'Severe',
			7 => 'Severe (security)',
			8 => 'Critical',
			9 => 'Critical (security)',
		),
	);
	
	
	/**
	* 
	* Fetch one bug report.
	* 
	* @access public
	* 
	* @param int $id The bug report ID number.
	* 
	* @return array An array of info about the bug report.
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
	* Fetch a list of open bug reports.
	* 
	* @access public
	* 
	* @param string $order An ORDER clause to override the default order.
	* 
	* @param int $page Which page number to show; by default, shows all.
	* 
	* @return array An array of bug reports.
	* 
	*/
	
	public function fetchOpen($order = null, $page = null)
	{
		$tmp = array();
		foreach ($this->config['status_open'] as $val) {
			$tmp[] = 'status = ' . $this->quote($val);
		}
		$where = implode(' OR ', $tmp);
		$having = null;
		return $this->selectFetch('list', $where, $having, $order, $page);
	}
	
	
	/**
	* 
	* Fetch a list of all bug reports (open and closed).
	* 
	* @access public
	* 
	* @param string|array An added WHERE clause to further filter the results.
	* If an assoc array, the keys are treated as field names and the values as
	* "equals" values, to be ANDed together to make a WHERE clause.  If null,
	* no filtering is applied.
	* 
	* @param string $order An ORDER clause to override the default order.
	* 
	* @param int $page Which page number to show; by default, shows all.
	* 
	* @return array An array of bug reports.
	* 
	*/
	
	public function fetchList($where = null, $order = null, $page = null)
	{
		if (is_array($where)) {
			$tmp = array();
			foreach ($where as $key => $val) {
				$tmp = "$key = " . $this->quote($val);
			}
			$where = implode(' AND ' . $where);
		}
		
		return $this->selectFetch('list', null, null, $order, $page);
	}
	
	
	/**
	* 
	* Updates one bug report by ID.
	* 
	* @access public
	* 
	* @param array $data An associative array of data where the key is a 
	* column name and the value is updated value.
	* 
	* @param int $id The row ID to update.
	* 
	* @return mixed
	* 
	*/
	
	public function updateItem($data, $id)
	{
		$where = 'id = ' . $this->quote($id);
		return $this->update($data, $where);
	}
	
	
	/**
	* 
	* Returns the $schema array for this object.
	* 
	* @access protected
	* 
	* @return array An entity schema array.
	* 
	*/
	
	protected function getSchema()
	{
		// -------------------------------------------------------------
		// 
		// table name
		// 
		
		$schema['tbl'] = 'sc_bugs';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// sequential id
		$schema['col']['id'] = array(
			'type'    => 'int',
			'sequence' => 'sc_bugs',
			'primary' => true,
			'require' => true,
		);
			
		// date-time when first reported
		$schema['col']['ts_new'] = array(
			'type'    => 'timestamp',
			'require' => true,
			'default' => array(array('self','defaultCol'), 'ts_new'),
		);
		
		// date-time when last modified
		$schema['col']['ts_mod'] = array(
			'type'    => 'timestamp',
			'require' => true,
			'default' => array(array('self','defaultCol'), 'ts_mod'),
		);
		
		// short summary of the bug
		$schema['col']['summ'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'validate'   => array(
				array(
					'notBlank',
					$this->locale('VALID_SUMM')
				)
			),
		);
		
		// report type
		$schema['col']['type'] = array(
			'type'    => 'varchar',
			'size'    => 16,
			'require' => true,
			'validate'   => array(
				array(
					'inList',
					$this->locale('VALID_TYPE'),
					array_keys($this->config['type'])
				)
			),
		);
		
		// the affected component part
		$schema['col']['queue'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'validate'   => array(
				array(
					'notBlank',
					$this->locale('VALID_QUEUE')
				),
				array(
					'inList',
					$this->locale('VALID_QUEUE'),
					$this->config['queue']
				)
			),
		);
		
		// priority (0 is lowest, 9 is highest)
		$schema['col']['priority'] = array(
			'type'    => 'numeric',
			'size'    => 1,
			'scope'   => 0,
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
			'validate'   => array(
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
			'ts_new'  => 'normal',
			'ts_mod'  => 'normal',
			'type'    => 'normal',
			'queue'    => 'normal',
			'user_id' => 'normal',
			'status'  => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// list of bugs
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => 'ts_new DESC',
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
		
		// combine the queue options (keys same as values)
		$queue_opts = array_combine($this->config['queue'], $this->config['queue']);
		
		// create priority options
		$priority_opts = array();
		for ($i = 0; $i < 10; $i++) {
			$priority_opts[$i] = $this->config['priority'][$i];
		}
		
		// new report
		$schema['frm']['new'] = array(
			'id'     => array(
				'type'    => 'hidden',
			),
			'summ' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_SUMM'),
				'attribs' => array('size' => '60'),
				'require' => true,
				'validate' => $schema['col']['summ']['validate'],
			),
			'type' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_TYPE'),
				'options' => $type_opts,
				'require' => true,
				'validate' => $schema['col']['type']['validate'],
			),
			'queue' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_QUEUE'),
				'options' => $queue_opts,
				'require' => true,
				'validate' => $schema['col']['queue']['validate'],
			),
			'priority' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_PRIORITY'),
				'options' => $priority_opts,
			),
		);
		
		// full editor
		$schema['frm']['edit'] = array(
			'id'     => array(
				'type'    => 'hidden',
			),
			'summ' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_SUMM'),
				'attribs' => array('size' => '60'),
			),
			'ts_new' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_TS_NEW'),
				'disable' => true,
			),
			'ts_mod' => array(
				'type'    => 'text',
				'label'   => $this->locale('LABEL_TS_MOD'),
				'disable' => true,
			),
			'type' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_TYPE'),
				'options' => $type_opts,
			),
			'queue' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_QUEUE'),
				'options' => $queue_opts,
				'validate' => $schema['col']['queue']['validate'],
			),
			'priority' => array(
				'type'    => 'select',
				'label'   => $this->locale('LABEL_PRIORITY'),
				'options' => $priority_opts,
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
				'validate' => $schema['col']['status']['validate'],
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
		
		case 'ts_new':
		case 'ts_mod':
			return $this->timestamp();
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
		$data['ts_new'] = $this->defaultCol('ts_new');
		$data['ts_mod'] = $data['ts_new'];
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
		$data['ts_mod'] = $this->defaultCol('ts_mod');
		
		// hash the password
		if (isset($data['passwd'])) {
			$data['passwd'] = md5($data['passwd']);
		}
	}
}
?>