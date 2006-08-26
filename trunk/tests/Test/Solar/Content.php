<?php
class Test_Solar_Content extends Solar_Test {
    
    public function test__construct()
    {
        $sql = Solar::factory('Solar_Sql');
        $content = Solar::factory('Solar_Content', array('sql' => $sql));
        $this->assertInstance($content, 'Solar_Content');
    }
}
?>