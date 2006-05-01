<?php

class customValid {
    static public function staticIsInt($val)
    {
        return is_int($val);
    }
    
    public function isInt($val)
    {
        return is_int($val);
    }
}

class Test_Solar_Valid extends Solar_Test {
    
    protected $_valid;
    
    public function __construct()
    {
        $this->_valid = Solar::factory('Solar_Valid');
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_valid, 'Solar_Valid');
    }
    
    public function testAlnum()
    {
        // good
        $test = array(
            0, 1, 2, 5,
            '0', '1', '2', '5',
            'alphaonly',
            'AlphaOnLy',
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->alnum($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            "Seven 8 nine",
            "non:alpha-numeric's",
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->alnum($val));
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
            $this->assertTrue($this->_valid->alnum($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testAlpha()
    {
        // good
        $test = array(
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->alpha($val));
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
            $this->assertFalse($this->_valid->alpha($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->alpha($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testBlank()
    {
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
            $this->assertTrue($this->_valid->blank($val));
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
            $this->assertFalse($this->_valid->blank($val));
        }
    }
    
    public function testCallback()
    {
        $callbacks = array(
            'function()'        => 'is_int',
            'static::method()'  => array('customValid', 'staticIsInt'),
            '$object->method()' => array(new customValid(), 'isInt'),
        );
            
        // good
        $test = array(
            1, 2, 5
        );
        foreach ($callbacks as $callbackName => $callback) {
            foreach ($test as $val) {
                $this->assertTrue($this->_valid->callback($val, $callback));
            }
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            4.5,
            '0', '1', '2', '5',
            "Seven 8 nine",
        );
        foreach ($callbacks as $callbackName => $callback) {
            foreach ($test as $val) {
                $this->assertFalse($this->_valid->callback($val, $callback));
            }
        }
    }
    
    public function testCtype()
    {
        // good
        $test = array(
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->ctype($val, 'alpha'));
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
            $this->assertFalse($this->_valid->ctype($val, 'alpha'));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            'alphaonly',
            'AlphaOnLy',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->ctype($val, 'alpha', Solar_Valid::OR_BLANK));
        }
    }
    
    public function testEmail()
    {
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
            $this->assertTrue($this->_valid->email($val));
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
            $this->assertFalse($this->_valid->email($val));
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
            $this->assertTrue($this->_valid->email($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testFeedback()
    {
        $this->_valid = Solar::factory('Solar_Valid');
        $method = 'range';
        $message = 'INVALID_NUMBER';
        $min = 4;
        $max = 6;
        $params = array($method, $message, $min, $max);
        
        // test that a valid value returns null
        $result = $this->_valid->feedback(5, $params);
        $this->assertNull($result);
        
        // test that an invalid value returns the message
        $result = $this->_valid->feedback(1, $params);
        $this->assertSame($result, $message);
    }
    
    public function testInKeys()
    {
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
            $this->assertTrue($this->_valid->inKeys($val, $opts));
        }
        
        // bad, or are blank
        $test = array('a', 'b', 'c', '', ' ');
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->inKeys($val, $opts));
        }
        
        
        // blanks allowed
        $test = array_keys($opts);
        $test[] = " ";
        $test[] = "\r";
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->inKeys($val, $opts, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testInList()
    {
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
            $this->assertTrue($this->_valid->inList($val, $opts));
        }
        
        // bad, or are blank
        $test = array('a', 'b', 'c', '', ' ');
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->inList($val, $opts));
        }
        
        
        // blanks allowed
        $test = $opts;
        $test[] = "";
        $test[] = " ";
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->inList($val, $opts, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testInteger()
    {
        // good
        $test = array(
            "+1234567890",
            1234567890,
            -123456789.0,
            -1234567890,
            '-123',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->integer($val));
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
            $this->assertFalse($this->_valid->integer($val));
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
            $this->assertTrue($this->_valid->integer($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testIpv4()
    {
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
            $this->assertTrue($this->_valid->ipv4($val));
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
            $this->assertFalse($this->_valid->ipv4($val));
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
            $this->assertTrue($this->_valid->ipv4($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testIsoDate()
    {
        // good
        $test = array(
            '0001-01-01',
            '1970-08-08',
            '1979-11-07',
            '2004-02-29',
            '9999-12-31',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->isoDate($val));
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
            $this->assertFalse($this->_valid->isoDate($val));
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
            $this->assertTrue($this->_valid->isoDate($val, Solar_Valid::OR_BLANK));
        }
        
    }
    
    public function testIsoTime()
    {
        // good
        $test = array(
            '00:00:00',
            '12:34:56',
            '23:59:59',
            '24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->isoTime($val));
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
            $this->assertFalse($this->_valid->isoTime($val));
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
            $this->assertTrue($this->_valid->isoTime($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testIsoTimeStamp()
    {
        // good
        $test = array(
            '0001-01-01T00:00:00',
            '1970-08-08T12:34:56',
            '2004-02-29T24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->isoTimestamp($val));
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
            $this->assertFalse($this->_valid->isoTimestamp($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            '0001-01-01T00:00:00',
            '1970-08-08T12:34:56',
            '2004-02-29T24:00:00',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->isoTimestamp($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testLocaleCode()
    {
        // good
        $test = array(
            'en_US',
            'pt_BR',
            'xx_YY',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->localeCode($val));
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
            $this->assertFalse($this->_valid->localeCode($val));
        }
        
        
        // blanks allowed
        $test = array(
            "", ' ',
            'en_US',
            'pt_BR',
            'xx_YY',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->localeCode($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testMax()
    {
        $this->_valid = Solar::factory('Solar_Valid');
        $max = 3;
        
        // good
        $test = array(
            1, 2, 3,
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->max($val, $max));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->max($val, $max));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            1, 2, 3,
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->max($val, $max, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testMaxLength()
    {
        $len = strlen("I am the very model");
        
        // good
        $test = array(
            0,
            "I am",
            "I am the very model",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->maxLength($val, $len));
        }
        
        // bad, or are blank
        $test = array(
            "", " ",
            "I am the very model of a modern",
            "I am the very model of a moden Major-General",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($this->_valid->maxLength($val, $len));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            "I am",
            "I am the very model",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->maxLength($val, $len, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testMimeType()
    {
        // good
        $test = array(
            'text/plain',
            'text/xhtml+xml',
            'application/vnd.ms-powerpoint',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->mimeType($val));
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
            $this->assertFalse($this->_valid->mimeType($val));
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
            $this->assertTrue($this->_valid->mimeType($val, null, Solar_Valid::OR_BLANK));
        }
        
        // only certain types allowed
        $allowed = array('text/plain', 'text/html', 'text/xhtml+xml');
        $this->assertTrue($this->_valid->mimeType('text/html', $allowed));
        $this->assertFalse($this->_valid->mimeType('application/vnd.ms-powerpoint', $allowed));
    }
    
    public function testMin()
    {
        $this->_valid = Solar::factory('Solar_Valid');
        $min = 4;
        
        // good
        $test = array(
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->min($val, $min));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            0, 1, 2, 3, ' ', ''
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->min($val, $min));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->min($val, $min, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testMinLength()
    {
        $len = strlen("I am the very model");
        
        // good
        $test = array(
            "I am the very model",
            "I am the very model of a modern",
            "I am the very model of a moden Major-General",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->minLength($val, $len));
        }
        
        // bad, or are blank
        $test = array(
            "", " ",
            0,
            "I am",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($this->_valid->minLength($val, $len));
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
            $this->assertTrue($this->_valid->minLength($val, $len, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testMultiple()
    {
        $this->_valid = Solar::factory('Solar_Valid');
        $multi = array(
            array('min', 4),
            array('max', 7),
            'integer',
        );
        
        // good
        $test = array(
            '4', 5, 6, '7'
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->multiple($val, $multi));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            1, 2, 3, 5.5, 8, 9, 10
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->multiple($val, $multi));
        }
        
        // we don't test "allowed-blank" in multiple,
        // because the different validations check for blanks
        // in different ways.
    }
    
    public function testNotBlank()
    {
        // good
        $test = array(
            0, 1, 2, 5,
            '0', '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->notBlank($val));
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
            $this->assertFalse($this->_valid->notBlank($val));
        }
        
    }
    
    public function testNotZero()
    {
        // good (are non-zero)
        $test = array(
            '1', '2', '5',
            "Seven 8 nine",
            "non:alpha-numeric's",
            'someThing8else',
            '+-0.0',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->notZero($val));
        }
        
        // bad (are in fact zero, or are blank)
        $test = array(
            ' ', '',
            '0', 0, '00000.00', '+0', '-0', "+00.00",
        );
        foreach ($test as $key => $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($this->_valid->notZero($val));
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
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->notZero($val, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testRange()
    {
        $this->_valid = Solar::factory('Solar_Valid');
        $min = 4;
        $max = 6;
        
        // good
        $test = array(
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->range($val, $min, $max));
        }
        
        // bad, or are blank
        $test = array(
            ' ', '',
            0, 1, 2, 3, 7, 8, 9,
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->range($val, $min, $max));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            4, 5, 6
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->range($val, $min, $max, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testRangeLength()
    {
        $this->_valid = Solar::factory('Solar_Valid');
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
            $this->assertTrue($this->_valid->rangeLength($val, $min, $max));
        }
        
        // bad, or are blank
        $test = array(
            "", " ",
            'a', 'ab', 'abc',
            'abcdefg', 'abcdefgh', 'abcdefghi', 
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($this->_valid->rangeLength($val, $min, $max));
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
            $this->assertTrue($this->_valid->rangeLength($val, $min, $max, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testRegex()
    {
        $this->skip('all other tests use regex extensively');
    }
    
    public function testScope()
    {
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
            $this->assertTrue($this->_valid->scope($val, $size, $scope));
        }
        
        // bad, or are blank
        foreach ($bad as $val) {
            $this->assertFalse($this->_valid->scope($val, $size, $scope));
        }
        
        // blanks allowed
        $test = $good;
        $test[] = "";
        $test[] = " ";
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->scope($val, $size, $scope, Solar_Valid::OR_BLANK));
        }
    }
    
    public function testSepWords()
    {
        // good
        $test = array(
            'abc def ghi',
            ' abc def ',
            'a1s_2sd and another',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->sepWords($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            'a, b, c',
            'ab-db cd-ef',
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_valid->sepWords($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            'abc def ghi',
            ' abc def ',
            'a1s_2sd and another',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->sepWords($val, ' ', Solar_Valid::OR_BLANK));
        }
        
        // alternative separator
        $test = array(
            'abc,def,ghi',
            'abc,def',
            'a1s_2sd,and,another',
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_valid->sepWords($val, ','));
        }
    }
    
    public function testUri()
    {
        // good
        $test = array(
            "http://example.com",
            "http://example.com/path/to/file.php",
            "http://example.com/path/to/file.php/info",
            "http://example.com/path/to/file.php/info?foo=bar&baz=dib#zim",
            "http://example.com/?foo=bar&baz=dib#zim",
            "mms://user:pass@site.info/path/to/file.php/info?foo=bar&baz=dib#zim",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->uri($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            'a,', '^b', '%',
            'ab-db cd-ef',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($this->_valid->uri($val));
        }
        
        // blanks allowed
        $test = array(
            "", ' ',
            "http://example.com/path/to/file.php/info?foo=bar&baz=dib#zim",
            "mms://user:pass@site.info/path/to/file.php/info?foo=bar&baz=dib#zim",
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->uri($val, null, Solar_Valid::OR_BLANK));
        }
        
        // only certain schemes allowed
        $test = "http://example.com/path/to/file.php/info?foo=bar&baz=dib#zim";
        $this->assertTrue($this->_valid->uri($test, 'http'));
        $this->assertTrue($this->_valid->uri($test, array('ftp', 'http', 'news')));
        $this->assertFalse($this->_valid->uri($test, array('ftp', 'mms', 'gopher')));
    }
    
    public function testWord()
    {
        // good
        $test = array(
            'abc', 'def', 'ghi',
            'abc_def',
            'A1s_2Sd',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertTrue($this->_valid->word($val));
        }
        
        // bad, or are blank
        $test = array(
            "", '',
            'a,', '^b', '%',
            'ab-db cd-ef',
        );
        foreach ($test as $val) {
            // $assert->setLabel("'$val'");
            $this->assertFalse($this->_valid->word($val));
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
            $this->assertTrue($this->_valid->word($val, Solar_Valid::OR_BLANK));
        }
    }
}

?>