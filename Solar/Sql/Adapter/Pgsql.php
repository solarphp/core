<?php
/**
 * 
 * Class for connecting to PostgreSQL databases.
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
 * Abstract SQL adapter.
 */
Solar::loadClass('Solar_Sql_Adapter');

/**
 * 
 * Class for connecting to PostgreSQL databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Pgsql extends Solar_Sql_Adapter {
    
    /**
     * 
     * Map of Solar generic column types to RDBMS native declarations.
     * 
     * @var array
     * 
     */
    protected $_native = array(
        'bool'      => 'BOOLEAN',
        'char'      => 'CHAR(:size)',
        'varchar'   => 'VARCHAR(:size)',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'NUMERIC(:size,:scope)',
        'float'     => 'DOUBLE PRECISION',
        'clob'      => 'TEXT',
        'date'      => 'CHAR(10)',
        'time'      => 'CHAR(8)',
        'timestamp' => 'CHAR(19)'
    );
    
    protected $_describe = array(
        
        // numeric
        'int2'      => 'smallint',
        'int4'      => 'integer',
        'int8'      => 'bigint',
        'numeric'   => 'numeric',
        'float8'    => 'float',
        
        // date & time
        'date'      => 'date',
        'time'      => 'time',
        'timestamp' => 'timestamp',
        
        // string types
        'bpchar'    => 'char',
        'varchar'   => 'varchar',
    );
    
    
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'pgsql';
    
    /**
     * 
     * Builds a SELECT statement from its component parts.
     * 
     * Adds LIMIT clause.
     * 
     * @param array $parts The component parts of the statement.
     * 
     * @return void
     * 
     */
    protected function _buildSelect($parts)
    {
        // build the baseline statement
        $stmt = parent::_buildSelect($parts);
        
        // determine count
        $count = ! empty($parts['limit']['count'])
            ? (int) $parts['limit']['count']
            : 0;
        
        // determine offset
        $offset = ! empty($parts['limit']['offset'])
            ? (int) $parts['limit']['offset']
            : 0;
            
        // add the count and offset
        if ($count > 0) {
            $stmt .= " LIMIT $count";
            if ($offset > 0) {
                $stmt .= " OFFSET $offset";
            }
        }
        
        // done!
        return $stmt;
    }
    
    /**
     * 
     * Returns the SQL statement to get a list of database tables.
     * 
     * @return string The SQL statement.
     * 
     */
    public function listTables()
    {
        // copied from PEAR DB
        // $cmd = "SELECT c.relname AS table_name " 
        //      . "FROM pg_class c, pg_user u " 
        //      . "WHERE c.relowner = u.usesysid AND c.relkind = 'r' " 
        //      . "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) " 
        //      . "AND c.relname !~ '^(pg_|sql_)' " 
        //      . "UNION " 
        //      . "SELECT c.relname AS table_name " 
        //      . "FROM pg_class c " 
        //      . "WHERE c.relkind = 'r' " 
        //      . "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) " 
        //      . "AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) " 
        //      . "AND c.relname !~ '^pg_'";
        
        // replace with:
        $cmd = "SELECT DISTINCT table_name "
             . "FROM information_schema.tables "
             . "WHERE table_schema != 'pg_catalog' "
             . "AND table_schema != 'information_schema'";
             
        $result = $this->query($cmd);
        $list = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        return $list;
    }
    
    public function describeTable($table)
    {
        // strip non-word characters to try and prevent SQL injections
        $table = preg_replace('/[^\w]/', '', $table);
        
        // http://www.postgresql.org/docs/current/static/infoschema-columns.html
        $cmd = <<<SQL
            SELECT 
                cols.column_name AS name, 
                cols.udt_name AS type, 
                CASE
                    WHEN cols.character_maximum_length > 0 THEN cols.character_maximum_length
                    ELSE CASE
                        WHEN cols.numeric_precision_radix = 10 THEN cols.numeric_precision
                        ELSE NULL
                    END
                END AS size,
                CASE
                    WHEN cols.numeric_precision_radix = 10 THEN cols.numeric_scale
                    ELSE NULL
                END AS scope,
                CASE
                    WHEN is_nullable = 'YES' THEN FALSE
                    ELSE TRUE
                END AS require,
                CASE
                    WHEN cons.constraint_type = 'PRIMARY KEY' THEN TRUE
                    ELSE FALSE
                END AS primary,
                CASE
                    WHEN cols.column_default ~ '^nextval' THEN TRUE
                    ELSE FALSE
                END AS autoinc
            FROM information_schema.columns AS cols
            LEFT JOIN information_schema.key_column_usage AS keys
                ON keys.table_name = cols.table_name
                AND keys.column_name = cols.column_name
            LEFT JOIN information_schema.table_constraints AS cons
                ON keys.constraint_name = cons.constraint_name
            WHERE cols.table_name = :table
            ORDER BY cols.ordinal_position;
SQL;
        
        // get the native PDOStatement result
        $result = $this->query($cmd, array('table' => $table));
        
        // where the description will be stored
        $descr = array();
        
        // loop through the result rows; each describes a column.
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $val) {
            $val['type'] = $this->_getSolarType($val['type']);
            $descr[$val['name']] = $val;
        }
        
        // done
        return $descr;
    }
    
    /**
     * 
     * Drops an index.
     * 
     * @param string $table The table of the index.
     * 
     * @param string $name The full index name.
     * 
     * @return void
     * 
     */
    protected function _dropIndex($table, $name)
    {
        // postgres index names are for the entire database,
        // not for a single table.
        // http://www.postgresql.org/docs/7.4/interactive/sql-dropindex.html
        $this->query("DROP INDEX $name");
    }
    
    /**
     * 
     * Creates a sequence, optionally starting at a certain number.
     * 
     * @param string $name The sequence name to create.
     * 
     * @param int $start The first sequence number to return.
     * 
     * @return void
     * 
     */
    protected function _createSequence($name, $start = 1)
    {
        $this->query("CREATE SEQUENCE $name START $start");
    }
    
    /**
     * 
     * Drops a sequence.
     * 
     * @param string $name The sequence name to drop.
     * 
     * @return void
     * 
     */
    protected function _dropSequence($name)
    {
        $this->query("DROP SEQUENCE $name");
    }
    
    /**
     * 
     * Gets a sequence number; creates the sequence if it does not exist.
     * 
     * @param string $name The sequence name.
     * 
     * @return int The next sequence number.
     * 
     */
    protected function _nextSequence($name)
    {
        $cmd = 'SELECT NEXTVAL(' . $this->quote($name) . ')';
        
        // first, try to increment the sequence number, assuming
        // the table exists.
        try {
            $this->query($cmd);
        } catch (Exception $e) {
            // error when updating the sequence.
            // assume we need to create it.
            $this->_createSequence($name);
            
            // now try to increment again.
            $this->query($cmd);
        }
        
        // get the sequence number
        return $this->_pdo->lastInsertID($name);
    }
}
?>