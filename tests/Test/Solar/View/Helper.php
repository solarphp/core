<?php

abstract class Test_Solar_View_Helper extends Solar_Test {
    
    protected $_view;
    
    protected $_name;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
        $this->_view = Solar::factory('Solar_View');
        $this->_name = substr(get_class($this), 23);
        $this->_name[0] = strtolower($this->_name[0]);
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $actual = $this->_view->getHelper($this->_name);
        $expect = 'Solar_View_Helper_' . ucfirst($this->_name);
        $this->assertInstance($actual, $expect);
    }
}
?>