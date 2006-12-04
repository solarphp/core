<?php
require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Cache_Adapter_Eaccelerator extends Test_Solar_Cache_Adapter {

    protected $_Test_Solar_Cache_Adapter_Eaccelerator = array(
        'adapter' => 'Solar_Cache_Adapter_Eaccelerator',
        'config'  => array(
            'life'   => 7, // 7 seconds
        ),
    );

    public function __construct($config = null)
    {
        if (! extension_loaded('eaccellerator')) {
            $this->skip('eaccellerator extension not loaded');
        }
        parent::__construct($config);
    }
}
?>