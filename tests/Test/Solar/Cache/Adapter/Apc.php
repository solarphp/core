<?php
require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Cache_Adapter_Apc extends Test_Solar_Cache_Adapter {

    protected $_Test_Solar_Cache_Adapter_Apc = array(
        'adapter' => 'Solar_Cache_Adapter_Apc',
        'config'  => array(
            'life'   => 7, // 7 seconds
        ),
    );
    
    public function __construct($config = null)
    {
        if (! extension_loaded('apc')) {
            $this->skip('apc extension not loaded');
        }
        parent::__construct($config);
    }
}
?>