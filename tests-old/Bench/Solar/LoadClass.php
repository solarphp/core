<?php
Solar::loadClass('Solar_Test_Bench');

class Bench_Solar_LoadClass extends Solar_Test_Bench {
    
    public $file = 'Solar/Sql.php';
    public $class = 'Solar_Sql';
    
    public function benchLoadClass()
    {
        Solar::loadClass($this->class);        
    }
    
    public function benchRequireOnce()
    {
        require_once $this->file;
    }
}
?>