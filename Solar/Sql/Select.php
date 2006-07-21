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
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
 * // select these columns from the 'contacts' table
 * $select->from('contacts', array(
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
 * // on these ANDed conditions
 * $select->where('n_last = :lastname');
 * $select->where('adr_city = :city');
 * 
 * // reverse-ordered by first name
 * $select->order('n_first DESC')
 * 
 * // get 50 per page, when we limit by page
 * $select->setPaging(50);
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
     * : \\sql\\ : (dependency) A Solar_Sql dependency object.
     * 
     * : \\paging\\ : (int) Number of rows per page.
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
     * Record sources, typically "from", "select", and "join".
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
        return $this->fetch('string');
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
     * true).
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function distinct($flag = true)
    {
        $this->_parts['distinct'] = (bool) $flag;
        return $this;
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
     * @return Solar_Sql_Select
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
     * @param string|object $spec If a Solar_Sql_Table object, the table
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
     * @param string|object $spec If a Solar_Sql_Table object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function leftJoin($spec, $cond, $cols)
    {
        $this->_join('LEFT', $spec, $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds an INNER JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Table object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function innerJoin($spec, $cond, $cols)
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
     * @return Solar_Sql_Select
     * 
     */
    public function where($cond)
    {
        if (empty($cond)) {
            return $this;
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
    public function orWhere($cond)
    {
        if (empty($cond)) {
            return $this;
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
        
        // done
        return $this;
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
     * @return Solar_Sql_Select
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
        if (strtoupper($op) == 'OR') {
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
        
        // done
        return $this;
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
     * @return Solar_Sql_Select
     * 
     */
    public function having($cond)
    {
        if (empty($cond)) {
            return $this;
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
    public function orHaving($cond)
    {
        if (empty($cond)) {
            return $this;
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
        
        // done
        return $this;
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
     * @return Solar_Sql_Select
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
        
        // done
        return $this;
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
     * Clears query properties and record sources.
     * 
     * @param string $key The property to clear; if empty, clears all
     * query properties.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function clear($key = null)
    {
        if (empty($key)) {
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
        
        $key = strtolower($key);
        switch ($key) {
            
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
        case 'where':
        case 'group':
        case 'having':
        case 'order':
            $this->_parts[$key] = array();
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
     * @param string $type The type of fetch to perform (all, one, row,
     * etc).  Default is 'result'.
     * 
     * @param string $class When fetching 'all' or 'row', use this as 
     * the return class.
     * 
     * @return mixed The query results.
     * 
     */
    public function fetch($type = 'result', $class = null)
    {
        // build from scratch using the table and record sources.
        $this->_parts['cols'] = array();
        $this->_parts['from'] = array();
        $this->_parts['join'] = array();
        
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
                if ($prefix == '' || $parens) {
                    // no prefix (generally because of countPages()).
                    // if there are parens in the name, it's a function,
                    // so don't prefix it.
                    $this->_parts['cols'][] = $col;
                } else {
                    // auto deconfliction
                    $this->_parts['cols'][] = "{$prefix}.$col";
                }
            }
        }
        
        // perform the fetch
        return $this->_sql->select($type, $this->_parts, $this->_bind, $class);
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
        $select = clone($this);
        $select->clear('limit');
        $select->clear('order');
        
        // clear all columns so there are no name conflicts
        foreach ($select->_sources as $key => $val) {
            $select->_sources[$key]['cols'] = array();
        }
        
        // add a single COUNT() column
        $select->_addSource(
            'cols', // type
            null,         // name
            null,         // orig
            null,         // join
            null,         // cond
            "COUNT($col)"
        );
        
        // get the count and calculate pages
        $count = $select->fetch('one');
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
        $val = $this->_sql->quoteInto($txt, $val);
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
     * (e.g. ' AND '), default null.
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
        // does the name have an "AS" alias?
        $pos = stripos($name, ' AS ');
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
     * @param string|Solar_Sql_Table $spec If a Solar_Sql_Table
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
        
        $this->_sources[$name] = array(
            'type' => $type,
            'name' => $name,
            'orig' => $orig,
            'join' => $join,
            'cond' => $cond,
            'cols' => $cols,
        );
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
?>