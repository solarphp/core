<?php
Solar::loadClass('Solar_Test_Bench');

class Bench_Solar_ArrayMerge extends Solar_Test_Bench {

    protected $_one = array(
        'a' => '1',
        'b' => '2',
        'c' => '3',
        'd' => '4',
        'e' => '5',
        'f' => '6',
        'g' => '7',
        'h' => '8',
        'i' => '9',
        'j' => '0',
        'k' => 'a',
        'l' => 'b',
        'm' => 'c',
        'n' => 'd',
        'o' => 'e',
        'p' => 'f',
    );
    
    protected $_two = array(
        'k' => 'a',
        'l' => 'b',
        'm' => 'c',
        'n' => '1',
        'o' => '2',
        'p' => '3',
        'q' => '4',
        'r' => '5',
        's' => '6',
        't' => '7',
        'u' => '8',
        'v' => '9',
        'x' => '0',
        'w' => 'a',
        'y' => 'b',
        'z' => 'c',
    );
    
    
    public function benchMerge()
    {
        $three = array_merge($this->_one, $this->_two);
    }
    
    public function benchForeach()
    {
        $three = $this->_one;
        foreach ($this->_two as $key => $val) {
            $three[$key] = $val;
        }
    }    

}
