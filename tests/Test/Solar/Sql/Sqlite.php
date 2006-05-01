<?php

require_once realpath(dirname(__FILE__) . '/../Sql.php');

class Test_Solar_Sql_Sqlite extends Test_Solar_Sql {
    
    protected $_config = array(
        'driver' => 'Solar_Sql_Driver_Sqlite',
        'name'   => '/tmp/Test_Solar_Sql_Sqlite.sq3',
    );
    
    protected $_quote_expect = "'\"foo\" bar ''baz'''";
    
    protected $_quote_array_expect = "'\"foo\"', 'bar', '''baz'''";
    
    protected $_quote_multi_expect = "id = 1 AND foo = 'bar' AND zim IN('dib', 'gir', 'baz')";
    
    protected $_quote_into_expect = "foo = '''bar'''";
    
    public function _destruct()
    {
        unlink('/tmp/Test_Solar_Sql_Sqlite.sq3');
    }
    
    public function testDropColumn()
    {
        $this->skip('drop column not supported by sqlite');
    }
}
?>