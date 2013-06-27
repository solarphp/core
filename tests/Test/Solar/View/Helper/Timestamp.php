<?php
/**
 * 
 * Concrete class test.
 *
 * The timezone offset values were extracted from the TimeZone Converter website
 *
 * @link http://www.timezoneconverter.com/cgi-bin/tzc.tzc
 *
 */
class Test_Solar_View_Helper_Timestamp extends Test_Solar_View_Helper {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_View_Helper_Timestamp = array(
    );
    
    // -----------------------------------------------------------------
    // 
    // Test methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Test -- Outputs a formatted timestamp using [[php::date() | ]] format codes.
     * 
     */
    public function testTimestamp()
    {
        $string = 'Nov 7, 1970, 12:34:56';
        $actual = $this->_view->timestamp($string);
        $expect = '1970-11-07 12:34:56';
        $this->assertSame($actual, $expect);
    }
    
    public function testTimestamp_int()
    {
        $int = strtotime('Nov 7, 1970 12:34:56pm');
        $actual = $this->_view->timestamp($int);
        $expect = '1970-11-07 12:34:56';
        $this->assertSame($actual, $expect);
    }
    
    public function testTimestamp_reformat()
    {
        $string = 'Nov 7, 1970, 11:45pm';
        $actual = $this->_view->timestamp($string, 'U');
        $expect = strtotime($string);
        $this->assertEquals($actual, $expect);
    }
    
    public function testTimestamp_configFormat()
    {
        $helper = $this->_view->newHelper('timestamp', array('format' => 'U'));
        $string = 'Nov 7, 1970, 12:34:56 pm';
        $actual = $helper->timestamp($string);
        $expect = strtotime($string);
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset between UTC and
     * Pacific/Honolulu (-10 hours)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetUtcHonolulu()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'UTC',
            'tz_output' => 'Pacific/Honolulu',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2009-12-31 14:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     *  tests correct handling of timezone offset between UTC and
     * Pacific/Fiji (+12 hours)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetUtcFiji()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'UTC',
            'tz_output' => 'Pacific/Fiji',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-01-01 13:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     *  tests correct handling of timezone offset between Honolulu and
     * Fiji (+22 hours)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetHonoluluFiji()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'Pacific/Honolulu',
            'tz_output' => 'Pacific/Fiji',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-01-01 23:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     *  tests correct handling of timezone offset between UTC and
     * Europe/Berlin (+1 hour when DST isn't active)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetUtcBerlin()
    {
        $helper = $this->_view->newHelper('timestamp',array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'UTC',
            'tz_output' => 'Europe/Berlin',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-01-01 01:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     *  tests correct handling of timezone offset between UTC and
     * Europe/Berlin (+2 hours when DST is active)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetUtcBerlinDst()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'UTC',
            'tz_output' => 'Europe/Berlin',
        ));
        $string = '2010-06-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-06-01 02:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     *  tests correct handling of timezone offset between Berlin and
     * Los Angeles (-9 hours when DST is active in both zones)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetBerlinLosAngelesDst()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'Europe/Berlin',
            'tz_output' => 'America/Los_Angeles',
        ));
        $string = '2010-06-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-05-31 15:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     *  tests correct handling of timezone offset between Los Angeles and
     * Berlin (+9 hours when DST is active in both zones)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetLosAngelesBerlinDst()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'America/Los_Angeles',
            'tz_output' => 'Europe/Berlin',
        ));
        $string = '2010-06-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-06-01 09:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset between Berlin and
     * Honolulu (-11 hours when DST is not active in Berlin)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetBerlinHonolulu()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'Europe/Berlin',
            'tz_output' => 'Pacific/Honolulu',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2009-12-31 13:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset between Berlin and
     * Honolulu (-12 hours when DST is active in Berlin)
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetBerlinHonoluluDst()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'Europe/Berlin',
            'tz_output' => 'Pacific/Honolulu',
        ));
        $string = '2010-06-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-05-31 12:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset for a system timezone
     * different from UTC
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetSystemTimezoneBerlin()
    {
        ini_set('date.timezone', 'Europe/Berlin');

        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'Europe/Berlin',
            'tz_output' => 'America/New_York',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2009-12-31 18:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset for a system timezone
     * different from UTC
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetSystemTimezoneBerlinDst()
    {
        ini_set('date.timezone', 'Europe/Berlin');

        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
            'tz_origin' => 'America/Los_Angeles',
            'tz_output' => 'Pacific/Honolulu',
        ));
        $string = '2010-06-01 00:00:00';
        $actual = $helper->timestamp($string);
        $expect = '2010-05-31 21:00:00'; // Hawaii doesn't observe DST
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset for a system timezone
     * different from UTC. Using method params for setting time zones instead
     * of config values.
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetParamsBerlinHonolulu()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
        ));
        $string = '2010-01-01 00:00:00';
        $actual = $helper->timestamp($string, null, 'Europe/Berlin', 'Pacific/Honolulu');
        $expect = '2009-12-31 13:00:00';
        $this->assertEquals($actual, $expect);
    }

    /**
     *
     * tests correct handling of timezone offset for a system timezone
     * different from UTC Using method params for setting time zones instead
     * of config values.
     *
     * @return void
     *
     **/
    public function testTimestamp_timezoneOffsetParamsBerlinHonoluluDst()
    {
        $helper = $this->_view->newHelper('timestamp', array(
            'format'    => 'Y-m-d H:i:s',
        ));
        $string = '2010-06-01 00:00:00';
        $actual = $helper->timestamp($string, null, 'Europe/Berlin', 'Pacific/Honolulu');
        $expect = '2010-05-31 12:00:00';
        $this->assertEquals($actual, $expect);
    }
}
