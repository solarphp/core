<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

class Solar_Role_Adapter_NoneTest extends Solar_Role_AdapterTestCase {
    
    // in this adapter, we expect to get NO ROLES AT ALL
    
    public function testFetch()
    {
        $expect = array();
        $actual = $this->_role->fetch('pmjones');
        $this->assertEquals($actual, $expect);
    }
    
    public function testLoad()
    {
        $this->_role->load('pmjones');
        $expect = array();
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
    }
    
    public function testLoad_refresh()
    {
        // load the first time
        $this->_role->load('pmjones');
        $expect = array();
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
        
        // foribly refresh
        $this->_role->load('boshag', true);
        $expect = array();
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
    }
    
    public function testReset()
    {
        // load the first time
        $this->_role->load('pmjones');
        $expect = array();
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
        
        // reset to empty
        $this->_role->reset();
        $expect = array();
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
    }
    
    public function testIs()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->is('admin');
        $this->assertFalse($actual);
    }
    
    public function testIsNot()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->is('no-such-role');
        $this->assertFalse($actual);
    }
    
    public function testIsAny()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAny(array('no-such-role', 'root'));
        $this->assertFalse($actual);
    }
    
    public function testIsNotAny()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAny(array('no-such-role', 'no-other-role'));
        $this->assertFalse($actual);
    }
    
    public function testIsAll()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAll(array('admin', 'root'));
        $this->assertFalse($actual);
    }
    
    public function testIsNotAll()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAll(array('admin', 'root', 'no-such-role'));
        $this->assertFalse($actual);
    }
    
}
