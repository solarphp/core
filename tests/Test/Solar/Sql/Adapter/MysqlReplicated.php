<?php
/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Sql_Adapter_MysqlReplicated extends Test_Solar_Sql_Adapter_Mysql {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Adapter_MysqlReplicated = array(
        'host'    => 'localhost',
        'user'    => '',
        'pass'    => '',
        'name'    => 'test',
        'slaves'  => array(
            'zero' => array(
                'host' => '127.0.0.1',
                'user' => 'test_readonly',
            ),
        ),
    );
    
    public function testQuery_replicatedInsert()
    {
        $data = array('id' => '1', 'name' => 'Zim');
        $stmt = "INSERT INTO {$this->_table_name} (id, name) VALUES (:id, :name)";
        
        $result = $this->_adapter->query($stmt, $data);
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'master';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
        
        $result = $this->_adapter->fetchPdo("SELECT * FROM $this->_table_name WHERE id = 1");
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'slave zero';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
    }
    
    public function testQuery_replicatedInsertSlave()
    {
        $slave = $this->_adapter->getPdo();
        $stmt = "INSERT INTO {$this->_table_name} (id, name) VALUES (1, 'Zim')";
        try {
            $result = $slave->query($stmt);
            $this->fail('slave should deny insert commands');
        } catch (PDOException $e) {
            $expect = 'SQLSTATE[42000]: Syntax error or access violation';
            $actual = substr($e->getMessage(), 0, strlen($expect));
            $this->assertSame($actual, $expect);
        }
    }
    
    public function testQuery_replicatedUpdate()
    {
        $data = array('id' => '1', 'name' => 'Zim');
        $stmt = "INSERT INTO {$this->_table_name} (id, name) VALUES (:id, :name)";
        $result = $this->_adapter->query($stmt, $data);
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'master';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
        
        $data = array('name' => 'Bar');
        $stmt = "UPDATE {$this->_table_name} SET name = :name WHERE id = 1";
        $result = $this->_adapter->query($stmt, $data);
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'master';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
        
        $result = $this->_adapter->fetchPdo("SELECT * FROM $this->_table_name WHERE id = 1");
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'slave zero';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
    }
    
    public function testQuery_replicatedDelete()
    {
        // add some test data
        $this->_insertData();
        
        // attempt the delete
        $stmt = "DELETE FROM {$this->_table_name} WHERE id = 5";
        $result = $this->_adapter->query($stmt);
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'master';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
        
        // did it work?
        $stmt = "SELECT * FROM $this->_table_name ORDER BY id";
        $result = $this->_adapter->query($stmt);
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'slave zero';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
    }
    
    public function testQuery_replicatedSelect()
    {
        $this->_insertData();
        $result = $this->_adapter->query("SELECT COUNT(*) FROM $this->_table_name");
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'slave zero';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
    }
    
    public function testQuery_replicatedSelectTransaction()
    {
        $this->_insertData();
        $this->_adapter->begin();
        $result = $this->_adapter->query("SELECT COUNT(*) FROM $this->_table_name");
        $this->assertInstance($result, 'PDOStatement');
        $actual = $result->solar_conn['server'];
        $expect = 'master';
        $this->assertSame($actual, $expect);
        $this->diag($result->solar_conn);
    }
    
    public function testQuery_replicatedSelectGap()
    {
        // should hit the master
        $this->todo('need to inject request and session');
    }
}
