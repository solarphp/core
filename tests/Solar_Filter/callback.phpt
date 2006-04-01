--TEST--
Solar_Filter::callback()
--FILE---
<?php
// include ../_prepend.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_prepend.inc')) {
    require dirname(dirname(__FILE__)) . '/_prepend.inc';
}

// include ./_prepend.inc
if (is_readable(dirname(__FILE__) . '/_prepend.inc')) {
    require dirname(__FILE__) . '/_prepend.inc';
}

// ---------------------------------------------------------------------

function test_filter_callback($value, $find, $with)
{
    return str_replace($find, $with, $value);
}

class Test_Filter_Callback {
    public function exec($value, $find, $with)
    {
        return str_replace($find, $with, $value);
    }
    
    public static function execStatic($value, $find, $with)
    {
        return str_replace($find, $with, $value);
    }
}

$filter = Solar::factory('Solar_Filter');

$before = 'abc 123 ,./';

// function
$after = $filter->callback($before, 'test_filter_callback', ' ', '@');
$assert->same($after, 'abc@123@,./');

// static method
$after = $filter->callback($before, array('Test_Filter_Callback', 'execStatic'), ' ', '@');
$assert->same($after, 'abc@123@,./');

// object method
$obj = new Test_Filter_Callback();
$after = $filter->callback($before, array($obj, 'exec'), ' ', '@');
$assert->same($after, 'abc@123@,./');

// ---------------------------------------------------------------------

// include ./_append.inc
if (is_readable(dirname(__FILE__) . '/_append.inc')) {
    require dirname(__FILE__) . '/_append.inc';
}
// include ../_append.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_append.inc')) {
    require dirname(dirname(__FILE__)) . '/_append.inc';
}
?>
--EXPECT--
