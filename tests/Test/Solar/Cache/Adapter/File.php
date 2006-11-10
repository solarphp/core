<?php
require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Cache_Adapter_File extends Test_Solar_Cache_Adapter {
    
    protected $_Test_Solar_Cache_Adapter_File = array(
        'adapter' => 'Solar_Cache_Adapter_File',
        'config'  => array(
            'path'   => null, // set in constructor
            'life'   => 7, // 7 seconds
        ),
    );
    
    public function __construct($config = null)
    {
        $this->_Test_Solar_Cache_Adapter_File['config']['path'] = Solar::temp('/Solar_Cache_Testing/');
        parent::__construct($config);
    }
}
?>