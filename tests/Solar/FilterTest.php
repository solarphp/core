<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/Filter.php';

class Solar_FilterTest extends PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        Solar::start('config.inc.php');
    }
    
    public function tearDown() 
    {
        Solar::stop();
    }
    
    public function testCanInstantiateThroughFactory() 
    {
        $object = Solar::factory('Solar_Filter');
        $this->assertTrue($object instanceof Solar_Filter);
    }
    
    public function testCanFilterOutNonAlphaCharactersByAlpha() 
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->alpha($before);
        
        $this->assertNotSame($before, $after);
        $this->assertSame($after, 'abc');
    }
    
    public function testCanStripAlphaCharactersByStripalpha()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->stripAlpha($before);
        
        $this->assertNotSame($before, $after);
        $this->assertSame($after, ' 123 ,./');
    }
    
    public function testCanFilterOutNonAlphaNumericCharactersByAlnum()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->alnum($before);
        
        $this->assertNotSame($before, $after);
        $this->assertSame($after, 'abc123');
    }
    
    public function testCanStripAlphaNumericCharactersByStripalnum()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->stripAlnum($before);
        
        $this->assertNotSame($before, $after);
        $this->assertSame($after, '  ,./');
    }
    
    public function testCanBlankAllNonBlankCharactersByBlank()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = "abc \n 123 \t ,./";
        $after = $filter->blank($before);
        $this->assertSame($after, " \n  \t ");
    }
    
    public function testCanStripAllWhitespaceCharactersByStripblank()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = "abc \n 123 \t ,./";
        $after = $filter->stripBlank($before);
        $this->assertSame($after, "abc123,./");
    }
    
    public function testCanStripAllNonNumericCharactersByNumeric()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->numeric($before);
        $this->assertSame($after, '123');
    }
    
    public function testCanStripNumericCharactersByStripnumeric()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->stripNumeric($before);
        $this->assertSame($after, 'abc  ,./');
    }
    
    public function testCanStripAllNonWordCharactersByWord()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc _ 123 - ,./';
        $after = $filter->word($before);
        $this->assertSame($after, 'abc_123');
    }
    
    public function testCanStripAllWordCharactersByStripWord()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc _ 123 - ,./';
        $after = $filter->stripWord($before);
        $this->assertSame($after, '   - ,./');
    }
    
    public function testCanFormatDateFromFullTextDateByFormatdate()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->formatDate($before);
        $this->assertSame($after, '1979-11-07');
    }
    
    public function testCanFormatTimeFromFullTextDateByFormattime()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->formatTime($before);
        $this->assertSame($after, '12:34:00');
    }
    
    public function testCanFormatISOTimestampFromTextDateByFormattimestamp()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->formatTimestamp($before);
        $this->assertSame($after, '1979-11-07T12:34:00');
    }
    
    public function testCanPerformPregReplacesByPregreplace()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->pregReplace($before, '/[^a-z]/', '@');
        $this->assertSame($after, 'abc@@@@@@@@');
    }
    
    public function testCanPerformSimpleStringReplaceByStrreplace()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->strReplace($before, ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testCanPerformLeftAndRightTrimByTrim()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = '  abc 123 ,./  ';
        $after = $filter->trim($before);
        $this->assertSame($after, 'abc 123 ,./');
    }
    
    public function testCanCastValuesByCast()
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
    
    public function testCanFilterThroughFunctionCallbackByCallback()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $after = $filter->callback($before, 'solar_test_filter_callback', ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testCanFilterThroughStaticMethodCallbackByCallback()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        Solar::loadClass('Solar_Test_Example');
        $after = $filter->callback($before, array('Solar_Test_Example', 'staticFilterCallback'), ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }

    public function testCanFilterThroughObjectMethodCallbackByCallback()
    {
        $filter = Solar::factory('Solar_Filter');
        $before = 'abc 123 ,./';
        $obj = Solar::factory('Solar_Test_Example');
        $after = $filter->callback($before, array($obj, 'filterCallback'), ' ', '@');
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testCanPerformMultipleFiltersByMultiple()
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

function solar_test_filter_callback($value, $find, $with)
{
    return str_replace($find, $with, $value);
}