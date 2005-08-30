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
* @version $Id: Entity.php 277 2005-05-30 16:02:02Z pmjones $
* 
*/

Solar::loadClass('Solar_Sql_Table');


/**
* 
* Class for SQL select generation and results.
* 
* Proposed usage:
* 
* <code>
* $select = Solar::object('Solar_Sql_Select');
* 
* // select these columns
* $select->cols(
*   'id',
* 	'n_last',
* 	'n_first',
* 	'adr_street',
* 	'adr_city',
* 	'adr_region AS state',
* 	'adr_postcode AS zip',
* 	'adr_country',
* );
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
* // get 50 at a time
* $select->setPaging(50);
* 
* // fetch the first page of all rows of this bound data ...
* // remember :lastname and :city in the setWhere() calls above.
* $data = ('lastname' => 'Jones', 'city' => 'Memphis');
* 
* // use this to indicate which page of results we want
* $page = 1;
* 
* // fetch all rows as an array (the default)
* $select->setFetch('all');
* $result = $select->exec($data, $page);
* 
* // alternatively, get a Solar_Sql_Result object
* $select->setFetch(null);
* $result = $select->exec($data, $page);
* 
* // find out the count of rows, and how many pages there are.
* // this comes back as an array('count' => ?, 'pages' => ?).
* $total = $select->countPages($data);
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
	* locale => (string) Path to locale files.
	* 
	* paging => (int) Number of rows per page.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'locale' => 'Solar/Sql/Locale/',
		'sql'    => 'sql',
		'paging' => 10,
		'fetch'  => 'all',
	);
	
	
	/**
	* 
	* The columns to be selected.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $cols = array();
	
	
	/**
	* 
	* The tables to select from.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $from = array();
	
	
	/**
	* 
	* The tables to join to.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $join = array();
	
	
	/**
	* 
	* The WHERE conditions to select on.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $where = array();
	
	
	/**
	* 
	* How to group the selected rows.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $group = array();
	
	
	/**
	* 
	* The HAVING conditions to filter on.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $having = array();
	
	
	/**
	* 
	* The order in which to return rows.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $order = array();
	
	
	/**
	* 
	* The order in which to return rows.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $paging = 10;
	
	
	/**
	* 
	* A fetch mode ('all', 'one', 'row', etc.)
	* 
	* Set to empty/null/false to get a result object.
	* 
	* Eventually, a PDO_FETCHMODE_* constant.
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $fetch = 'all';
	
	
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
		$this->setPaging($this->config['paging']);
		$this->setFetch($this->config['fetch']);
	}
	
	
	/**
	* 
	* Read-only access to properties.
	* 
	* @access public
	* 
	* @return int The paging value.
	* 
	*/

	public function __get($key)
	{
		return $this->$key;
	}
	
	
	/**
	* 
	* Sets the paging value.
	* 
	* @access public
	* 
	* @param int $val The number of rows to page at.
	* 
	* @return void
	* 
	*/

	public function setPaging($val)
	{
		// force a positive integer
		$val = (int) $val;
		if ($val < 1) {
			$val = 1;
		}
		
		// set the paging value
		$this->paging = $val;
	}
	
	
	/**
	* 
	* Sets the fetch-mode value.
	* 
	* @access public
	* 
	* @param string $mode The fetch mode (null to return a result object, or
	* 'all', 'row', 'col', etc. to use a fetch*() method.
	* 
	* @return void
	* 
	*/

	public function setFetch($mode = null)
	{
		$this->fetch = $mode;
	}
	
	
	/**
	* 
	* Clears query properties.
	* 
	* @access public
	* 
	* @param string $key The property to clear; if null, clears all
	* query properties.
	* 
	* @return void
	* 
	*/

	public function clear($key = null)
	{
		$tmp = array('cols', 'from', 'join', 'where', 'group',
			'having', 'order');
		
		if (is_null($key)) {
			foreach ($tmp as $key) {
				$this->$key = array();
			}
		} elseif (in_array($key, $tmp)) {
			$this->$key = array();
		}
	}
	
	
	/**
	* 
	* Adds columns to the query.
	* 
	* @access public
	* 
	* @param array $cols The columns to add.
	* 
	* @param string $table The table-name prefix to add to each column.
	* 
	* @return void
	* 
	*/

	public function cols($cols)
	{
		$spec = func_get_args();
		$this->cols = array_merge($this->cols, $spec);
	}
	
	
	/**
	* 
	* Adds a table to the query, optionally with columns from that table.
	* 
	* @access public
	* 
	* @param mixed $spec The table name string.
	* 
	* @return void
	* 
	*/

	public function from($spec)
	{
		$spec = func_get_args();
		$this->cols = array_merge($this->cols, $spec);
	}
	
	
	/**
	* 
	* Adds joins to the query.
	* 
	* @access public
	* 
	* @param string $table The table name to join to.
	* 
	* @param string $condition Join on this condition.
	* 
	* @param string $type The type of join to perform, e.g.
	* "left", "right", "inner", etc.  Typically not needed.
	* 
	* @return void
	* 
	*/

	public function join($table, $condition, $type = null)
	{
		$tmp = "JOIN $table ON $condition";
		if ($type) {
			$tmp = strtoupper($type) . ' ' . $tmp;
		}
		$this->join[] = $tmp;
	}
	
	
	/**
	* 
	* Adds a WHERE condition to the query.
	* 
	* @access public
	* 
	* @param string $condition The WHERE condition.
	* 
	* @param string $op Whether to 'AND' or 'OR' this condition with
	* existing conditions (default is 'AND').
	* 
	* @return void
	* 
	*/

	public function where($condition, $op = 'AND')
	{
		if ($this->where) {
			$this->where[] = strtoupper($op) . ' ' . $condition;
		} else {
			$this->where[] = $condition;
		}
	}
	
	
	/**
	* 
	* Adds a grouping to the query.
	* 
	* @access public
	* 
	* @param string $column The column to group by.
	* 
	* @return void
	* 
	*/

	public function group()
	{
		$spec = func_get_args();
		$this->group = array_merge($this->group, $spec);
	}
	
	
	/**
	* 
	* Adds a HAVING filter to the query.
	* 
	* @access public
	* 
	* @param string $condition The HAVING filter.
	* 
	* @param string $op Whether to 'AND' or 'OR' this filter with
	* existing filters (default is 'AND').
	* 
	* @return void
	* 
	*/

	public function having($filter, $op = 'AND')
	{
		if ($this->having) {
			$this->having[] = strtoupper($op) . ' ' . $filter;
		} else {
			$this->having[] = $filter;
		}
	}
	
	
	/**
	* 
	* Adds a row order to the query.
	* 
	* @access public
	* 
	* @param string $spec The column and direction to order by.
	* 
	* @return void
	* 
	*/

	public function order($spec)
	{
		$spec = func_get_args();
		$this->order = array_merge($this->order, $spec);
	}
	
	
	/**
	* 
	* Generates a SELECT statement based on the current query properties.
	* 
	* @access public
	* 
	* @return string An SQL SELECT statement.
	* 
	*/

	public function stmt()
	{
		$stmt = "SELECT\n\t";
		$stmt .= implode(',\n\t', $this->cols) . "\n";
		
		$stmt .= "FROM\n\t";
		$stmt .= implode(",\n\t", $this->from) . "\n";
		
		if ($this->join) {
			$stmt .= implode("\n\t", $this->join, "\n");
		}
		
		if ($this->where) {
			$stmt .= "WHERE\n\t";
			$stmt .= implode("\n\t", $this->where) . "\n";
		}
		
		if ($this->group) {
			$stmt .= "GROUP BY\n\t";
			$stmt .= implode(",\n\t", $this->group) . "\n";
		}
		
		if ($this->having) {
			$stmt .= "HAVING\n\t";
			$stmt .= implode("\n\t", $this->having) . "\n";
		}
		
		if ($this->order) {
			$stmt .= "ORDER BY\n\t";
			$stmt .= implode(",\n\t", $this->order) . "\n";
		}
		
		return $stmt;
	}
	
	
	/**
	* 
	* Fetch an array of results based on the current query properties.
	* 
	* @access public
	* 
	* @param string $type The type of fetch to perform (all, one, row, etc).
	* 
	* @param array $data Data to bind to the SELECT statement.
	* 
	* @param int $page The page number of results to fetch.
	* 
	* @return mixed The query results in an array (or as a string, if
	* fetching only 'one').
	* 
	*/

	public function exec($data = null, $page = null)
	{
		if ($this->fetch) {
			$method = 'fetch' . ucfirst(strtolower($this->fetch));
		} else {
			$method = 'exec';
		}
		
		$limit = $this->pageLimit($page);
		
		return $this->sql->$method(
			$this->stmt(), $data, $limit['count'], $limit['offset']
		);
	}
	
	
	/**
	* 
	* Get the count of rows and number of pages for the current query.
	* 
	* @access public
	* 
	* @param array $data Data to bind to the SELECT statement.
	* 
	* @param string $col The column to COUNT() on.
	* 
	* @return array An associative array with keys 'count' (the total number
	* of rows) and 'pages' (the number of pages based on $this->paging).
	* 
	*/

	public function countPages($data = null, $col = '*')
	{
		// make a self-cloned copy so that all settings are identical
		$select = $this->__clone();
		
		// clear out all columns, then add a single COUNT column
		$select->clear('cols');
		$select->cols('COUNT($col)');
		
		// select the count of rows and free the cloned copy
		$select->setFetch('one');
		$result = $select->exec($data);
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
	
	
	/**
	* 
	* Calculates the limit count and offset for a given page number.
	* 
	* Pages are 1-based; page 1 is records 1-10, page 2 is 11-20, and
	* so on.  Page 0 is nonexistent, and will not set a limit.
	* 
	* @access protected
	* 
	* @param int $page The page number to get limits for.
	* 
	* @return array An associative array with 'count' and 'offset' values.
	* 
	*/
	
	protected function pageLimit($page = null)
	{
		if ($page !== null && $page !== false && $page > 0) {
			$count  = $this->paging;
			$offset = $count * ($page - 1);
		} else {
			$count  = null;
			$offset = null;
		}
		
		return array(
			'count'  => $count,
			'offset' => $offset
		);
	}
}
?>