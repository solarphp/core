<?php
/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Cache_Adapter_File extends Test_Solar_Cache_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Cache_Adapter_File = array(
        'path'   => null, // set in constructor
        'life'   => 7, // 7 seconds
    );
    
    /**
     * 
     * Setup; runs before each test method.
     * 
     */
    public function preTest()
    {
        if (is_null($this->_config['path'])) {
            $this->_config['path'] = Solar_Dir::tmp('/Solar_Cache_Testing/');
        }
        
        parent::preTest();
        
        /**
         * @todo remove requirement that deleteAll() actually work here
         */
        // remove all previous entries
        $this->_adapter->deleteAll();
    }
    
    public function testEntry()
    {
        $id = 'wile-e-coyote';
        $actual = $this->_adapter->entry($id);
        $expect = $this->_config['path'] . md5($id);
        $this->assertSame($actual, $expect);
    }
}
