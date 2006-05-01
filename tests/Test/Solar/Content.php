<?php
class Test_Solar_Content extends Solar_Test {
    
    public function __construct($config = null)
    {
        //$this->todo('need to fix Solar_Sql_Table index creation');
        Solar::register('sql', 'Solar_Sql');
    }
    
    public function __destruct()
    {
        unlink(Solar::config('Solar_Sql', 'name'));
    }
    
    public function test__construct()
    {
        $content = Solar::factory('Solar_Content');
        $this->assertInstance($content, 'Solar_Content');
    }
}
?>