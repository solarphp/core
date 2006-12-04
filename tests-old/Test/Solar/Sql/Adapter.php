<?php

abstract class Test_Solar_Sql_Adapter extends Solar_Test {
    
    protected $_sql;
    
    protected $_table_def = array(
        'id' => array(
            'type' => 'int',
        ),
        'name' => array(
            'type' => 'varchar',
            'size' => 64
        ),
    );
    
    protected $_table_name = 'test_solar_sql';
    
    protected $_seq_name = 'test_solar_sql_id';
    
    protected $_quote_expect = null;
    
    protected $_quote_array_expect = null;
    
    protected $_quote_multi_expect = null;
    
    protected $_quote_into_expect = null;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_sql = Solar::factory('Solar_Sql', $this->_config);
        
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
        
        // drop existing table
        try {
            $this->_sql->dropTable($this->_table_name);
        } catch (Exception $e) {
        }
        
        // drop existing sequence
        try {
            $this->_sql->dropSequence($this->_seq_name);
        } catch (Exception $e) {
        }
        
        // recreate table
        $this->_sql->createTable($this->_table_name, $this->_table_def);
    }
    
    public function teardown()
    {
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
    
    public function test__construct()
    {
        $this->assertInstance($this->_sql, $this->_config['adapter']);
    }
    
    public function testQuery_preparedQueryPlain()
    {
        $this->_insertData();
        $result = $this->_sql->query("SELECT COUNT(*) FROM $this->_table_name");
        $this->assertInstance($result, 'PDOStatement');
    }
    
    public function testQuery_preparedQueryBind()
    {
        $this->_insertData();
        $data = array('id' => 1);
        $stmt = "SELECT * FROM $this->_table_name WHERE id = :id";
        $result = $this->_sql->query($stmt, $data);
        $this->assertInstance($result, 'PDOStatement');
    }
    
    public function testQuery_exec()
    {
        $result = $this->_sql->query("DROP TABLE $this->_table_name");
        $this->assertInstance($result, 'PDOStatement');
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
        $result = $this->_sql->select('one', "SELECT COUNT(*) FROM $this->_table_name");
        $this->assertEquals($result, '1');
    }
    
    public function testRollback()
    {
        $data = array('id' => 1, 'name' => 'Zim');
        $this->_sql->begin();
        $this->_sql->insert($this->_table_name, $data);
        $this->_sql->rollback();
        $result = $this->_sql->select('one', "SELECT COUNT(*) FROM $this->_table_name");
        $this->assertEquals($result, '0');
    }
    
    public function testInsert()
    {
        $data = array('id' => '1', 'name' => 'Zim');
        
        $result = $this->_sql->insert($this->_table_name, $data);
        $this->assertEquals($result, 1);
        
        $result = $this->_sql->select('row', "SELECT * FROM $this->_table_name WHERE id = 1");
        $this->assertEquals($result->toArray(), $data);
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
        $actual = $this->_sql->select('row', "SELECT * FROM $this->_table_name WHERE id = 1");
        $this->assertEquals($actual->toArray(), $expect);
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
        $actual = $this->_sql->select('all', "SELECT * FROM $this->_table_name ORDER BY id");
        $this->assertEquals($actual->toArray(), $expect);
    }
    
    public function testSelect_all()
    {
        $this->_insertData();
        $actual = $this->_sql->select('all', "SELECT * FROM $this->_table_name ORDER BY id");
        $expect = array(
            array('id' => '1', 'name' => 'Foo'),
            array('id' => '2', 'name' => 'Bar'),
            array('id' => '3', 'name' => 'Baz'),
            array('id' => '4', 'name' => 'Dib'),
            array('id' => '5', 'name' => 'Zim'),
            array('id' => '6', 'name' => 'Gir'),
        );
        $this->assertEquals($actual->toArray(), $expect);
    }
    
    public function testSelect_assoc()
    {
        $this->_insertData();
        $actual = $this->_sql->select('assoc', "SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = array(
            'Foo' => array('id' => '1', 'name' => 'Foo'),
            'Bar' => array('id' => '2', 'name' => 'Bar'),
            'Baz' => array('id' => '3', 'name' => 'Baz'),
            'Dib' => array('id' => '4', 'name' => 'Dib'),
            'Zim' => array('id' => '5', 'name' => 'Zim'),
            'Gir' => array('id' => '6', 'name' => 'Gir'),
        );
        $this->assertEquals($actual, $expect);
    }
    
    public function testSelect_col()
    {
        $this->_insertData();
        $actual = $this->_sql->select('col', "SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = array(
            'Foo',
            'Bar',
            'Baz',
            'Dib',
            'Zim',
            'Gir',
        );
        $this->assertEquals($actual, $expect);
    }
    
    public function testSelect_one()
    {
        $this->_insertData();
        $actual = $this->_sql->select('one', "SELECT COUNT(*) FROM $this->_table_name");
        $expect = '6';
        $this->assertEquals($actual, $expect);
    }
    
    public function testSelect_pairs()
    {
        $this->_insertData();
        $actual = $this->_sql->select('pairs', "SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = array(
            'Foo' => '1',
            'Bar' => '2',
            'Baz' => '3',
            'Dib' => '4',
            'Zim' => '5',
            'Gir' => '6',
        );
        $this->assertEquals($actual, $expect);
    }
    
    public function testSelect_pdo()
    {
        $this->_insertData();
        $actual = $this->_sql->select('pdo', "SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = 'PDOStatement';
        $this->assertInstance($actual, $expect);
    }
    
    public function testSelect_result()
    {
        $this->_insertData();
        $actual = $this->_sql->select('result', "SELECT name, id FROM $this->_table_name ORDER BY id");
        $expect = 'Solar_Sql_Result';
        $this->assertInstance($actual, $expect);
    }
    
    public function testSelect_row()
    {
        $this->_insertData();
        $cmd = "SELECT id, name FROM $this->_table_name WHERE id = :id";
        $data = array('id' => 5);
        $actual = $this->_sql->select('row', $cmd, $data);
        $expect = array('id' => '5', 'name' => 'Zim');
        $this->assertEquals($actual->toArray(), $expect);
    }
    
    public function testSelect_string()
    {
        $expect = "SELECT name, id FROM $this->_table_name WHERE id = :id";
        $actual = $this->_sql->select('string', $expect);
        $this->assertEquals($actual, $expect);
    }
    
    public function testSelect_unknown()
    {
        try {
            $actual = $this->_sql->select('NO_SUCH_TYPE', "SELECT * FROM $this->_table_name");
            $this->fail('should not have selected NO_SUCH_TYPE');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Sql_Adapter_Exception');
        }
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
        $list = $this->_sql->listTables();
        $actual = in_array($this->_table_name, $list);
        $this->assertFalse($actual);
    }
    
    public function testListTables()
    {
        $list = $this->_sql->listTables();
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
        
        $actual = $this->_sql->select('row', "SELECT * FROM $this->_table_name WHERE id = 1");
        $expect = array('id' => '1', 'name' => 'Foo', 'email' => 'nobody@example.com');
        $this->assertEquals($actual->toArray(), $expect);
    }
    
    public function testDropColumn()
    {
        $this->_insertData();
        $this->_sql->dropColumn($this->_table_name, 'name');
        $actual = $this->_sql->select('row', "SELECT * FROM $this->_table_name WHERE id = 1");
        $expect = array('id' => '1');
        $this->assertEquals($actual->toArray(), $expect);
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
        $this->assertNotNull($result);
        
        $result = $this->_sql->dropIndex($this->_table_name, 'id');
        $this->assertNull($result);
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
}
?>