<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Storage_Adapter_Var extends Test_Solar_Auth_Storage_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Storage_Adapter_Var = array(
        'data' => array(
            'pmjones' => 'jones'
        )
    );
    
    protected $_expect = array(
        'handle' => 'pmjones',
    );
}
