<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/DataFilter.php';

/**
 * @todo Refactor all tests so they instantiate Solar_DataFilter themselves
 */
class Solar_DataFilterTest extends PHPUnit_Framework_TestCase
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
        $filter = Solar::factory('Solar_DataFilter');
        $this->assertTrue($filter instanceof Solar_DataFilter);
    }
    
    public function testSanitizeBoolean()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $list = array(
            true,
            'on', 'On', 'ON',
            'yes', 'Yes', 'YeS',
            'y', 'Y',
            'true', 'True', 'TrUe',
            't', 'T',
            1, '1',
            'not empty',
        );
        
        foreach ($list as $val) {
            $bool = $filter->sanitizeBoolean($val, true);
            $this->assertTrue($bool);
        }
        
        $list = array(
            false,
            'off', 'Off', 'OfF',
            'no', 'No', 'NO',
            'n', 'N',
            'false', 'False', 'FaLsE',
            'f', 'F',
            0, '0',
            '', '    ',
        );
        
        foreach ($list as $val) {
            $bool = $filter->sanitizeBoolean($val, true);
            $this->assertFalse($bool);
        }
    }
    
    public function testSanitizeFloat()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $before = 'abc ... 123.45 ,.../';
        $after = $filter->sanitizeFloat($before, true);
        $this->assertSame($after, 123.450);
        
        $before = 'a-bc .1. alkasldjf 23 aslk.45 ,.../';
        $after = $filter->sanitizeFloat($before, true);
        $this->assertSame($after, -.123450);
        
        $before = '1E5';
        $after = $filter->sanitizeFloat($before, true);
        $this->assertSame($after, 100000.0);
    }
    
    public function testSanitizeInt()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'abc ... 123.45 ,.../';
        $after = $filter->sanitizeInt($before, true);
        $this->assertSame($after, 12345);
        
        $before = 'a-bc .1. alkasldjf 23 aslk.45 ,.../';
        $after = $filter->sanitizeInt($before, true);
        $this->assertSame($after, -12345);
        
        $before = '1E5';
        $after = $filter->sanitizeInt($before, true);
        $this->assertSame($after, 100000);
    }
    
    public function testSanitizeIsoDate()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->sanitizeIsoDate($before, true);
        $this->assertSame($after, '1979-11-07');
    }
    
    public function testSanitizeIsoTime()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->sanitizeIsoTime($before, true);
        $this->assertSame($after, '12:34:00');
    }
    
    public function testSanitizeIsoTimestamp()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'Nov 7, 1979, 12:34pm';
        $after = $filter->sanitizeIsoTimestamp($before, true);
        $this->assertSame($after, '1979-11-07T12:34:00');
    }
    
    public function testSanitizeNumeric()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $before = 'abc ... 123.45 ,.../';
        $after = $filter->sanitizeNumeric($before, true);
        $this->assertSame($after, (string) 123.450);
        
        $before = 'a-bc .1. alkasldjf 23 aslk.45 ,.../';
        $after = $filter->sanitizeNumeric($before, true);
        $this->assertSame($after, (string) -.123450);
        
        $before = '1E5';
        $after = $filter->sanitizeNumeric($before, true);
        $this->assertSame($after, (string) 100000.0);
    }
    
    public function testSanitizeString()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 12345;
        $after = $filter->sanitizeString($before);
        $this->assertSame($after, '12345');
    }
    
    public function testSanitizeStringAlnum()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'abc 123 ,./';
        $after = $filter->sanitizeStringAlnum($before, true);
        
        $this->assertNotSame($before, $after, true);
        $this->assertSame($after, 'abc123');
    }
    
    public function testSanitizeStringAlpha()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'abc 123 ,./';
        $after = $filter->sanitizeStringAlpha($before, true);
        
        $this->assertNotSame($before, $after);
        $this->assertSame($after, 'abc');
    }
    
    public function testSanitizeStringEmail()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // values taken directly from filter/test/022.phpt
        $test = array(
            "a@b.c"                              => "a@b.c",
            "a[!@#$%^&*()@a@#$%^&*(.com@#$%^&*(" => "a[!@#$%^&*@a@#$%^&*.com@#$%^&*",
            "white space here \ \ \" som more"   => "whitespaceheresommore",
            "123456789000000"                    => "123456789000000",
        );
        
        foreach ($test as $source => $expect) {
            $actual = $filter->sanitizeStringEmail($source);
            $this->assertSame($actual, $expect);
        }
    }
    
    public function testSanitizeStringRegex()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'abc 123 ,./';
        $after = $filter->sanitizeStringRegex($before, '/[^a-z]/', '@', true);
        $this->assertSame($after, 'abc@@@@@@@@');
    }
    
    public function testSanitizeStringReplace()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'abc 123 ,./';
        $after = $filter->sanitizeStringReplace($before, ' ', '@', true);
        $this->assertSame($after, 'abc@123@,./');
    }
    
    public function testSanitizeStringUri()
    {
        $this->markTestSkipped('no test data from filter_var phpt file');
    }
    
    public function testSanitizeStringTrim()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = '  abc 123 ,./  ';
        $after = $filter->sanitizeStringTrim($before);
        $this->assertSame($after, 'abc 123 ,./');
    }
    
    public function testSanitizeStringTrim_OtherChars()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = '  abc 123 ,./  ';
        $after = $filter->sanitizeStringTrim($before, ' ,./');
        $this->assertSame($after, 'abc 123');
    }
    
    public function testSanitizeStringWord()
    {
        $filter = Solar::factory('Solar_DataFilter');
        $before = 'abc _ 123 - ,./';
        $after = $filter->sanitizeStringWord($before, true);
        $this->assertSame($after, 'abc_123');
    }
    
    public function testValidateAlnum()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            0, 1, 2, 5,
            '0', '1', '2', '5',
            'alphaonly',
            'AlphaOnLy',
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateAlnum($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            "Seven 8 nine",
            "non:alpha-numeric's",
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateAlnum($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            0, 1, 2, 5,
            '0', '1', '2', '5',
            'alphaonly',
            'AlphaOnLy',
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateAlnum($val, false));
        }
    }
    
    public function testValidateAlpha()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateAlpha($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            0, 1, 2, 5,
            '0', '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateAlpha($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateAlpha($val, false));
        }
    }
    
    public function testValidateBlank()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'empty'   => "",
            'space'   => " ",
            'tab'     => "\t",
            'newline' => "\n",
            'return'  => "\r",
            'multi'   => " \t \n \r ",
        );
        foreach ($test as $key => $val) {
            $this->assertTrue($filter->validateBlank($val));
        }
        
        // bad
        $test = array(
            0, 1, 2, 5,
            '0', '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateBlank($val));
        }
    }
    
    public function testValidateBoolean()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // boolean, blanks not allowed
        $list = array(
            true,
            'on', 'On', 'ON',
            'yes', 'Yes', 'YeS',
            'y', 'Y',
            'true', 'True', 'TrUe',
            't', 'T',
            1, '1',
            false,
            'off', 'Off', 'OfF',
            'no', 'No', 'NO',
            'n', 'N',
            'false', 'False', 'FaLsE',
            'f', 'F',
            0, '0',
        );
        
        foreach ($list as $val) {
            $bool = $filter->validateBoolean($val, false);
            $this->assertTrue($bool);
        }
        
        // not boolean, blanks not allowed
        $list = array(
            'nothing', 123,
        );
        
        foreach ($list as $val) {
            $bool = $filter->validateBoolean($val, false);
            $this->assertFalse($bool);
        }
        
        // boolean, blanks allowed
        $list = array(
            '', '    ',
        );
        
        foreach ($list as $val) {
            $bool = $filter->validateBoolean($val, true);
            $this->assertTrue($bool);
        }
    }
    
    public function testValidateCtype()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateCtype($val, 'alpha'));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            0, 1, 2, 5,
            '0', '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateCtype($val, 'alpha'));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateCtype($val, 'alpha', false));
        }
    }
    
    public function testValidateEmail()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            "pmjones@solarphp.net",
            "no.body@no.where.com",
            "any-thing@gmail.com",
            "any_one@hotmail.com",
            "nobody1234567890@yahoo.co.uk",
            "something+else@example.com",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateEmail($val));
        }
        
        // bad, or are blank
        $test = array(
            "something @ somewhere.edu",
            "the-name.for!you",
            "non:alpha@example.com",
            "",
            "\t\n",
            " ",
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateEmail($val));
        }
        
        
        // blanks allowed
        $test = array(
            "",
            "\t\n",
            " ",
            "pmjones@solarphp.net",
            "no.body@no.where.com",
            "any-thing@gmail.com",
            "any_one@hotmail.com",
            "nobody1234567890@yahoo.co.uk",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateEmail($val, false));
        }
    }
    
    public function testValidateFloat()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            "+123456.7890",
            12345.67890,
            -123456789.0,
            -123.4567890,
            '-1.23',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateFloat($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            "-abc.123",
            "123.abc",
            "123,456",
            '00.00123.4560.00',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateFloat($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            "+123456.7890",
            12345.67890,
            -123456789.0,
            -123.4567890,
            '-1.23',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateFloat($val, false));
        }
    }
    
    public function testValidateInKeys()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // basic options
        $opts = array(
            0      => 'val0',
            1      => 'val1',
            'key0' => 'val3',
            'key1' => 'val4',
            'key2' => 'val5'
        );
        
        
        // good
        $test = array_keys($opts);
        foreach ($test as $val) {
            $this->assertTrue($filter->validateInKeys($val, $opts));
        }
        
        // bad, or are blank
        $test = array('a', 'b', 'c', '', ' ');
        foreach ($test as $val) {
            $this->assertFalse($filter->validateInKeys($val, $opts));
        }
        
        
        // blanks allowed
        $test = array_keys($opts);
        $test[] = " ";
        $test[] = "\r";
        foreach ($test as $val) {
            $this->assertTrue($filter->validateInKeys($val, $opts, false));
        }
    }
    
    public function testValidateInList()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // basic options
        $opts = array(
            0      => 'val0',
            1      => 'val1',
            'key0' => 'val3',
            'key1' => 'val4',
            'key2' => 'val5'
        );
        
        
        // good
        $test = $opts;
        foreach ($test as $val) {
            $this->assertTrue($filter->validateInList($val, $opts));
        }
        
        // bad, or are blank
        $test = array('a', 'b', 'c', '', ' ');
        foreach ($test as $val) {
            $this->assertFalse($filter->validateInList($val, $opts));
        }
        
        
        // blanks allowed
        $test = $opts;
        $test[] = "";
        $test[] = " ";
        foreach ($test as $val) {
            $this->assertTrue($filter->validateInList($val, $opts, false));
        }
    }
    
    public function testValidateInt()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            "+1234567890",
            1234567890,
            -123456789.0,
            -1234567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateInt($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            "-abc.123",
            "123.abc",
            "123,456",
            '0000123.456000',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateInt($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            "+1234567890",
            1234567890,
            -123456789.0,
            -1234567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateInt($val, false));
        }
    }
    
    public function testValidateIp()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            '141.225.185.101',
            '255.0.0.0',
            '0.255.0.0',
            '0.0.255.0',
            '0.0.0.255',
            '127.0.0.1',
            '2001:0db8:0000:0000:0000:0000:1428:57ab',
            '2001:0db8:0000:0000:0000::1428:57ab',
            '2001:0db8:0:0:0:0:1428:57ab',
            '2001:0db8:0:0::1428:57ab',
            '2001:0db8::1428:57ab',
            '2001:db8::1428:57ab',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIp($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            '127.0.0.1234',
            '127.0.0.0.1',
            '256.0.0.0',
            '0.256.0.0',
            '0.0.256.0',
            '0.0.0.256',
            '1.',
            '1.2.',
            '1.2.3.',
            '1.2.3.4.',
            'a.b.c.d',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateIp($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            '141.225.185.101',
            '255.0.0.0',
            '0.255.0.0',
            '0.0.255.0',
            '0.0.0.255',
            '127.0.0.1',
            '2001:0db8:0000:0000:0000:0000:1428:57ab',
            '2001:0db8:0000:0000:0000::1428:57ab',
            '2001:0db8:0:0:0:0:1428:57ab',
            '2001:0db8:0:0::1428:57ab',
            '2001:0db8::1428:57ab',
            '2001:db8::1428:57ab',
            "\n", "\r\n",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIp($val, false));
        }
    }
    
    public function testValidateIpv4()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            '141.225.185.101',
            '255.0.0.0',
            '0.255.0.0',
            '0.0.255.0',
            '0.0.0.255',
            '127.0.0.1',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIpv4($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            '127.0.0.1234',
            '127.0.0.0.1',
            '256.0.0.0',
            '0.256.0.0',
            '0.0.256.0',
            '0.0.0.256',
            '1.',
            '1.2.',
            '1.2.3.',
            '1.2.3.4.',
            'a.b.c.d',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateIpv4($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            '141.225.185.101',
            '255.0.0.0',
            '0.255.0.0',
            '0.0.255.0',
            '0.0.0.255',
            '127.0.0.1',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIpv4($val, false));
        }
    }
    
    public function testValidateIpv6()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            '2001:0db8:0000:0000:0000:0000:1428:57ab',
            '2001:0db8:0000:0000:0000::1428:57ab',
            '2001:0db8:0:0:0:0:1428:57ab',
            '2001:0db8:0:0::1428:57ab',
            '2001:0db8::1428:57ab',
            '2001:db8::1428:57ab',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIpv6($val));
        }
        
        // bad, or are blank
        $test = array(
            '141.225.185.101',
            '255.0.0.0',
            '0.255.0.0',
            '0.0.255.0',
            '0.0.0.255',
            '127.0.0.1',
            '', '   ',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateIpv6($val));
        }
        
        // blanks allowed
        $test = array(
            '2001:0db8:0000:0000:0000:0000:1428:57ab',
            '2001:0db8:0000:0000:0000::1428:57ab',
            '2001:0db8:0:0:0:0:1428:57ab',
            '2001:0db8:0:0::1428:57ab',
            '2001:0db8::1428:57ab',
            '2001:db8::1428:57ab',
            '', '    '
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIpv6($val, false));
        }
    }
    
    public function testValidateIsoDate()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            '0001-01-01',
            '1970-08-08',
            '1979-11-07',
            '2004-02-29',
            '9999-12-31',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIsoDate($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            '1-2-3',
            '0001-1-1',
            '1-01-1',
            '1-1-01',
            '0000-00-00',
            '0000-01-01',
            '0010-20-40',
            '2005-02-29',
            '9999.12:31',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateIsoDate($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            '0001-01-01',
            '1970-08-08',
            '1979-11-07',
            '9999-12-31',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIsoDate($val, false));
        }
        
    }
    
    public function testValidateIsoTime()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            '00:00:00',
            '12:34:56',
            '23:59:59',
            '24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIsoTime($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            '24:00:01',
            '12.00.00',
            '12-34_56',
            ' 12:34:56 ',
            '  :34:56',
            '12:  :56',
            '12:34   ',
            '12:34'
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateIsoTime($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            '00:00:00',
            '12:34:56',
            '23:59:59',
            '24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIsoTime($val, false));
        }
    }
    
    public function testValidateIsoTimeStamp()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            '0001-01-01T00:00:00',
            '1970-08-08T12:34:56',
            '2004-02-29T24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIsoTimestamp($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            '0000-00-00T00:00:00',
            '0000-01-01T12:34:56',
            '0010-20-40T12:34:56',
            '1979-11-07T12:34',
            '1970-08-08t12:34:56',
            '           24:00:00',
            '          T        ',
            '9999-12-31         ',
            '9999.12:31 ab:cd:ef',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateIsoTimestamp($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            '0001-01-01T00:00:00',
            '1970-08-08T12:34:56',
            '2004-02-29T24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateIsoTimestamp($val, false));
        }
    }
    
    public function testValidateLocaleCode()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'en_US',
            'pt_BR',
            'xx_YY',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateLocaleCode($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            'PT_br',
            'EN_US',
            '12_34',
            'en_USA',
            'America/Chicago',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateLocaleCode($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            'en_US',
            'pt_BR',
            'xx_YY',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateLocaleCode($val, false));
        }
    }
    
    public function testValidateMax()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $max = 3;
        
        // good
        $test = array(
            1, 2, 3,
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateMax($val, $max));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateMax($val, $max));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            1, 2, 3,
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateMax($val, $max, false));
        }
    }
    
    public function testValidateMaxLength()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $len = strlen("I am the very model");
        
        // good
        $test = array(
            0,
            "I am",
            "I am the very model",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateMaxLength($val, $len));
        }
        
        // bad, or are blank
        $test = array(
            "", " ",
            "I am the very model of a modern",
            "I am the very model of a moden Major-General",
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateMaxLength($val, $len));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            "I am",
            "I am the very model",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateMaxLength($val, $len, false));
        }
    }
    
    public function testValidateMimeType()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'text/plain',
            'text/xhtml+xml',
            'application/vnd.ms-powerpoint',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateMimeType($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            'text/',
            '/something',
            0, 1, 2, 5,
            '0', '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($filter->validateMimeType($val));
        }
        
        
        // blanks allowed
        $test = array(
            '', ' ',
            'text/plain',
            'text/xhtml+xml',
            'application/vnd.ms-powerpoint',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateMimeType($val, null, false));
        }
        
        // only certain types allowed
        $allowed = array('text/plain', 'text/html', 'text/xhtml+xml');
        $this->assertTrue($filter->validateMimeType('text/html', $allowed));
        $this->assertFalse($filter->validateMimeType('application/vnd.ms-powerpoint', $allowed));
    }
    
    public function testValidateMin()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $min = 4;
        
        // good
        $test = array(
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateMin($val, $min));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            0, 1, 2, 3, ' ', ''
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateMin($val, $min));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateMin($val, $min, false));
        }
    }
    
    public function testValidateMinLength()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $len = strlen("I am the very model");
        
        // good
        $test = array(
            "I am the very model",
            "I am the very model of a modern",
            "I am the very model of a moden Major-General",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateMinLength($val, $len));
        }
        
        // bad, or are blank
        $test = array(
            "", " ",
            0,
            "I am",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($filter->validateMinLength($val, $len));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            "I am the very model",
            "I am the very model of a modern",
            "I am the very model of a moden Major-General",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateMinLength($val, $len, false));
        }
    }
    
    public function testValidateNotBlank()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            0, 1, 2, 5,
            '0', '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateNotBlank($val));
        }
        
        // bad
        $test = array(
            'empty'   => "",
            'space'   => " ",
            'tab'     => "\t",
            'newline' => "\n",
            'return'  => "\r",
            'multi'   => " \t \n \r ",
        );
        foreach ($test as $key => $val) {
            $this->assertFalse($filter->validateNotBlank($val));
        }
        
    }
    
    public function testValidateNotZero()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good (are non-zero)
        $test = array(
            '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
            '+-0.0',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateNotZero($val));
        }
        
        // bad (are in fact zero, or are blank)
        $test = array(
            ' ', '',
            '0', 0, '00000.00', '+0', '-0', "+00.00",
        );
        foreach ($test as $key => $val) {
            $this->assertFalse($filter->validateNotZero($val));
        }
        
        
        // blank
        $test = array(
            ' ', '',
            '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
            '+-0.0',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateNotZero($val, false));
        }
    }
    
    public function testValidateNumeric()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            "+123456.7890",
            12345.67890,
            -123456789.0,
            -123.4567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateNumeric($val));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            "-abc.123",
            "123.abc",
            "123,456",
            '00.00123.4560.00',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateNumeric($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            "+123456.7890",
            12345.67890,
            -123456789.0,
            -123.4567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateNumeric($val, false));
        }
    }
    
    public function testValidateRange()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $min = 4;
        $max = 6;
        
        // good
        $test = array(
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateRange($val, $min, $max));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            0, 1, 2, 3, 7, 8, 9,
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateRange($val, $min, $max));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateRange($val, $min, $max, false));
        }
    }
    
    public function testValidateRangeLength()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $min = 4;
        $max = 6;
        
        // good
        $test = array(
            "abcd",
            "abcde",
            "abcdef",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateRangeLength($val, $min, $max));
        }
        
        // bad, or are blank
        $test = array(
            "", " ",
            'a', 'ab', 'abc',
            'abcdefg', 'abcdefgh', 'abcdefghi', 
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($filter->validateRangeLength($val, $min, $max));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            "abcd",
            "abcde",
            "abcdef",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateRangeLength($val, $min, $max, false));
        }
    }
    
    public function testValidateRegex()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $expr = '/^[\+\-]?[0-9]+$/';
        
        // good
        $test = array(
            "+1234567890",
            1234567890,
            -123456789.0,
            -1234567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateRegex($val, $expr));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            "-abc.123",
            "123.abc",
            "123,456",
            '0000123.456000',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateRegex($val, $expr));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            "+1234567890",
            1234567890,
            -123456789.0,
            -1234567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateRegex($val, $expr, false));
        }

    }
    
    public function testValidateSizeScope()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        $good = array(
            "+1234567890",
            '0000123.456000',
            123.4560000,
            12345.67890,
            123456.7890,
            1234567.890,
            12345678.90,
            123456789.0,
            1234567890,
            -12345.67890,
            -123456.7890,
            -1234567.890,
            -12345678.90,
            -123456789.0,
            -1234567890,
        );
        
        $bad = array(
            ' ', '',
            "-abc.123",
            "123,456",
            .1234567890,
            1.234567890,
            12.34567890,
            123.4567890,
            1234.567890,
            -.1234567890,
            -1.234567890,
            -12.34567890,
            -123.4567890,
            -1234.567890,
        );
        
        $size = 10;
        $scope = 4;
        
        // good
        foreach ($good as $val) {
            $this->assertTrue($filter->validateSizeScope($val, $size, $scope));
        }
        
        // bad, or are blank
        foreach ($bad as $val) {
            $this->assertFalse($filter->validateSizeScope($val, $size, $scope));
        }
        
        // blanks allowed
        $test = $good;
        $test[] = "";
        $test[] = " ";
        foreach ($test as $val) {
            $this->assertTrue($filter->validateSizeScope($val, $size, $scope, false));
        }
    }
    
    public function testValidateSepWords()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'abc def ghi',
            ' abc def ',
            'a1s_2sd and another',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateSepWords($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            'a, b, c',
            'ab-db cd-ef',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateSepWords($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            'abc def ghi',
            ' abc def ',
            'a1s_2sd and another',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateSepWords($val, ' ', false));
        }
        
        // alternative separator
        $test = array(
            'abc,def,ghi',
            'abc,def',
            'a1s_2sd,and,another',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateSepWords($val, ','));
        }
    }
    
    public function testValidateString()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            12345,
            123.45,
            true,
            false,
            'string',
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateString($val));
        }
        
        // bad, or blank
        $test = array(
            array(),
            new StdClass,
            '',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateString($val));
        }
        
        // blanks allowed
        $test = array(
            12345,
            123.45,
            true,
            false,
            'string',
            '', ' ', "\t\n",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateString($val, false));
        }
    }
    
    public function testValidateUri()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            "http://example.com",
            "https://example.com/path/to/file.php",
            "ftp://example.com/path/to/file.php/info",
            "news://example.com/path/to/file.php/info?foo=bar&baz=dib#zim",
            "gopher://example.com/?foo=bar&baz=dib#zim",
            "mms://user:pass@site.info/path/to/file.php/info?foo=bar&baz=dib#zim",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateUri($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            'a,', '^b', '%',
            'ab-db cd-ef',
            'example.com',
            'http://',
        );
        foreach ($test as $val) {
            $this->assertFalse($filter->validateUri($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            "foo://example.com/path/to/file.php/info?foo=bar&baz=dib#zim",
            "mms://user:pass@site.info/path/to/file.php/info?foo=bar&baz=dib#zim",
        );
        foreach ($test as $val) {
            $this->assertTrue($filter->validateUri($val, null, false));
        }
    }
    
    public function testValidateWord()
    {
        $filter = Solar::factory('Solar_DataFilter');
        
        // good
        $test = array(
            'abc', 'def', 'ghi',
            'abc_def',
            'A1s_2Sd',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateWord($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            'a,', '^b', '%',
            'ab-db cd-ef',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($filter->validateWord($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            'abc', 'def', 'ghi',
            'abc_def',
            'A1s_2Sd',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($filter->validateWord($val, false));
        }
    }
}
