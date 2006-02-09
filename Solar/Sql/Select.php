<?php
/**
 * 
 * Class for SQL select generation and results.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Needed for instanceof comparisons.
 */
Solar::loadClass('Solar_Sql_Table');

/**
 * 
 * Class for SQL select generation and results.
 * 
 * Example usage:
 * 
 * <code>
 * $select = Solar::factory('Solar_Sql_Select');
 * 
 * // select these columns
 * $select->cols(array(
 *   'id',
 *     'n_last',
 *     'n_first',
 *     'adr_street',
 *     'adr_city',
 *     'adr_region AS state',
 *     'adr_postcode AS zip',
 *     'adr_country',
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
 * @package Solar_Sql
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
     * @var array
     * 
     */
    protected $_config = array(
        'sql'    => 'sql',
        'paging' => 10,
    );
    
    /**
     * 
     * Data to bind into the query as key => value pairs.
     * 
     * @var array
     * 
     */
    protected $_bind = array();
    
    /**
     * 
     * The component parts of a select statement.
     * 
     * @var array
     * 
     */
    protected $_parts = array(
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
     * @var int
     * 
     */
    protected $_paging = 10;
    
    /**
     * 
     * Tracks which columns are being select from each table and join.
     * 
     * We use this for automated deconfliction.
     * 
     * @var array
     * 
     */
    protected $_tbl_cols = array();
    
    
    /**
     * 
     * Internal Solar_Sql object.
     * 
     * @var Solar_Sql
     * 
     */
    protected $_sql;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic construction
        parent::__construct($config);
        
        // connect to the database with dependency injection
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // set up defaults
        $this->paging($this->_config['paging']);
    }
    
    /**
     * 
     * Read-only access to properties.
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
        $this->_paging = $rows;
    }
    
    /**
     * 
     * Makes the query SELECT DISTINCT.
     * 
     * @param bool $flag Whether or not the SELECT is DISTINCT (default
     * true).
     * 
     * @return void
     * 
     */
    public function distinct($flag = true)
    {
        $this->_parts['distinct'] = (bool) $flag;
    }
    
    /**
     * 
     * Add un-mapped columns to the query.
     * 
     * @param string|array The list of columns.
     * 
     * @return void
     * 
     */
    public function cols($spec)
    {
        // track them as related to no specific table
        $this->_tblCols('', $spec);
    }
    
    /**
     * 
     * Adds a FROM table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Table object, the table
     * to select from; if a string, the table name to select from.
     * 
     * @param array|string $cols The columns to select from this table.
     * 
     * @return void
     * 
     */
    public function from($spec, $cols = null)
    {
        // the $spec may be a table object, or a string.
        if ($spec instanceof Solar_Sql_Table) {
            
            // get the table name
            $name = $spec->name;
            
            // add all columns?
            if ($cols == '*') {
                $cols = array_keys($spec->col);
            }
            
        } else {
            $name = $spec;
        }
        
        // add the table to the 'from' list
        $this->_parts['from'] = array_merge(
            $this->_parts['from'],
            (array) $name
        );
        
        // add to the columns from this table
        $this->_tblCols($name, $cols);
    }
    
    /**
     * 
     * Adds a JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Table object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return void
     * 
     */
    public function join($spec, $cond, $cols = null)
    {
        // the $spec may be a table object, or a string.
        if ($spec instanceof Solar_Sql_Table) {
            
            // get the table name
            $name = $spec->name;
            
            // add all columns?
            if ($cols == '*') {
                $cols = array_keys($spec->col);
            }
            
        } else {
            $name = $spec;
        }
        
        $this->_parts['join'][] = array(
            'type' => null,
            'name' => $name,
            'cond' => $cond
        );
        
        // add to the columns from this joined table
        $this->_tblCols($name, $cols);
    }
    
    /**
     * 
     * Adds a WHERE condition to the query by AND.
     * 
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears.
     * 
     * Array values are quoted and comma-separated.
     * 
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     * 
     * // secure
     * $select->where('id = ?', $id);
     * 
     * // equivalent security with named binding
     * $select->where('id = :id');
     * $select->bind('id', $id);
     * </code>
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param string $val A single value to quote into the condition.
     * 
     * @return void
     * 
     */
    public function where($cond)
    {
        if (empty($cond)) {
            return;
        }
        
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['where']) {
            $this->_parts['where'][] = "AND $cond";
        } else {
            $this->_parts['where'][] = $cond;
        }
    }
    
    /**
     * 
     * Adds a WHERE condition to the query by OR.
     * 
     * Otherwise identical to where().
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param string $val A value to quote into the condition.
     * 
     * @return void
     * 
     * @see where()
     * 
     */
    public function orWhere($cond)
    {
        if (empty($cond)) {
            return;
        }
        
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['where']) {
            $this->_parts['where'][] = "OR $cond";
        } else {
            $this->_parts['where'][] = $cond;
        }
    }
    
    /**
     * 
     * Adds multiple WHERE conditions to the query.
     * 
     * Otherwise identical to where()/orWhere().
     * 
     * @param array $list An array of WHERE conditions.
     * 
     * @param string $op How to add the conditions, by 'AND' (the
     * default) or by 'OR'.
     * 
     * @return void
     * 
     * @see where()
     * 
     * @see orWhere()
     * 
     */
    public function multiWhere($list, $op = 'AND')
    {
        // normally use where() ...
        $method = 'where';
        if ($op == 'OR') {
            // unless it's orWhere().
            $method = 'orWhere';
        }
        
        // add each condition.
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer key means a literal condition
                // and no value to be quoted into it
                $this->$method($val);
            } else {
                // string $key means the key is a condition,
                // and the $val should be quoted into it.
                $this->$method($key, $val);
            }
        }
    }
    
    /**
     * 
     * Adds grouping to the query.
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
        
        $this->_parts['group'] = array_merge($this->_parts['group'], $spec);
    }
    
    /**
     * 
     * Adds a HAVING condition to the query by AND.
     * 
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears.
     * 
     * Array values are quoted and comma-separated.
     * 
     * <code>
     * // simplest but non-secure
     * $select->having("COUNT(id) = $count");
     * 
     * // secure
     * $select->having('COUNT(id) = ?', $count);
     * 
     * // equivalent security with named binding
     * $select->having('COUNT(id) = :count');
     * $select->bind('count', $count);
     * </code>
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param string $val A single value to quote into the condition.
     * 
     * @return void
     * 
     */
    public function having($cond)
    {
        if (empty($cond)) {
            return;
        }
        
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['having']) {
            $this->_parts['having'][] = "AND $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }
    }
    
    /**
     * 
     * Adds a HAVING condition to the query by OR.
     * 
     * Otherwise identical to orHaving().
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param string $val A single value to quote into the condition.
     * 
     * @return void
     * 
     * @see having()
     * 
     */
    public function orHaving($cond)
    {
        if (empty($cond)) {
            return;
        }
        
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['having']) {
            $this->_parts['having'][] = "OR $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }
    }
    
    /**
     * 
     * Adds multiple HAVING conditions to the query.
     * 
     * Otherwise identical to having()/orHaving().
     * 
     * @param array $list An array of HAVING conditions.
     * 
     * @param string $op How to add the conditions, by 'AND' (the
     * default) or by 'OR'.
     * 
     * @return void
     * 
     * @see having()
     * 
     * @see orHaving()
     * 
     */
    public function multiHaving($list, $op = 'AND')
    {
        $method = 'having';
        if (strtoupper($op) == 'OR') {
            $method = 'orHaving';
        }
        
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer key means a literal condition
                // and no value to be quoted into it
                $this->$method($val);
            } else {
                // string $key means the key is a condition,
                // and the $val should be quoted into it.
                $this->$method($key, $val);
            }
        }
    }
    
    /**
     * 
     * Adds a row order to the query.
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
        $this->_parts['order'] = array_merge($this->_parts['order'], $spec);
    }
    
    /**
     * 
     * Sets a limit count and offset to the query.
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
        $this->_parts['limit']['count']  = (int) $count;
        $this->_parts['limit']['offset'] = (int) $offset;
    }
    
    /**
     * 
     * Sets the limit and count by page number.
     * 
     * @param int $page Limit results to this page number.
     * 
     * @return void
     * 
     */
    public function limitPage($page = null)
    {
        // reset the count and offset
        $this->_parts['limit']['count']  = 0;
        $this->_parts['limit']['offset'] = 0;
        
        // determine the count and offset from the page number
        $page = (int) $page;
        if ($page > 0) {
            $this->_parts['limit']['count']  = $this->_paging;
            $this->_parts['limit']['offset'] = $this->_paging * ($page - 1);
        }
    }
    
    /**
     * 
     * Clears query properties.
     * 
     * @param string $key The property to clear; if empty, clears all
     * query properties.
     * 
     * @return void
     * 
     */
    public function clear($key = null)
    {
        $list = array_keys($this->_parts);
        
        if (empty($key)) {
            // clear all
            foreach ($list as $key) {
                $this->_parts[$key] = array();
            }
        } elseif (in_array($key, $list)) {
            // clear some
            $this->_parts[$key] = array();
        }
        
        // make sure limit has a count and offset
        if (empty($this->_parts['limit'])) {
            $this->_parts['limit'] = array(
                'count' => 0,
                'offset' => 0
            );
        }
    }
    
    /**
     * 
     * Adds data to bind into the query.
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
            $this->_bind = array_merge($this->_bind, $key);
        } elseif (is_object($key)) {
            $this->_bind = array_merge((array) $this->_bind, $key);
        } else {
            $this->_bind[$key] = $val;
        }
    }
    
    /**
     * 
     * Unsets bound data.
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
            $this->_bind = array();
        } else {
            settype($spec, 'array');
            foreach ($spec as $key) {
                unset($this->_bind[$key]);
            }
        }
    }
    
    /**
     * 
     * Fetch the results based on the current query properties.
     * 
     * @param string $type The type of fetch to perform (all, one, row, etc).
     * 
     * @return mixed The query results.
     * 
     */
    public function fetch($type = 'result')
    {
        // build up the $parts['cols'] from scratch.
        $this->_parts['cols'] = array();
        
        // how many tables/joins to select from?
        $count = count(array_keys($this->_tbl_cols));
        
        // add table/join column names with deconfliction
        foreach ($this->_tbl_cols as $tbl => $cols) {
            
            // set up a table/join prefix.
            // is the table/join aliased?
            $pos = stripos($tbl, ' AS ');
            if ($pos) {
                // yes, use the alias portion as the prefix
                $pre = trim(substr($tbl, $pos + 4));
            } else {
                // no, just use the table/join name as the prefix.
                $pre = trim($tbl);
            }
            
            // add each of the columns, deconflicting as we go
            foreach ($cols as $col) {
            
                // is the column name aliased?
                $aliased = stripos($col, ' AS ');
                
                // is it starred?
                $starred = strpos($col, '*');
                
                // does it use a function?
                $parens = strpos($col, '(');
                
                // choose our column-name deconfliction strategy
                if ($pre == '' || $aliased || $parens) {
                    // no prefix, aliased, or uses parentheses.
                    // use the column-name as-is.
                    $this->_parts['cols'][] = $col;
                } elseif ($count == 1) {
                    // only one table with columns: minimal deconfliction.
                    // need to see if the column is a star.
                    if ($starred !== false) {
                        $this->_parts['cols'][] = "{$pre}.$col";
                    } else {
                        $this->_parts['cols'][] = "{$pre}.$col AS $col";
                    }
                } else {
                    // more than one table: full deconfliction, except for
                    // starred ones.
                    if ($starred !== false) {
                        $this->_parts['cols'][] = "{$pre}.$col";
                    } else {
                        $this->_parts['cols'][] = "{$pre}.$col AS {$pre}__$col";
                    }
                }
            }
        }
        
        // perform the select query and return the results
        return $this->_sql->select($type, $this->_parts, $this->_bind);
    }
    
    /**
     * 
     * Get the count of rows and number of pages for the current query.
     * 
     * @param string $col The column to COUNT() on.  Default is 'id'.
     * 
     * @return array An associative array with keys 'count' (the total number
     * of rows) and 'pages' (the number of pages based on $this->_paging).
     * 
     */
    public function countPages($col = 'id')
    {
        // make a self-cloned copy so that all settings are identical
        $select = clone($this);
        
        // clear out all columns (note that this works because we are
        // already in a Select class; this wouldn't work externally
        // because $cols is protected) ...
        $select->_tbl_cols = array();
        
        // ... then add a single COUNT column (no need for a table name
        // in this case)
        $select->cols("COUNT($col)");
        
        // clear any order (for Postgres, noted by 4bgjnsn)
        $select->clear('order');
        
        // clear any limits
        $select->clear('limit');
        
        // select the count of rows and free the cloned copy
        $result = $select->fetch('one');
        unset($select);
        
        // $result is the row-count; how many pages does it convert to?
        $pages = 0;
        if ($result > 0) {
            $pages = ceil($result / $this->_paging);
        }
        
        // done!
        return array(
            'count' => $result,
            'pages' => $pages
        );
    }
    
    /**
     * 
     * Adds to the internal table-to-column mapping array.
     * 
     * @param string $tbl The table/join the columns come from.
     * 
     * @param string|array $cols The list of columns; preferably as
     * an array, but possibly as a comma-separated string.
     * 
     * @return void
     * 
     */
    protected function _tblCols($tbl, $cols)
    {
        if (is_string($cols)) {
            $cols = explode(',', $cols);
        } else {
            settype($cols, 'array');
        }
        
        // only add columns if non-blank
        if (! empty($cols)) {
            
            // trim everything up ...
            array_walk($cols, 'trim');
            
            // ... and merge them into the tbl_cols mapping.
            if (empty($this->_tbl_cols[$tbl])) {
                // this table/join not previously used
                $this->_tbl_cols[$tbl] = $cols;
            } else {
                // merge with existing columns for this table/join
                $this->_tbl_cols[$tbl] = array_merge(
                    $this->_tbl_cols[$tbl],
                    $cols
                );
            }
        }
    }
}
?>