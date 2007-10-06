<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

abstract class Solar_Sql_AdapterTestCase extends PHPUnit_Framework_TestCase
{
    
    protected $_sql;
    
    protected $_config;
    
    protected $_table_def = array(
        'id' => array(
            'type' => 'int',
        ),
        'name' => array(
            'type' => 'varchar',
            'size' => 64
        ),
    );
    
    protected $_table_name = 'test_solar_sql_create';
    
    protected $_seq_name = 'test_solar_sql_create_id';
    
    protected $_quote_expect = null;
    
    protected $_quote_array_expect = null;
    
    protected $_quote_multi_expect = null;
    
    protected $_quote_into_expect = null;
    
    protected $_describe_table_sql = null;
    
    /**
     * @todo refactor - this makes the asumption that Solar_Sql works.  Need to
     * use either PDO and setup the DSN in the individual test cases or the
     * individual test cases need to setup the test tables.
     */
    public function setup()
    {
        Solar::start(false);
        
        $this->_sql = Solar::factory('Solar_Sql', $this->_config);
        
        // drop existing table
        try {
            $this->_sql->dropTable($this->_table_name);
        } catch (Exception $e) {
            // do nothing
        }
        
        // drop existing sequence
        try {
            $this->_sql->dropSequence($this->_seq_name);
        } catch (Exception $e) {
            // do nothing
        }
        
        // recreate table
        $this->_sql->createTable($this->_table_name, $this->_table_def);
    }
    
    public function teardown()
    {
        $this->_sql = null;
        parent::teardown();
    }
    
    protected function _insertData()
    {
        // insert data
        $insert = array(
            array('id' => '1', 'name' => 'Foo'),
            array('id' => '2', 'name' => 'Bar'),
            array('id' => '3', 'name' => 'Baz'),
            array('id' => '4', 'name' => 'Dib'),
            array('id' => '5', 'name' => 'Zim'),
            array('id' => '6', 'name' => 'Gir'),
        );
        
        foreach ($insert as $row) {
            $this->_sql->insert($this->_table_name, $row);
        }
    }
    
    /**
     * @todo is this testing the adapter or Solar_Sql?  This test needs to be
     * refactored.  The adapters need to be tested independent of Solar_Sql and
     * Solar_Sql needs to be tested with a Mock adapter that checks that the
     * expected functions are called.
     */
    public function test__construct()
    {
        $this->assertType($this->_config['adapter'], $this->_sql);
    }
    
    public function testQuery_preparedQueryPlain()
    {
        $this->_insertData();
        $result = $this->_sql->query("SELECT COUNT(*) FROM $this->_table_name");
        $this->assertType('PDOStatement', $result);
    }
    
    public function testQuery_preparedQueryBind()
    {
        $this->_insertData();
        $data = array('id' => 1);
        $stmt = "SELECT * FROM $this->_table_name WHERE id = :id";
        $result = $this->_sql->query($stmt, $data);
        $this->assertType('PDOStatement', $result);
    }
    
    public function testQuery_exec()
    {
        $result = $this->_sql->query("DROP TABLE $this->_table_name");
        $this->assertType('PDOStatement', $result);
    }
    
    public function testBegin()
    {
        $result = $this->_sql->begin();
        $this->assertTrue($result);
        $this->_sql->rollback();
    }
    
    public function testCommit()
    {
        $data = array('id' => 1, 'name' => 'Zim');
        $this->_sql->begin();
        $this->_sql->insert($this->_table_name, $data);
        $this->_sql->commit();
        $result = $this->_sql->fetchValue("SELECT COUNT(*) FROM $this->_table_name");
        $this->assertEquals($result, '1');
    }
    
    public function testRollback()
    {
        $data = array('id' => 1, 'name' => 'Zim');
        $this->_sql->begin();
        $this->_sql->insert($this->_table_name, $data);
        $this->_sql->rollback();
        $result = $this->_sql->fetchValue("SELECT COUNT(*) FROM $this->_table_name");
        $this->assertEquals($result, '0');
    }
    
    public function testInsert()
    {
        $data = array('id' => '1', 'name' => 'Zim');
        
        $result = $this->_sql->insert($this->_table_name, $data);
        $this->assertEquals($result, 1);
        
        $result = $this->_sql->fetchOne("SELECT * FROM $this->_table_name WHERE id = 1");
        $this->assertEquals($result, $data);
    }
    
