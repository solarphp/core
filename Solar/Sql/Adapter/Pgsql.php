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
     * Map of Solar generic types to RDBMS native types used when creating
     * portable tables.
     * 
     * @var array
     * 
     */
    protected $_solar_native = array(
        'bool'      => 'BOOLEAN',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'NUMERIC',
        'float'     => 'DOUBLE PRECISION',
        'clob'      => 'TEXT',
        'date'      => 'DATE',
        'time'      => 'TIME',
        'timestamp' => 'TIMESTAMP'
    );
    
    /**
     * 
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     */
    protected $_native_solar = array(
        
        // numeric
        'boolean'                       => 'bool',
        'smallint'                      => 'smallint',
        'integer'                       => 'int',
        'bigint'                        => 'bigint',
        'numeric'                       => 'numeric',
        'double precision'              => 'float',
        
        // date & time                  
        'date'                          => 'date',
        'time without time zone'        => 'time',
        'timestamp without time zone'   => 'timestamp',
        
        // string types
        'character'                     => 'char',
        'character varying'             => 'varchar',
        
        // clob
        'text'                          => 'clob',
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
     * Returns a list of all tables in the database.
     * 
     * @return array All table names in the database.
     * 
     */
    protected function _fetchTableList()
    {
        $cmd = "
            SELECT DISTINCT table_name
            FROM information_schema.tables
            WHERE table_schema != 'pg_catalog'
            AND table_schema != 'information_schema'";
             
        return $this->fetchCol($cmd);
    }
    
    /**
     * 
     * Describes the columns in a table.
     * 
     * @param string $table The table to describe.
     * 
     * @return array
     * 
     */
    protected function _fetchTableCols($table)
    {
        //          name         |            type             | require | primary |                           default                           
        // ----------------------+-----------------------------+---------+---------+-------------------------------------------------------------
        //  test_autoinc_primary | integer                     | t       | t       | nextval('test_describe_test_autoinc_primary_seq'::regclass)
        //  test_require         | integer                     | t       |         | 
        //  test_bool            | boolean                     | f       |         | 
        //  test_char            | character(7)                | f       |         | 
        //  test_varchar         | character varying(7)        | f       |         | 
        //  test_smallint        | smallint                    | f       |         | 
        //  test_int             | integer                     | f       |         | 
        //  test_bigint          | bigint                      | f       |         | 
        //  test_numeric_size    | numeric(5,0)                | f       |         | 
        //  test_numeric_scope   | numeric(5,3)                | f       |         | 
        //  test_float           | double precision            | f       |         | 
        //  test_clob            | text                        | f       |         | 
        //  test_date            | date                        | f       |         | 
        //  test_time            | time without time zone      | f       |         | 
        //  test_timestamp       | timestamp without time zone | f       |         | 
        //  test_default_null    | character(7)                | f       |         | 
        //  test_default_string  | character(7)                | f       |         | 'literal'::bpchar
        //  test_default_integer | integer                     | f       |         | 7
        //  test_default_numeric | numeric(5,3)                | f       |         | 12.345
        //  test_default_ignore  | timestamp without time zone | f       |         | now()
        //  test_default_varchar | character varying(17)       | f       |         | 'literal'::character varying
        //  test_default_date    | date                        | f       |         | '1979-11-07'::date
        
        // modified from Zend_Db_Adapter_Pdo_Pgsql
        $cmd = "
            SELECT
                a.attname AS name,
                format_type(a.atttypid, a.atttypmod) AS type,
                a.attnotnull AS require,
                (SELECT 't'
                    FROM pg_index
                    WHERE c.oid = pg_index.indrelid
                    AND pg_index.indkey[0] = a.attnum
                    AND pg_index.indisprimary = 't'
                ) AS primary,
                (SELECT pg_attrdef.adsrc
                    FROM pg_attrdef
                    WHERE c.oid = pg_attrdef.adrelid
                    AND pg_attrdef.adnum=a.attnum
                ) AS default
            FROM
                pg_attribute a,
                pg_class c,
                pg_type t
            WHERE
                c.relname = :table
                AND a.attnum > 0
                AND a.attrelid = c.oid
                AND a.atttypid = t.oid
            ORDER BY
                a.attnum";
        
        // where the description will be stored
        $descr = array();
        
        // loop through the result rows; each describes a column.
        $cols = $this->fetchAll($cmd, array('table' => $table));
        foreach ($cols as $val) {
            $name = $val['name'];
            list($type, $size, $scope) = $this->_getTypeSizeScope($val['type']);
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => ($size  ? (int) $size  : null),
                'scope'   => ($scope ? (int) $scope : null),
                'default' => $this->_getDefault($val['default']),
                'require' => (bool) ($val['require'] == 't'),
                'primary' => (bool) ($val['primary'] == 't'),
                'autoinc' => (bool) (substr($val['default'], 0, 7) == 'nextval'),
            );
        }
        
        // done
        return $descr;
    }
    
    /**
     * 
     * Given a native column SQL default value, finds a PHP literal value.
     * 
     * SQL NULLs are converted to PHP nulls.  Non-literal values (such as
     * keywords and functions) are also returned as null.
     * 
     * @param string $default The column default SQL value.
     * 
     * @return scalar A literal PHP value.
     * 
     */
    protected function _getDefault($default)
    {
        // numeric literal?
        if (is_numeric($default)) {
            return $default;
        }
        
        // string literal?
        $k = substr($default, 0, 1);
        if ($k == '"' || $k == "'") {
            // find the trailing :: typedef
            $pos = strrpos($default, '::');
            // also remove the leading and trailing quotes
            return substr($default, 1, $pos-2);
        }
        
        // null or non-literal
        return null;
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
        return $this->query("DROP INDEX $name");
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
        return $this->query("CREATE SEQUENCE $name START $start");
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
        return $this->query("DROP SEQUENCE IF EXISTS $name");
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
    
    /**
     * 
     * Get the last auto-incremented insert ID from the database.
     * 
     * Postgres SERIAL and BIGSERIAL types create sequences named in this
     * fashion:  `{$table}_{$col}_seq`.
     * 
     * <http://www.postgresql.org/docs/7.4/interactive/datatype.html#DATATYPE-SERIAL>
     * 
     * @param string $table The table name on which the auto-increment occurred.
     * 
     * @param string $col The name of the auto-increment column.
     * 
     * @return int The last auto-increment ID value inserted to the database.
     * 
     */
    public function lastInsertId($table = null, $col = null)
    {
        $this->_connect();
        $name = "{$table}_{$col}_seq";
        return $this->_pdo->lastInsertId($name);
    }
    
    /**
     * 
     * Given a column definition, modifies the auto-increment and primary-key
     * clauses in place.
     * 
     * @param string &$coldef The column definition as it is now.
     * 
     * @param bool $autoinc Whether or not this is an auto-increment column.
     * 
     * @param bool $primary Whether or not this is a primary-key column.
     * 
     * @return void
     * 
     */
    protected function _modAutoincPrimary(&$coldef, $autoinc, $primary)
    {
        if ($autoinc) {
            // replace datatype with SERIAL or BIGSERIAL
            $parts = explode(' ', $coldef);
            if (strtoupper($parts[0]) == 'BIGINT') {
                $parts[0] = 'BIGSERIAL';
            } else {
                $parts[0] = 'SERIAL';
            }
            $coldef = implode(' ', $parts);
        }
        
        if ($primary) {
            $coldef .= ' PRIMARY KEY';
        }
    }
    
    /**
     * 
     * Modifies the sequence name.
     * 
     * PostgreSQL won't allow a sequence with the same name as a table or
     * index. This method modifies the name by appending '__s'.
     * 
     * @param string $name The requested sequence name.
     * 
     * @return string The modified sequence name.
     * 
     */
    protected function _modSequenceName($name)
    {
        return $name . '__s';
    }
    
    /**
     * 
     * Modifies the index name.
     * 
     * PostgreSQL won't allow two indexes of the same name, even if they are
     * on different tables.  This method modifies the name by prefixing with
     * the table name and two underscores.  Thus, for a index named 'foo' on 
     * a table named 'bar', the modified name will be 'foo__bar'.
     * 
     * @param string $table The table on which the index occurs.
     * 
     * @param string $name The requested index name.
     * 
     * @return string The modified index name.
     * 
     */
    protected function _modIndexName($table, $name)
    {
        return $table . '__' . $name;
    }
}
