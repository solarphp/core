<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Filter_ValidateMaxIsoDate extends Test_Solar_Filter_Abstract {

    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Filter_ValidateMaxIsoDate = array(
    );

    public function testValidateMaxIsoDate()
    {
        $minDate = "1987-04-19";
        $test = array(
                "1968-05-12",
                "1975-12-23",
                "1987-04-18",
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_filter->validateMaxIsoDate($val, $minDate));
        }
    }

    public function testValidateMaxIsoDate_badOrBlank()
    {
        $minDate = "1987-04-19";
        $test = array(
                "",
                "1987-07-29",
                "1999-02-23",
                "2010-11-29",
                null,
        );
        foreach ($test as $val) {
            $this->assertFalse($this->_filter->validateMaxIsoDate($val, $minDate));
        }
    }

    public function testValidateIsoDate_notRequired()
    {
        $minDate = "1987-04-19";
        $this->_filter->setRequire(false);
        $test = array(
                "",
                null,
                "1968-05-12",
                "1975-12-23",
                "1987-04-18",
        );
        foreach ($test as $val) {
            $this->assertTrue($this->_filter->validateMaxIsoDate($val, $minDate));
        }
    }
}


