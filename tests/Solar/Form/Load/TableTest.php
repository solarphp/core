<?php

require_once dirname(__FILE__) . '/../../../SolarUnitTest.config.php';

Solar::autoload('Solar_Sql');

Solar::autoload('Solar_Sql_Table');

/**
 * @todo This test is way too dependant on location and uses real classes where
 * mocks should be used.  It needs to be updated, but I'm not sure what exactly
 * is being tested here and what is not.  It looks to me like the "_table" here
 * is a mock, but if that's the case this is really a test of Solar_Form
 */
class Solar_Form_Load_TableTest extends PHPUnit_Framework_TestCase
{
    
    protected $_form;
    
    protected $_table;
    
    protected $_sql;
    public function setup()
    {
        $this->markTestSkipped('way too brittle to test');
        
        $this->_sql = Solar::factory('Solar_Sql');
        
        $this->_table = Solar::factory(
            'Test_Solar_Form_Load_Table_Object',
            array('sql' => $this->_sql)
        );
        
        $this->_form = Solar::factory('Solar_Form');
    }
    
    public function teardown()
    {
        parent::teardown();
        $this->_sql->dropTable('test_form_load');
    }
    
    /*
    public function testFetch()
    {
        $this->todo('incomplete');
    }
    */
    
    public function test__construct()
    {
        $load = Solar::factory('Solar_Form_Load_Table');
        $this->assertInstance($load, 'Solar_Form_Load_Table');
    }
    
    // tests the inList automated option creator
    public function testFetch_inList()
    {
        $this->_form->load('Solar_Form_Load_Table', $this->_table);
        
        $expect = array(
            'Alpha' => 'Alpha',
            'Bravo' => 'Bravo',
            'Charlie' => 'Charlie',
        );
        
        $actual = $this->_form->elements['test_form_load[by_list]']['options'];
        
        $this->assertSame($actual, $expect);
    }
    
    // tests the inList automated option creator
    public function testFetch_inKeys()
    {
        $this->_form->load('Solar_Form_Load_Table', $this->_table);
        
        $expect = array(
            'a' => 'Alpha',
            'b' => 'Bravo',
            'c' => 'Charlie',
        );
        
        $actual = $this->_form->elements['test_form_load[by_keys]']['options'];
        
        $this->assertSame($actual, $expect);
    }
}

class Test_Solar_Form_Load_Table_Object extends Solar_Sql_Table {
    
    protected $_name = 'test_form_load';
    
    protected $_col = array(
        'by_keys' => array(
            'type'   => 'varchar',
            'size'   => '255',
            'valid'  => array(
                'inKeys',
                'Not in the keys.',
                array('a' => 'Alpha', 'b' => 'Bravo', 'c' => 'Charlie'),
            ),
        ),
        'by_list' => array(
            'type'   => 'varchar',
            'size'   => '255',
            'valid'  => array(
                'inList',
                'Not in the vals.',
                array('Alpha', 'Bravo', 'Charlie'),
            ),
        ),
    );
}

