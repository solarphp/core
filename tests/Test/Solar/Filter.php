<?php

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

class Test_Solar_Filter extends Solar_Test {
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function setup()
    {
    }
    
    public function teardown()
    {
    }
    
    public function test__construct()
    {
        $filter = Solar::factory('Solar_Filter');
        $this->assertInstance($filter, 'Solar_Filter');
    }
    
    public function testAlpha()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->alpha($before);
        $this->assertSame($after, 'abc');
    }
    
    public function testStripAlpha()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->stripAlpha($before);
        $this->assertSame($after, ' 123 ,./');
    }
    
    public function testAlnum()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->alnum($before);
        $this->assertSame($after, 'abc123');
    }
    
    public function testStripAlnum()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->stripAlnum($before);
        $this->assertSame($after, '  ,./');
    }
    
    public function testBlank()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = "abc \n 123 \t ,./";
        $after = $filter->blank($before);
        $this->assertSame($after, " \n  \t ");
    }
    
    public function testStripBlank()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = "abc \n 123 \t ,./";
        $after = $filter->stripBlank($before);
        $this->assertSame($after, "abc123,./");
    }
    
    public function testNumeric()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->numeric($before);
        $this->assertSame($after, '123');
    }
    
    public function testStripNumeric()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->stripNumeric($before);
        $this->assertSame($after, 'abc  ,./');
    }
    
    public function testWord()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc _ 123 - ,./';
        $after = $filter->word($before);
        $this->assertSame($after, 'abc_123');
    }
    
    public function testStripWord()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc _ 123 - ,./';
        $after = $filter->stripWord($before);
        $this->assertSame($after, '   - ,./');
    }
    
    public function testFormatDate()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->formatDate($before);
        $this->assertSame($after, '1979-11-07');
    }
    
    public function testFormatTime()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->formatTime($before);
        $this->assertSame($after, '12:34:00');
    }
    
    public function testFormatTimestamp()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->formatTimestamp($before);
        $this->assertSame($after, '1979-11-07T12:34:00');
    }
    
    public function testPregReplace()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->pregReplace($before, '/[^a-z]/', '@');
        $this->assertSame($after, 'abc@@@@@@@@');
    }
    
    public function testStrReplace()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->strReplace($before, ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testTrim()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = '  abc 123 ,./  ';
        $after = $filter->trim($before);
        $this->assertSame($after, 'abc 123 ,./');
    }
    
    public function testCast()
    {
        $filter = Solar::factory('Solar_Filter');

        $types = array(
            'array', 'boolean', 'integer',
            'double', 'string', 'object', 'NULL',
        );

        $before = '-123.456';

        foreach ($types as $type) {
            $after = $filter->cast($before, $type);
            $this->assertSame(gettype($after), $type);
        }
    }
    
    public function testCallback_function()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->callback($before, 'test_filter_callback', ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testCallback_staticMethod()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->callback($before, array('Test_Filter_Callback', 'execStatic'), ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }

    public function testCallback_objectMethod()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $obj = new Test_Filter_Callback();
        $after = $filter->callback($before, array($obj, 'exec'), ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testMultiple()
    {
        $filter = Solar::factory('Solar_Filter');

        $before = "  310a847 640";

        $multi = array(
            'stripBlank',
            'stripAlpha',
            array('cast', 'int'),
            'formatTimestamp',
        );

        $after = $filter->multiple($before, $multi);

        $this->assertSame($after, '1979-11-07T12:34:00');
    }
}
?>