    public function testUpdate()
    {
        $insert = array('id' => '1', 'name' => 'Foo');
        $result = $this->_sql->insert($this->_table_name, $insert);
        $this->assertEquals($result, 1);
        
        $update = array('name' => 'Bar');
        $where = $this->_sql->quoteInto("id = ?", 1);
        $result = $this->_sql->update($this->_table_name, $update, $where);
        $this->assertEquals($result, 1);
        
        $expect = array('id' => '1', 'name' => 'Bar');
        $actual = $this->_sql->fetchOne("SELECT * FROM $this->_table_name WHERE id = 1");
        $this->assertEquals($actual, $expect);
    }
    
    public function testDelete()
    {
        // add some test data
        $this->_insertData();
        
        // attempt the delete
        $where = $this->_sql->quoteInto('id = ?', 5);
        $result = $this->_sql->delete($this->_table_name, $where);
        $this->assertEquals($result, 1);
        
        $expect = array(
            array('id' => '1', 'name' => 'Foo'),
            array('id' => '2', 'name' => 'Bar'),
            array('id' => '3', 'name' => 'Baz'),
            array('id' => '4', 'name' => 'Dib'),
            array('id' => '6', 'name' => 'Gir'),
        );
        
        // did it work?
        $actual = $this->_sql->fetchAll("SELECT * FROM $this->_table_name ORDER BY id");
        $this->assertEquals($actual, $expect);
    }
    
    public function testFetchAll()
    {
        $this->_insertData();
        $actual = $this->_sql->fetchAll("SELECT * FROM $this->_table_name ORDER BY id");
        $expect = array(
            array('id' => '1', 'name' => 'Foo'),
            array('id' => '2', 'name' => 'Bar'),
            array('id' => '3', 'name' => 'Baz'),
            array('id' => '4', 'name' => 'Dib'),
            array('id' => '5', 'name' => 'Zim'),
            array('id' => '6', 'name' => 'Gir'),
        );
        $this->assertEquals($actual, $expect);
    }
    
    public function testFetchAssoc()
    {
        $this->_insertData();
        $actual = $this->_sql->fetchAssoc("SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = array(
            'Foo' => array('id' => '1', 'name' => 'Foo'),
            'Bar' => array('id' => '2', 'name' => 'Bar'),
            'Baz' => array('id' => '3', 'name' => 'Baz'),
            'Dib' => array('id' => '4', 'name' => 'Dib'),
            'Zim' => array('id' => '5', 'name' => 'Zim'),
            'Gir' => array('id' => '6', 'name' => 'Gir'),
        );
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchCol()
    {
        $this->_insertData();
        $actual = $this->_sql->fetchCol("SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = array(
            'Foo',
            'Bar',
            'Baz',
            'Dib',
            'Zim',
            'Gir',
        );
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchValue()
    {
        $this->_insertData();
        $actual = $this->_sql->fetchValue("SELECT COUNT(*) FROM $this->_table_name");
        $expect = '6';
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchPairs()
    {
        $this->_insertData();
        $actual = $this->_sql->fetchPairs("SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = array(
            'Foo' => '1',
            'Bar' => '2',
            'Baz' => '3',
            'Dib' => '4',
            'Zim' => '5',
            'Gir' => '6',
        );
        $this->assertEquals($expect, $actual);
    }
    
    public function testFetchPdo()
    {
        $this->_insertData();
        $actual = $this->_sql->fetchPdo("SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = 'PDOStatement';
        $this->assertType($expect, $actual);
    }
    
    public function testFetchOne()
    {
        $this->_insertData();
        $cmd = "SELECT id, name FROM $this->_table_name WHERE id = :id";
        $data = array('id' => 5);
        $actual = $this->_sql->fetchOne($cmd, $data);
        $expect = array('id' => '5', 'name' => 'Zim');
        $this->assertEquals($actual, $expect);
    }
    
    public function testFetchSql()
    {
        $expect = "SELECT name, id FROM $this->_table_name WHERE id = :id";
        $actual = $this->_sql->fetchSql($expect);
        $this->assertEquals($expect, $actual);
    }
    
    public function testCreateSequence()
    {
        $this->_sql->createSequence($this->_seq_name);
        $result = $this->_sql->nextSequence($this->_seq_name);
        $this->assertEquals($result, '1');
    }
    
    public function testDropSequence()
    {
        // create the sequence so next value is 9
        $this->_sql->createSequence($this->_seq_name, 9);
        $result = $this->_sql->nextSequence($this->_seq_name);
        $this->assertEquals($result, '9');
        
        // drop and recreate starting at 0, should get 1
        $this->_sql->dropSequence($this->_seq_name);
        $result = $this->_sql->nextSequence($this->_seq_name);
        $this->assertEquals($result, '1');
    }
    
    public function testNextSequence()
    {
        $result = $this->_sql->nextSequence($this->_seq_name);
        $this->assertEquals($result, '1');
    }
    
    /**
     * @todo redo this so it creates a column of every type,
     * also tests failed table creaton attempts.
     */
    /*
    public function testCreateTable()
    {
        $this->skip('table created before every test');
    }
    */
    
    public function testDropTable()
    {
        $this->_sql->dropTable($this->_table_name);
        $list = $this->_sql->fetchTableList();
        $actual = in_array($this->_table_name, $list);
        $this->assertFalse($actual);
    }
    
    public function testFetchTableList()
    {
        $list = $this->_sql->fetchTableList();
        $actual = in_array($this->_table_name, $list);
        $this->assertTrue($actual);
    }
    
    public function testAddColumn()
    {
        $this->_insertData();
        
        $info = array(
            'type' => 'varchar',
            'size' => 255,
        );
        $this->_sql->addColumn($this->_table_name, 'email', $info);
        $this->_sql->update($this->_table_name, array('email' => 'nobody@example.com'), '1=1');
        
        $actual = $this->_sql->fetchOne("SELECT * FROM $this->_table_name WHERE id = 1");
        $expect = array('id' => '1', 'name' => 'Foo', 'email' => 'nobody@example.com');
        $this->assertEquals($actual, $expect);
    }
    
    public function testDropColumn()
    {
        $this->_insertData();
        $this->_sql->dropColumn($this->_table_name, 'name');
        $actual = $this->_sql->fetchOne("SELECT * FROM $this->_table_name WHERE id = 1");
        $expect = array('id' => '1');
        $this->assertEquals($actual, $expect);
    }
    
    public function testCreateIndex_singleNormal()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'id');
        $this->assertNotNull($result);
    }
    
    public function testCreateIndex_singleUnique()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'id', true);
        $this->assertNotNull($result);
    }
    
