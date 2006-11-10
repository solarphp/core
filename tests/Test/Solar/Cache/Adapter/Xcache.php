<?php
require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Cache_Adapter_Xcache extends Test_Solar_Cache_Adapter {

    protected $_Test_Solar_Cache_Adapter_Xcache = array(
        'adapter' => 'Solar_Cache_Adapter_Xcache',
        'config'  => array(
            'life'   => 7, // 7 seconds
            'user' => 'foo',
            'pass' => 'bar',
        ),
    );

    public function __construct($config = null)
    {
        if (! extension_loaded('xcache')) {
            $this->skip('xcache extension not loaded');
        }
        parent::__construct($config);
    }
}
?>