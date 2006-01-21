<?php
/**
 * 
 * Class for iterating through selected row results.
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
 * 
 * Class for fetching selected row results.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Result extends Solar_Base {
    
    /**
     * 
     * User-defined configuration keys.
     * 
     * Keys are:
     * 
     * PDOStatment: (object) A PDOStatement object to be used as the
     * result source.
     * 
     * @access protected
     * 
     * @var array
     */
    protected $_config = array(
        'PDOStatement' => null,
    );
    
    /**
     * 
     * Fetches one row from the result source.
     * 
     * If a row name has double-underscores, the result is placed into a
     * sub-array named for the part before the double-underscores.  For
     * example, if the row-name is "example__row_name", then you would
     * get back example['row_name'].
     * 
     * When combined with the automated deconfliction in
     * Solar_Sql_Select, this allows you to select from multiple tables
     * and segregate the columns from different tables automatically into
     * separate arrays.
     * 
     * @access public
     * 
     * @param int $mode A PDO::FETCH_* constant to specify how the row
     * should be returned; default is PDO::FETCH_ASSOC.
     * 
     * @return array An array of data from the fetched row.
     * 
     * @todo In colname-to-array deconfliction: what if a natural colname
     * is the same as a table name? Then the one will overwrite the
     * other, which is bad juju.  Perhaps a separate array for table-based,
     * and another for non-table-based, and merge at the end?
     * 
     */
    public function fetch($mode = PDO::FETCH_ASSOC)
    {
        // the fetched row data to be returned
        $row = array();
        
        // the data as originally returned by PDOStatement
        $orig = $this->_config['PDOStatement']->fetch($mode);
        if (! $orig) {
            return false;
        }
        
        // loop through each column of original data and merge into the
        // $row array.
        foreach ($orig as $key => $val) {
            // does the column name have double-underscores in it?
            $pos = strpos($key, '__');
            if ($pos) {
                // assume that the left portion is the table name, and
                // the right portion is the column name.
                $tbl = substr($key, 0, $pos);
                $col = substr($key, $pos+2);
                $row[$tbl][$col] = $val;
            } else {
                // no underscores, it's just a column name.
                $row[$key] = $val;
            }
        }
        return $row;
    }
    
    /**
     * 
     * Fetches all rows from the result source via $this->fetch().
     * 
     * @access public
     * 
     * @param int $mode A PDO::FETCH_* constant to specify how the row
     * should be returned; default is PDO::FETCH_ASSOC.
     * 
     * @return array A sequential array of data from all fetched rows.
     * 
     */
    public function fetchAll($mode = PDO::FETCH_ASSOC)
    {
        $data = array();
        while ($row = $this->fetch($mode)) {
            $data[] = $row;
        }
        return $data;
    }
}
?>