    public function testCreateIndex_multiNormal()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'multi', false, array('id', 'name'));
        $this->assertNotNull($result);
    }
    
    public function testCreateIndex_multiUnique()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'multi', false, array('id', 'name'));
        $this->assertNotNull($result);
    }
    
    public function testCreateIndex_altnameNormal()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'alt_name', true, 'id');
        $this->assertNotNull($result);
    }
    
    public function testCreateIndex_altnameUnique()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'alt_name', true, 'id');
        $this->assertNotNull($result);
    }
    
    public function testDropIndex()
    {
        $result = $this->_sql->createIndex($this->_table_name, 'id');
        $this->assertTrue($result instanceof PDOStatement);
        
        $result = $this->_sql->dropIndex($this->_table_name, 'id');
        $this->assertTrue($result instanceof PDOStatement);
    }
    
    public function testQuote()
    {
        $actual = $this->_sql->quote('"foo" bar \'baz\'');
        //var_dump($actual);
        $this->assertEquals($actual, $this->_quote_expect);
    }
    
    public function testQuote_array()
    {
        $actual = $this->_sql->quote(array('"foo"', 'bar', "'baz'"));
        //var_dump($actual);
        $this->assertEquals($actual, $this->_quote_array_expect);
    }
    
    public function testQuoteInto()
    {
        $actual = $this->_sql->quoteInto("foo = ?", "'bar'");
        //var_dump($actual);
        $this->assertEquals($actual, $this->_quote_into_expect);
    }
    
    public function testQuoteMulti()
    {
        $where = array(
            'id = 1',
            'foo = ?' => 'bar',
            'zim IN(?)' => array('dib', 'gir', 'baz'),
        );
        $actual = $this->_sql->quoteMulti($where, ' AND ');
        //var_dump($actual);
        $this->assertEquals($actual, $this->_quote_multi_expect);
    }
    
    public function testFetchTableCols()
    {
        $this->_fetchTableCols();
    }
    
    protected function _fetchTableCols($colname = null)
    {
        $table = 'test_solar_sql_describe';
        
        // clear out previous tables
        try {
            $this->_sql->dropTable($table);
        } catch (Exception $e) {
            // assume the table didn't exist
        }
        
        // create the "describe table" table and make sure it's there
        $this->_sql->query($this->_describe_table_sql);
        $this->assertTrue(in_array(
            $this->_table_name,
            $this->_sql->fetchTableList()
        ));
        
        // get the table column descriptions
        $cols = $this->_sql->fetchTableCols($table);
        
        // return one, or all?
        if ($colname) {
            return $cols[$colname];
        } else {
            return $cols;
        }
    }
    
    public function testFetchTableCols_autoinc_primary()
    {
        $actual = $this->_fetchTableCols('test_autoinc_primary');
        $this->assertEquals($actual['name'], 'test_autoinc_primary');
        $this->assertTrue($actual['autoinc']);
        $this->assertTrue($actual['primary']);
    }
    
    public function testFetchTableCols_require()
    {
        // require, not primary or autoinc
        $actual = $this->_fetchTableCols('test_require');
        $this->assertEquals($actual['name'], 'test_require');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertTrue($actual['require']);
    }
    
    public function testFetchTableCols_bool()
    {
        // bool
        $actual = $this->_fetchTableCols('test_bool');
        $this->assertEquals($actual['name'], 'test_bool');
        $this->assertEquals($actual['type'], 'bool');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_char()
    {
        // char (also, not require)
        $actual = $this->_fetchTableCols('test_char');
        $this->assertEquals($actual['name'], 'test_char');
        $this->assertEquals($actual['type'], 'char');
        $this->assertEquals($actual['size'], 3);
        $this->assertFalse($actual['require']);
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_varchar()
    {
        // varchar
        $actual = $this->_fetchTableCols('test_varchar');
        $this->assertSame($actual['name'], 'test_varchar');
        $this->assertEquals($actual['type'], 'varchar');
        $this->assertEquals($actual['size'], 7);
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_smallint()
    {
        // smallint
        $actual = $this->_fetchTableCols('test_smallint');
        $this->assertEquals($actual['name'], 'test_smallint');
        $this->assertEquals($actual['type'], 'smallint');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_int()
    {
        // int
        $actual = $this->_fetchTableCols('test_int');
        $this->assertEquals($actual['name'], 'test_int');
        $this->assertEquals($actual['type'], 'int');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_bigint()
    {
        // bigint
        $actual = $this->_fetchTableCols('test_bigint');
        $this->assertEquals($actual['name'], 'test_bigint');
        $this->assertEquals($actual['type'], 'bigint');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_numeric_size()
    {
        // numeric, size only
        $actual = $this->_fetchTableCols('test_numeric_size');
        $this->assertEquals($actual['name'], 'test_numeric_size');
        $this->assertEquals($actual['type'], 'numeric');
        $this->assertEquals($actual['size'], 7);
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_numeric_scope()
    {
        // numeric, size and scope
        $actual = $this->_fetchTableCols('test_numeric_scope');
        $this->assertEquals($actual['name'], 'test_numeric_scope');
        $this->assertEquals($actual['type'], 'numeric');
        $this->assertEquals($actual['size'], 7);
        $this->assertEquals($actual['scope'], 3);
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_float()
    {
        // float
        $actual = $this->_fetchTableCols('test_float');
        $this->assertEquals($actual['name'], 'test_float');
        $this->assertEquals($actual['type'], 'float');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_clob()
    {
        // clob
        $actual = $this->_fetchTableCols('test_clob');
        $this->assertEquals($actual['name'], 'test_clob');
        $this->assertEquals($actual['type'], 'clob');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_date()
    {
        // date
        $actual = $this->_fetchTableCols('test_date');
        $this->assertEquals($actual['name'], 'test_date');
        $this->assertEquals($actual['type'], 'date');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_time()
    {
        // time
        $actual = $this->_fetchTableCols('test_time');
        $this->assertEquals($actual['name'], 'test_time');
        $this->assertEquals($actual['type'], 'time');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_timestamp()
    {
        // timestamp
        $actual = $this->_fetchTableCols('test_timestamp');
        $this->assertEquals($actual['name'], 'test_timestamp');
        $this->assertEquals($actual['type'], 'timestamp');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_default_null()
    {
        // default, sql null
        $actual = $this->_fetchTableCols('test_default_null');
        $this->assertEquals($actual['name'], 'test_default_null');
        $this->assertNull($actual['default']);
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_default_string()
    {
        // default, sql literal string
        $actual = $this->_fetchTableCols('test_default_string');
        $this->assertEquals($actual['name'], 'test_default_string');
        $this->assertEquals($actual['default'], 'literal');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_default_integer()
    {
        // default, sql literal integer
        $actual = $this->_fetchTableCols('test_default_integer');
        $this->assertEquals($actual['name'], 'test_default_integer');
        $this->assertEquals($actual['default'], '7');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_default_numeric()
    {
        // default, sql literal numeric
        $actual = $this->_fetchTableCols('test_default_numeric');
        $this->assertEquals($actual['name'], 'test_default_numeric');
        $this->assertEquals($actual['default'], '1234.567');
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
    
    public function testFetchTableCols_default_ignore()
    {
        // default, sql keyword or function (and thus ignored)
        $actual = $this->_fetchTableCols('test_default_ignore');
        $this->assertEquals($actual['name'], 'test_default_ignore');
        $this->assertNull($actual['default']);
        $this->assertFalse($actual['autoinc']);
        $this->assertFalse($actual['primary']);
        $this->assertFalse($actual['require']);
    }
}
