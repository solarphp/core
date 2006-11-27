<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Sql_Adapter_Pgsql extends Test_Solar_Sql_Adapter {
    
    protected $_Test_Solar_Sql_Adapter_Pgsql = array(
        'adapter' => 'Solar_Sql_Adapter_Pgsql',
    );
    
    protected $_quote_expect = "'\"foo\" bar ''baz'''";
    
    protected $_quote_array_expect = "'\"foo\"', 'bar', '''baz'''";
    
    protected $_quote_into_expect = "foo = '''bar'''";
    
    protected $_quote_multi_expect = "id = 1 AND foo = 'bar' AND zim IN('dib', 'gir', 'baz')";
}
?>