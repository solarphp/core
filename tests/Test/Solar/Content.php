<?php
class Test_Solar_Content extends Solar_Test {
    
    public function __construct($config = null)
    {
        Solar::register('sql', 'Solar_Sql');
    }
    
    public function __destruct()
    {
        unlink(Solar::config('Solar_Sql', 'name'));
    }
    
    public function test__construct()
    {
        $content = Solar::factory('Solar_Content');
        $this->_assertInstance($content, 'Solar_Content');
    }
}
?>