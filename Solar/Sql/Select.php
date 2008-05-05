<?php
/**
 * 
 * Class for SQL select generation and results.
 * 
 * {{code: php
 *     $select = Solar::factory('Solar_Sql_Select');
 *     
 *     // select these columns from the 'contacts' table
 *     $select->from('contacts', array(
 *       'id',
 *         'n_last',
 *         'n_first',
 *         'adr_street',
 *         'adr_city',
 *         'adr_region AS state',
 *         'adr_postcode AS zip',
 *         'adr_country',
 *     ));
 *     
 *     // on these ANDed conditions
 *     $select->where('n_last = :lastname');
 *     $select->where('adr_city = :city');
 *     
 *     // reverse-ordered by first name
 *     $select->order('n_first DESC')
 *     
 *     // get 50 per page, when we limit by page
 *     $select->setPaging(50);
 *     
 *     // bind data into the query.
 *     // remember :lastname and :city in the setWhere() calls above.
 *     $data = ('lastname' => 'Jones', 'city' => 'Memphis');
 *     $select->bind($data);
 *     
 *     // limit by which page of results we want
 *     $select->limitPage(1);
 *     
 *     // get a PDOStatement object
 *     $result = $select->fetchPdo();
 *     
 *     // alternatively, get an array of all rows
 *     $rows = $select->fetchAll();
 *     
 *     // or an array of one row
 *     $rows = $select->fetchOne();
 *     
 *     // find out the count of rows, and how many pages there are.
 *     // this comes back as an array('count' => ?, 'pages' => ?).
 *     $total = $select->countPages();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Sql_Select extends Solar_Base {
    
    /**
     * 
     * A constant so we can find "ignored" params, to avoid func_num_args().
     * 
     * The md5() value of 'Solar_Sql_Select::IGNORE', so it should be unique.
     * 
     * Yes, this is hackery, and perhaps a micro-optimization at that.
     * 
     * @const
     * 
     */
    const IGNORE = '--5a333dc50d9341d8e73e56e2ba591b87';
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `sql`
     * : (dependency) A Solar_Sql dependency object.
     * 
     * `paging`
     * : (int) Number of rows per page.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Select = array(
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
        'distinct' => null,
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
     * Column sources, typically "from", "select", and "join".
     * 
     * We use this for automated deconfliction of column names.
     * 
     * @var array
     * 
     */
    protected $_sources = array();
    
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
        $this->setPaging($this->_config['paging']);
    }
    
    /**
     * 
     * Returns this object as an SQL statement string.
     * 
     * @return string An SQL statement string.
     * 
     */
    
    public function __toString()
    {
        return $this->fetch('sql');
    }
    
    /**
     * 
     * Sets the number of rows per page.
     * 
     * @param int $rows The number of rows to page at.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function setPaging($rows)
    {
        // force a positive integer
        $rows = (int) $rows;
        if ($rows < 1) {
            $rows = 1;
        }
        $this->_paging = $rows;
        return $this;
    }
    
    /**
     * 
     * Gets the number of rows per page.
     * 
     * @return int The number of rows per page.
     * 
     */
    public function getPaging()
    {
        return $this->_paging;
    }
    
    /**
     * 
     * Makes the query SELECT DISTINCT.
     * 
     * @param bool $flag Whether or not the SELECT is DISTINCT (default
     * true).  If null, the current distinct setting is not changed.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function distinct($flag = true)
    {
        if ($flag !== null) {
            $this->_parts['distinct'] = (bool) $flag;
        }
        return $this;
    }
    
    /**
     * 
     * Adds 1 or more columns to the SELECT, without regard to a FROM or JOIN.
     * 
     * Multiple calls to cols() will append to the list of columns, not
     * overwrite the previous columns.
     * 
     * @param string|array $cols The column(s) to add to the SELECT.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function cols($cols)
    {
        // save in the sources list
        $this->_addSource(
            'cols',
            null,
            null,
            null,
            null,
            $cols
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a FROM table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to select from; if a string, the table name to select from.
     * 
     * @param array|string $cols The columns to select from this table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function from($spec, $cols = null)
    {
        // the $spec may be a table object, or a string.
        if ($spec instanceof Solar_Sql_Model) {
            
            // get the table name
            $name = $spec->table_name;
            
            // add all columns?
            if ($cols == '*') {
                $cols = array_keys($spec->table_cols);
            }
            
        } else {
            $name = $spec;
        }
        
        // convert to an array with keys 'orig' and 'alias'
        $name = $this->_origAlias($name);
        
        // save in the sources list, overwriting previous values
        $this->_addSource(
            'from',
            $name['alias'],
            $name['orig'],
            null,
            null,
            $cols
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a sub-select and columns to the query.
     * 
     * The format is "FROM ($select) AS $name"; an alias name is
     * always required so we can deconflict columns properly.
     * 
     * @param string|Solar_Sql_Select $spec If a Solar_Sql_Select
     * object, use as the sub-select; if a string, the sub-select
     * command string.
     * 
     * @param string $name The alias name for the sub-select.
     * 
     * @param array|string $cols The columns to retrieve from the 
     * sub-select; by default, '*' (all columns).  This is unlike the
     * normal from() and join() methods, which by default select no
     * columns.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function fromSelect($spec, $name, $cols = '*')
    {
        // the $spec may be a select object, or a string
        if ($spec instanceof self) {
            // get the select object as a string.
            $spec = $spec->__toString();
        }
        
        // save in the sources list, overwriting previous values
        $this->_addSource(
            'select',
            $name,
            $spec,
            null,
            null,
            $cols
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function join($spec, $cond, $cols = null)
    {
        $this->_join(null, $spec, $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds a LEFT JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function leftJoin($spec, $cond, $cols = null)
    {
        $this->_join('LEFT', $spec, $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds an INNER JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function innerJoin($spec, $cond, $cols = null)
    {
        $this->_join('INNER', $spec, $cond, $cols);
        return $this;
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
     * {{code: php
     *     // simplest but non-secure
     *     $select->where("id = $id");
     *     
     *     // secure
     *     $select->where('id = ?', $id);
     *     
     *     // equivalent security with named binding
     *     $select->where('id = :id');
     *     $select->bind('id', $id);
     * }}
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param string $val A single value to quote into the condition.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function where($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['where']) {
            $this->_parts['where'][] = "AND $cond";
        } else {
            $this->_parts['where'][] = $cond;
        }
        
        // done
        return $this;
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
     * @return Solar_Sql_Select
     * 
     * @see where()
     * 
     */
    public function orWhere($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['where']) {
            $this->_parts['where'][] = "OR $cond";
        } else {
            $this->_parts['where'][] = $cond;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds multiple WHERE conditions to the query.
     * 
     * @param array $list An array of WHERE conditions.  Conditions starting
     * with "OR" and "AND" are honored correctly.
     * 
     * @param string $op If a condition does not explicitly start with "AND"
     * or "OR", connect the condition with this operator.  Default "AND".
     * 
     * @return Solar_Sql_Select
     * 
     * @see _multiWhere()
     * 
     */
    public function multiWhere($list, $op = 'AND')
    {
        $op = strtoupper(trim($op));
        
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer key means a literal condition
                // and no value to be quoted into it
                $this->_multiWhere($val, Solar_Sql_Select::IGNORE, $op);
            } else {
                // string $key means the key is a condition,
                // and the $val should be quoted into it.
                $this->_multiWhere($key, $val, $op);
            }
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Backend support for multiWhere().
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param mixed $val A value (if any) to quote into the condition.
     * 
     * @param string $op The implicit operator to use for the condition, if
     * needed.
     * 
     * @see where()
     * 
     * @see orWhere()
     * 
     */
    public function _multiWhere($cond, $val, $op)
    {
        if (strtoupper(substr($cond, 0, 3)) == 'OR ') {
            // explicit OR
            $cond = substr($cond, 3);
            $this->orWhere($cond, $val);
        } elseif (strtoupper(substr($cond, 0, 4)) == 'AND ') {
            // explicit AND
            $cond = substr($cond, 4);
            $this->where($cond, $val);
        } elseif ($op == 'OR') {
            // implicit OR
            $this->orWhere($cond, $val);
        } else {
            // implicit AND (the default)
            $this->where($cond, $val);
        }
    }
    
    /**
     * 
     * Adds grouping to the query.
     * 
     * @param string|array $spec The column(s) to group by.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function group($spec)
    {
        if (empty($spec)) {
            return $this;
        }
        
        if (is_string($spec)) {
            $spec = explode(',', $spec);
        } else {
            settype($spec, 'array');
        }
        
        $this->_parts['group'] = array_merge($this->_parts['group'], $spec);
        return $this;
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
     * {{code: php
     *     // simplest but non-secure
     *     $select->having("COUNT(id) = $count");
     *     
     *     // secure
     *     $select->having('COUNT(id) = ?', $count);
     *     
     *     // equivalent security with named binding
     *     $select->having('COUNT(id) = :count');
     *     $select->bind('count', $count);
     * }}
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param string $val A single value to quote into the condition.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function having($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['having']) {
            $this->_parts['having'][] = "AND $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }
        
        // done
        return $this;
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
     * @return Solar_Sql_Select
     * 
     * @see having()
     * 
     */
    public function orHaving($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['having']) {
            $this->_parts['having'][] = "OR $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds multiple HAVING conditions to the query.
     * 
     * @param array $list An array of HAVING conditions.  Conditions starting
     * with "OR" and "AND" are honored correctly.
     * 
     * @param string $op If a condition does not explicitly start with "AND"
     * or "OR", connect the condition with this operator.  Default "AND".
     * 
     * @return Solar_Sql_Select
     * 
     * @see _multiHaving()
     * 
     */
    public function multiHaving($list, $op = 'AND')
    {
        $op = strtoupper(trim($op));
        
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer key means a literal condition
                // and no value to be quoted into it
                $this->_multiHaving($val, Solar_Sql_Select::IGNORE, $op);
            } else {
                // string $key means the key is a condition,
                // and the $val should be quoted into it.
                $this->_multiHaving($key, $val, $op);
            }
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Backend support for multiHaving().
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param mixed $val A value (if any) to quote into the condition.
     * 
     * @param string $op The implicit operator to use for the condition, if
     * needed.
     * 
     * @see having()
     * 
     * @see orHaving()
     * 
     */
    public function _multiHaving($cond, $val, $op)
    {
        if (strtoupper(substr($cond, 0, 3)) == 'OR ') {
            // explicit OR
            $cond = substr($cond, 3);
            $this->orHaving($cond, $val);
        } elseif (strtoupper(substr($cond, 0, 4)) == 'AND ') {
            // explicit AND
            $cond = substr($cond, 4);
            $this->having($cond, $val);
        } elseif ($op == 'OR') {
            // implicit OR
            $this->orHaving($cond, $val);
        } else {
            // implicit AND (the default)
            $this->having($cond, $val);
        }
    }
    
    /**
     * 
     * Adds a row order to the query.
     * 
     * @param string|array $spec The column(s) and direction to order by.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function order($spec)
    {
        if (empty($spec)) {
            return $this;
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
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets a limit count and offset to the query.
     * 
     * @param int $count The number of rows to return.
     * 
     * @param int $offset Start returning after this many rows.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function limit($count = null, $offset = null)
    {
        $this->_parts['limit']['count']  = (int) $count;
        $this->_parts['limit']['offset'] = (int) $offset;
        return $this;
    }
    
    /**
     * 
     * Sets the limit and count by page number.
     * 
     * @param int $page Limit results to this page number.
     * 
     * @return Solar_Sql_Select
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
        
        // done
        return $this;
    }
    
    /**
     * 
     * Clears query properties and row sources.
     * 
     * @param string $part The property to clear; if empty, clears all
     * query properties.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function clear($part = null)
    {
        if (empty($part)) {
            // clear all parts
            $this->_parts = array(
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
            
            // clear all table/join sources
            $this->_sources = array();
            
            // done
            return $this;
        }
        
        $part = strtolower($part);
        switch ($part) {
            
        case 'distinct':
            $this->_parts['distinct'] = false;
            break;
        
        case 'from':
            $this->_parts['from'] = array();
            foreach ($this->_sources as $skey => $sval) {
                if ($sval['type'] == 'from' || $sval['type'] == 'select') {
                    unset($this->_sources[$skey]);
                }
            }
            break;
        
        case 'join':
            $this->_parts['join'] = array();
            foreach ($this->_sources as $skey => $sval) {
                if ($sval['type'] == 'join') {
                    unset($this->_sources[$skey]);
                }
            }
            break;
        
        case 'limit':
            $this->_parts['limit'] = array(
                'count'  => 0,
                'offset' => 0
            );
            break;
        
        case 'cols':
            $this->_parts['cols'] = array();
            foreach ($this->_sources as $skey => $sval) {
                $this->_sources[$skey]['cols'] = array();
            }
            break;
        
        case 'where':
        case 'group':
        case 'having':
        case 'order':
            $this->_parts[$part] = array();
            break;
        }
        
        // done
        return $this;
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
     * @return Solar_Sql_Select
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
        
        // done
        return $this;
    }
    
    /**
     * 
     * Unsets bound data.
     * 
     * @param mixed $spec The key to unset.  If a string, unsets that one
     * bound value; if an array, unsets the list of values; if empty, unsets
     * all bound values (the default).
     * 
     * @return Solar_Sql_Select
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
        
        // done
        return $this;
    }
    
    /**
     * 
     * Fetch the results based on the current query properties.
     * 
     * @param string $type The type of fetch to perform (all, one, col,
     * etc).  Default is 'pdo'.
     * 
     * @return mixed The query results.
     * 
     */
    public function fetch($type = 'pdo')
    {
        // does the fetch-method exist? (this allows for extended
        // adapters  to define their own fetch methods)
        $fetch = 'fetch' . ucfirst($type);
        if (! method_exists($this->_sql, $fetch)) {
            throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
                'method' => $fetch
            ));
        }
        
        // build from scratch using the table and row sources.
        $this->_parts['cols'] = array();
        $this->_parts['from'] = array();
        $this->_parts['join'] = array();
        
        // get a count of how many sources there are. if there's only 1, we
        // won't use column-name prefixes below. this will help soothe SQLite
        // on JOINs of sub-selects.
        // 
        // e.g., `JOIN (SELECT alias.col FROM tbl AS alias) ...`  won't work
        // right, SQLite needs `JOIN (SELECT col AS col FROM tbl AS alias)`.
        // 
        // @todo Use $this->disambiguate() instead?
        // 
        $count_sources = count($this->_sources);
        
        // build from sources.
        foreach ($this->_sources as $source) {
            
            // build the from and join parts.  note that we don't
            // build from 'cols' sources, since they are just named
            // columns without reference to a particular from or join.
            $build = ucfirst($source['type']);
            if ($build != 'Cols') {
                $method = "_build$build";
                $this->$method(
                    $source['name'],
                    $source['orig'],
                    $source['join'],
                    $source['cond']
                );
            }
            
            // determine a prefix for the columns from this source
            if ($source['type'] == 'select' ||
                $source['name'] != $source['orig']) {
                // use the alias name, not the original name, for sub-
                // selects, and where aliases are explicitly named.
                $prefix = $source['name'];
            } else {
                // use the original name
                $prefix = $source['orig'];
            }
            
            // add each of the columns from the source, deconflicting
            // along the way.
            foreach ($source['cols'] as $col) {
        
                // does it use a function?
                $parens = strpos($col, '(');
                
                // choose our column-name deconfliction strategy
                if ($prefix == '' || $parens || $count_sources == 1) {
                    // - if no prefix, that's a no-brainer.
                    // - if there are parens in the name, it's a function.
                    // - if there's only one source, deconfliction not needed.
                    $this->_parts['cols'][] = $col;
                } else {
                    // auto deconfliction
                    $this->_parts['cols'][] = "{$prefix}.$col";
                }
            }
        }
        
        // return the fetch result
        return $this->_sql->$fetch($this->_parts, $this->_bind);
    }
    
    /**
     * 
     * Fetches all rows from the database using sequential keys.
     * 
     * @return array
     * 
     */
    public function fetchAll()
    {
        return $this->fetch('all');
    }
    
    /**
     * 
     * Fetches all rows from the database using associative keys (defined by
     * the first column).
     * 
     * N.b.: if multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     * 
     * @return array
     * 
     */
    public function fetchAssoc()
    {
        return $this->fetch('assoc');
    }
    
    /**
     * 
     * Fetches the first column of all rows as a sequential array.
     * 
     * @return array
     * 
     */
    public function fetchCol()
    {
        return $this->fetch('col');
    }
    
    /**
     * 
     * Fetches the very first value (i.e., first column of the first row).
     * 
     * @return mixed
     * 
     */
    public function fetchValue()
    {
        return $this->fetch('value');
    }
    
    /**
     * 
     * Fetches an associative array of all rows as key-value pairs (first 
     * column is the key, second column is the value).
     * 
     * @return array
     * 
     */
    public function fetchPairs()
    {
        return $this->fetch('pairs');
    }
    
    /**
     * 
     * Fetches a PDOStatement result object.
     * 
     * @return PDOStatement
     * 
     */
    public function fetchPdo()
    {
        return $this->fetch('pdo');
    }
    
    /**
     * 
     * Fetches one row from the database.
     * 
     * @return array
     * 
     */
    public function fetchOne()
    {
        return $this->fetch('one');
    }
    
    /**
     * 
     * Builds the SQL statement and returns it as a string instead of 
     * executing it.  Useful for debugging.
     * 
     * @return string
     * 
     */
    public function fetchSql()
    {
        return $this->fetch('sql');
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
        // prepare a copy of this object as a COUNT query
        $select = clone($this);
        
        // no limit, and no need to order rows
        $select->clear('limit');
        $select->clear('order');
        
        // clear all columns so there are no name conflicts
        foreach ($select->_sources as $key => $val) {
            $select->_sources[$key]['cols'] = array();
        }
        
        // look for a DISTINCT setting
        $is_distinct = $select->_parts['distinct'];
        
        // look in the WHERE and HAVING clauses for a `COUNT` condition
        $has_count_cond = $this->_hasCountCond($select->_parts['where']) ||
                          $this->_hasCountCond($select->_parts['having']);
        
        // is there a count condition or a distinct?
        if ($has_count_cond || $is_distinct) {
            
            // count on a sub-select instead.
            $count = $this->_countSubSelect($select, $col);
            
        } else {
            
            // "normal" case (no count condition in WHERE or HAVING).
            // add the one column we're counting on...
            $select->_addSource(
                'cols',         // type
                null,           // name
                null,           // orig
                null,           // join
                null,           // cond
                "COUNT($col)"
            );
            
            // ... and do the count.
            $count = $select->fetchValue();
        }
        
        // calculate pages
        $pages = 0;
        if ($count > 0) {
            $pages = ceil($count / $this->_paging);
        }
        
        // done!
        return array(
            'count' => $count,
            'pages' => $pages,
        );
    }
    
    protected function _hasCountCond($parts)
    {
        foreach ($parts as $key => $val) {
            if (is_int($key)) {
                // val is a literal condition
                $cond = strtoupper($val);
            } else {
                // key is a condition with a placeholder,
                // and val is the placeholder value.
                $cond = strtoupper($key);
            }
            // does the condition have COUNT in it?
            if (strpos($cond, 'COUNT') !== false) {
                return true;
            }
        }
        // no COUNT condition found
        return false;
    }
    
    protected function _countSubSelect($inner, $col)
    {
        // add the one column we're counting on, to the inner subselect
        $inner->_addSource(
            'cols',         // type
            null,           // name
            null,           // orig
            null,           // join
            null,           // cond
            $col
        );
        
        // does the counting column have a dot in it?
        $pos = strpos($col, '.');
        if ($pos) {
            // alias the subselect to the same table name as the column
            $alias = substr($col, 0, $pos);
            $col   = substr($col, $pos + 1);
        } else {
            // default alias 'subselect' in lieu of an explicit alias
            $alias = 'subselect';
        }
        
        // build the outer select, which will do the actual count.
        // wrapping with an outer select lets us have all manner of weirdness
        // in the inner query, so that it doesn't conflict with the count.
        $outer = clone($this);
        $outer->clear();
        $outer->fromSelect($inner, $alias, "COUNT($alias.$col)");
        
        // get the count
        return $outer->fetchValue();
    }
    
    /**
     * 
     * Safely quotes a value for an SQL statement.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return string An SQL-safe quoted value (or a string of 
     * separated-and-quoted values).
     * 
     * @see Solar_Sql::quote()
     * 
     */
    public function quote($val)
    {
        return $this->_sql->quote($val);
    }
    
    /**
     * 
     * Quotes a value and places into a piece of text at a placeholder.
     * 
     * @param string $txt The text with a placeholder.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return mixed An SQL-safe quoted value (or string of separated values)
     * placed into the orignal text.
     * 
     * @see Solar_Sql::quoteInto()
     * 
     */
    public function quoteInto($txt, $val)
    {
        return $this->_sql->quoteInto($txt, $val);
    }
    
    /**
     * 
     * Quote multiple text-and-value pieces.
     * 
     * @param array $list A series of key-value pairs where the key is
     * the placeholder text and the value is the value to be quoted into
     * it.  If the key is an integer, it is assumed that the value is
     * piece of literal text to be used and not quoted.
     * 
     * @param string $sep Return the list pieces separated with this string
     * (for example ' AND '), default null.
     * 
     * @return string An SQL-safe string composed of the list keys and
     * quoted values.
     * 
     * @see Solar_Sql::quoteMulti()
     * 
     */
    public function quoteMulti($list, $sep = null)
    {
        return $this->_sql->quoteMulti($list, $sep);
    }
    
    /**
     * 
     * Disambiguates columns in certain situations, per the adapter.
     * 
     * We need this because sometimes we need to put a single column in place,
     * but some adapters need it fully-qualified, and others fail when it it
     * fully qualified.  This lets the adapter specify the correct behavior.
     * 
     * @param string $table The table name.
     * 
     * @param string $col The column name.
     * 
     * @return string Some adapters (SQLite) return only the column name,
     * others return "table.col".
     * 
     */
    public function disambiguate($table, $col)
    {
        return $this->_sql->disambiguate($table, $col);
    }
    
    // -----------------------------------------------------------------
    // 
    // Protected support functions
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns an identifier as an "original" name and an "alias".
     * 
     * Effectively splits the identifier at "AS", so that "foo AS bar"
     * becomes array('orig' => 'foo', 'alias' => 'bar').
     * 
     * @param string $name The string identifier.
     * 
     * @return array The $name string as an array with keys 'name' and
     * 'alias'.
     * 
     */
    protected function _origAlias($name)
    {
        // does the name have an "AS" alias? pick the right-most one near the
        // end of the string (note the "rr" in strripos).
        $pos = strripos($name, ' AS ');
        if ($pos !== false) {
            return array(
                'orig'  => trim(substr($name, 0, $pos)),
                'alias' => trim(substr($name, $pos + 4)),
            );
        } else {
            return array(
                'orig'  => trim($name),
                'alias' => trim($name),
            );
        }
    }
    
    /**
     * 
     * Support method for adding JOIN clauses.
     * 
     * @param string $type The type of join; empty for a plain JOIN, or
     * "LEFT", "INNER", etc.
     * 
     * @param string|Solar_Sql_Model $spec If a Solar_Sql_Model
     * object, the table to join to; if a string, the table name to
     * join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the
     * joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _join($type, $spec, $cond, $cols)
    {
        // the $spec may be a table object, or a string.
        if ($spec instanceof Solar_Sql_Model) {
            
            // get the table name
            $name = $spec->table_name;
            
            // add all columns?
            if ($cols == '*') {
                $cols = array_keys($spec->table_cols);
            }
            
        } else {
            $name = $spec;
        }
        
        // convert to an array of orig and alias
        $name = $this->_origAlias($name);
        
        // save in the sources list, overwriting previous values
        $this->_addSource(
            'join',
            $name['alias'],
            $name['orig'],
            $type,
            $cond,
            $cols
        );
        
        return $this;
    }
    
    /**
     * 
     * Adds a row source (from table, from select, or join) to the 
     * sources array.
     * 
     * @param string $type The source type: 'from', 'join', or 'select'.
     * 
     * @param string $name The alias name.
     * 
     * @param string $orig The source origin, either a table name or a 
     * sub-select statement.
     * 
     * @param string $join If $type is 'join', the type of join ('left',
     * 'inner', or null for a regular join).
     * 
     * @param string $cond If $type is 'join', the join conditions.
     * 
     * @param array $cols The columns to select from the source.
     * 
     * @return void
     * 
     */
    protected function _addSource($type, $name, $orig, $join, $cond, $cols)
    {
        if (is_string($cols)) {
            $cols = explode(',', $cols);
        }
        
        settype($cols, 'array');
        foreach ($cols as $key => $val) {
            $cols[$key] = trim($val);
        }
        
        if ($type == 'cols') {
            $this->_sources[] = array(
                'type' => $type,
                'name' => $name,
                'orig' => $orig,
                'join' => $join,
                'cond' => $cond,
                'cols' => $cols,
            );
        } else {
            $this->_sources[$name] = array(
                'type' => $type,
                'name' => $name,
                'orig' => $orig,
                'join' => $join,
                'cond' => $cond,
                'cols' => $cols,
            );
        }
    }
    
    /**
     * 
     * Builds $this->_parts['from'] using a 'from' source.
     * 
     * @param string $name The table alias.
     * 
     * @param string $orig The original table name.
     * 
     * @return void
     * 
     */
    protected function _buildFrom($name, $orig)
    {
        if ($name == $orig) {
            $this->_parts['from'][] = $name;
        } else {
            $this->_parts['from'][] = "$orig AS $name";
        }
    }
    
    /**
     * 
     * Builds $this->_parts['join'] using a 'join' source.
     * 
     * @param string $name The table alias.
     * 
     * @param string $orig The original table name.
     * 
     * @param string $join The join type (null, 'left', 'inner', etc).
     * 
     * @param string $cond Join conditions.
     * 
     * @return void
     * 
     */
    protected function _buildJoin($name, $orig, $join, $cond)
    {
        $tmp = array(
            'type' => $join,
            'name' => null,
            'cond' => $cond,
        );
        
        if ($name == $orig) {
            $tmp['name'] = $name;
        } else {
            $tmp['name'] = "$orig AS $name";
        }
        
        $this->_parts['join'][] = $tmp;
    }
    
    /**
     * 
     * Builds $this->_parts['from'] using a 'select' source.
     * 
     * @param string $name The subselect alias.
     * 
     * @param string $orig The subselect command string.
     * 
     * @return void
     * 
     */
    protected function _buildSelect($name, $orig)
    {
        $this->_parts['from'][] = "($orig) AS $name";
    }
}
