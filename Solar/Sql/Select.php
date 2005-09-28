<?php

/**
* 
* Class for SQL select generation and results.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/


/**
* 
* Class for SQL select generation and results.
* 
* Example usage:
* 
* <code>
* $select = Solar::object('Solar_Sql_Select');
* 
* // select these columns
* $select->cols(array(
*   'id',
* 	'n_last',
* 	'n_first',
* 	'adr_street',
* 	'adr_city',
* 	'adr_region AS state',
* 	'adr_postcode AS zip',
* 	'adr_country',
* ));
* 
* // from this table
* $select->from('contacts'); // single or multi
* 
* // on these ANDed conditions
* $select->where('n_last = :lastname');
* $select->where('adr_city = :city');
* 
* // reverse-ordered by first name
* $select->order('n_first DESC')
* 
* // get 50 per page, when we limit by page
* $select->paging(50);
* 
* // bind data into the query.
* // remember :lastname and :city in the setWhere() calls above.
* $data = ('lastname' => 'Jones', 'city' => 'Memphis');
* $select->bind($data);
* 
* // limit by which page of results we want
* $select->limitPage(1);
* 
* // get a Solar_Sql_Result object (the default)
* $result = $select->fetch(); // or fetch('result')
* 
* // alternatively, fetch all rows as an array
* $rows = $select->fetch('all');
* 
* // find out the count of rows, and how many pages there are.
* // this comes back as an array('count' => ?, 'pages' => ?).
* $total = $select->countPages();
* 
* </code>
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Select extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* sql => (string|array) Name of the shared SQL object, or array of (driver,
	* options) to create a standalone SQL object.
	* 
	* paging => (int) Number of rows per page.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'sql'    => 'sql',
		'paging' => 10,
	);
	
	
	/**
	* 
	* The component parts of a select statement.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $parts = array(
		'distinct' => false,
		'cols'     => array(),
		'from'     => array(),
		'join'     => array(),
		'where'    => array(),
		'group'    => array(),
		'having'   => array(),
		'order'    => array(),
		'limit'    => array(
			'count'  => 0,
			'offset' => 0
		),
	);
	
	
	/**
	* 
	* The number of rows per page.
	* 
	* @access protected
	* 
	* @var int
	* 
	*/
	
	protected $paging = 10;
	
	
	/**
	* 
	* Data to bind into the query as key => value pairs.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $bind = array();
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config User-provided config values.
	* 
	*/

	public function __construct($config = null)
	{
		// basic construction
		parent::__construct($config);
		
		// connect to the database
		if (is_string($this->config['sql'])) {
			// use a shared object
			$this->sql = Solar::shared($this->config['sql']);
		} else {
			// use a standalone object
			$this->sql = Solar::object(
				$this->config['sql'][0],
				$this->config['sql'][1]
			);
		}
		
		// set up defaults
		$this->paging($this->config['paging']);
	}
	
	
	/**
	* 
	* Read-only access to properties.
	* 
	* @access public
	* 
	* @return mixed The property value.
	* 
	*/

	public function __get($key)
	{
		return $this->$key;
	}
	
	
	/**
	* 
	* Sets the number of rows per page.
	* 
	* @access public
	* 
	* @param int $rows The number of rows to page at.
	* 
	* @return void
	* 
	*/

	public function paging($rows)
	{
		// force a positive integer
		$rows = (int) $rows;
		if ($rows < 1) {
			$rows = 1;
		}
		$this->paging = $rows;
	}
	
	
	/**
	* 
	* Makes the query SELECT DISTINCT.
	* 
	* @access public
	* 
	* @param bool $flag Whether or not the SELECT is DISTINCT (default
	* true).
	* 
	* @return void
	* 
	*/

	public function distinct($flag = true)
	{
		$this->parts['distinct'] = (bool) $flag;
	}
	
	
	/**
	* 
	* Adds columns to the query.
	* 
	* @access public
	* 
	* @param string|array $cols The columns to add.
	* 
	* @return void
	* 
	*/

	public function cols($spec)
	{
		if (empty($spec)) {
			return;
		}
		
		if (is_string($spec)) {
			$spec = explode(',', $spec);
		} else {
			settype($spec, 'array');
		}
		
		$this->parts['cols'] = array_merge($this->parts['cols'], $spec);
	}
	
	
	/**
	* 
	* Adds a table to the query.
	* 
	* @access public
	* 
	* @param string|array $from The table name(s) to select from.
	* 
	* @return void
	* 
	*/

	public function from($spec)
	{
		if (empty($spec)) {
			return;
		}
		
		if (is_string($spec)) {
			$spec = explode(',', $spec);
		} else {
			settype($spec, 'array');
		}
		
		$this->parts['from'] = array_merge($this->parts['from'], $spec);
	}
	
	
	/**
	* 
	* Adds joins to the query, one at a time.
	* 
	* @access public
	* 
	* @param string $name The table name to join to.
	* 
	* @param string $cond Join on this condition.
	* 
	* @param string $type The type of join to perform, e.g.
	* "left", "right", "inner", etc.  Typically not needed.
	* 
	* @return void
	* 
	*/

	public function join($name, $cond, $type = null)
	{
		$this->parts['join'][] = array(
			'type' => null,
			'name' => $name,
			'cond' => $cond
		);
	}
	
	
	/**
	* 
	* Adds a WHERE condition to the query.
	* 
	* @access public
	* 
	* @param string $cond The WHERE condition.
	* 
	* @param string $op Whether to 'AND' or 'OR' this condition with
	* existing conditions (default is 'AND').
	* 
	* @return void
	* 
	*/

	public function where($cond, $op = 'AND')
	{
		if (empty($cond)) {
			return;
		}
		
		if ($this->parts['where']) {
			$this->parts['where'][] = strtoupper($op) . ' ' . $cond;
		} else {
			$this->parts['where'][] = $cond;
		}
	}
	
	
	/**
	* 
	* Adds grouping to the query.
	* 
	* @access public
	* 
	* @param string|array $spec The column(s) to group by.
	* 
	* @return void
	* 
	*/

	public function group($spec)
	{
		if (empty($spec)) {
			return;
		}
		
		if (is_string($spec)) {
			$spec = explode(',', $spec);
		} else {
			settype($spec, 'array');
		}
		
		$this->parts['group'] = array_merge($this->parts['group'], $spec);
	}
	
	
	/**
	* 
	* Adds a HAVING condition to the query.
	* 
	* @access public
	* 
	* @param string $cond The HAVING condition.
	* 
	* @param string $op Whether to 'AND' or 'OR' this condition with
	* existing conditions (default is 'AND').
	* 
	* @return void
	* 
	*/

	public function having($cond, $op = 'AND')
	{
		if (empty($cond)) {
			return;
		}
		
		if ($this->parts['having']) {
			$this->parts['having'][] = strtoupper($op) . ' ' . $cond;
		} else {
			$this->parts['having'][] = $cond;
		}
	}
	
	
	/**
	* 
	* Adds a row order to the query.
	* 
	* @access public
	* 
	* @param string|array $spec The column(s) and direction to order by.
	* 
	* @return void
	* 
	*/

	public function order($spec)
	{
		if (empty($spec)) {
			return;
		}
		
		if (is_string($spec)) {
			$spec = explode(',', $spec);
		} else {
			settype($spec, 'array');
		}
		
		// force 'ASC' or 'DESC' on each order spec, default is ASC.
		foreach ($spec as $key => $val) {
			$asc  = (strtoupper(substr($val, -4)) == ' ASC');
			$desc = (strtoupper(substr($val, -5)) == ' DESC');
			if (! $asc && ! $desc) {
				$spec[$key] .= ' ASC';
			}
		}
		
		// merge them into the current order set
		$this->parts['order'] = array_merge($this->parts['order'], $spec);
	}
	
	
	/**
	* 
	* Sets a limit count and offset to the query.
	* 
	* @access public
	* 
	* @param int $count The number of rows to return.
	* 
	* @param int $offset Start returning after this many rows.
	* 
	* @return void
	* 
	*/

	public function limit($count = null, $offset = null)
	{
		$this->parts['limit']['count']  = (int) $count;
		$this->parts['limit']['offset'] = (int) $offset;
	}
	
	
	/**
	* 
	* Sets the limit and count by page number.
	* 
	* @access public
	* 
	* @param int $page Limit results to this page number.
	* 
	* @return void
	* 
	*/

	public function limitPage($page = null)
	{
		// reset the count and offset
		$this->parts['limit']['count']  = 0;
		$this->parts['limit']['offset'] = 0;
		
		// determine the count and offset from the page number
		$page = (int) $page;
		if ($page > 0) {
			$this->parts['limit']['count']  = $this->paging;
			$this->parts['limit']['offset'] = $this->paging * ($page - 1);
		}
	}
	
	
	/**
	* 
	* Clears query properties.
	* 
	* @access public
	* 
	* @param string $key The property to clear; if empty, clears all
	* query properties.
	* 
	* @return void
	* 
	*/

	public function clear($key = null)
	{
		$list = array_keys($this->parts);
		
		if (empty($key)) {
			// clear all
			foreach ($list as $key) {
				$this->parts[$key] = array();
			}
		} elseif (in_array($key, $list)) {
			// clear some
			$this->parts[$key] = array();
		}
		
		// make sure limit has a count and offset
		if (empty($this->parts['limit'])) {
			$this->parts['limit'] = array(
				'count' => 0,
				'offset' => 0
			);
		}
	}
	
	
	/**
	* 
	* Adds data to bind into the query.
	* 
	* @access public
	* 
	* @param mixed $key The replacement key in the query.  If this is an
	* array or object, the $val parameter is ignored, and all the
	* key-value pairs in the array (or all properties of the object) are
	* added to the bind.
	* 
	* @param mixed $val The value to use for the replacement key.
	* 
	* @return void
	* 
	*/

	public function bind($key, $val = null)
	{
		if (is_array($key)) {
			$this->bind = array_merge($this->bind, $key);
		} elseif (is_object($key)) {
			$this->bind = array_merge((array) $this->bind, $key);
		} else {
			$this->bind[$key] = $val;
		}
	}
	
	
	/**
	* 
	* Unsets bound data.
	* 
	* @access public
	* 
	* @param mixed $spec The key to unset.  If a string, unsets that one
	* bound value; if an array, unsets the list of values; if empty, unsets
	* all bound values (the default).
	* 
	* @return void
	* 
	*/

	public function unbind($spec = null)
	{
		if (empty($spec)) {
			$this->bind = array();
		} else {
			settype($spec, 'array');
			foreach ($spec as $key) {
				unset($this->bind[$key]);
			}
		}
	}
	
	
	/**
	* 
	* Quotes values for a query.
	* 
	* @access public
	* 
	* @param mixed $value The value to quote.
	* 
	* @return mixed The quoted value.
	* 
	*/

	public function quote($value)
	{
		return $this->sql->quote($value);
	}
	
	
	/**
	* 
	* Fetch the results based on the current query properties.
	* 
	* @access public
	* 
	* @param string $type The type of fetch to perform (all, one, row, etc).
	* 
	* @return mixed The query results.
	* 
	*/

	public function fetch($type = 'result')
	{
		return $this->sql->select($type, $this->parts, $this->bind);
	}
	
	
	/**
	* 
	* Get the count of rows and number of pages for the current query.
	* 
	* @access public
	* 
	* @param string $col The column to COUNT() on.  Default is '*'.
	* 
	* @return array An associative array with keys 'count' (the total number
	* of rows) and 'pages' (the number of pages based on $this->paging).
	* 
	*/

	public function countPages($col = '*')
	{
		// make a self-cloned copy so that all settings are identical
		$select = clone($this);
		
		// clear out all columns, then add a single COUNT column
		$select->clear('cols');
		$select->cols("COUNT($col)");
		
		// clear any limits
		$select->clear('limit');
		
		// select the count of rows and free the cloned copy
		$result = $select->fetch('one');
		unset($select);
		
		// was there an error?
		if (Solar::isError($result)) {
			return $result;
		}
		
		// $result is the row-count; how many pages does it convert to?
		$pages = 0;
		if ($result > 0) {
			$pages = ceil($result / $this->paging);
		}
		
		// done!
		return array(
			'count' => $result,
			'pages' => $pages
		);
	}
}
?>