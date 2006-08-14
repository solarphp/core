<?php
class Test_Solar_Json_Checker extends Solar_Test {

    /**
     * Json Checker Test Suite dir
     */
    protected $t;

    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->t = dirname(__FILE__).'/testsuite/';
    }

    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function test__construct()
    {
        $checker = Solar::factory('Solar_Json_Checker');
        $this->assertInstance($checker, 'Solar_Json_Checker');
    }

    public function testPasses()
    {
        $checker = Solar::factory('Solar_Json_Checker');

        $tests = scandir($this->t);
        natsort($tests);

        foreach ($tests as $file) {
            if (substr($file, 0, 4) == 'pass' && substr($file, -4) == 'json') {
                $before = file_get_contents($this->t.$file);
                $this->assertTrue($checker->isValid($before));
            }
        }
    }

    public function testFailures()
    {
        $checker = Solar::factory('Solar_Json_Checker');

        $tests = scandir($this->t);
        natsort($tests);

        foreach ($tests as $file) {
            if (substr($file, 0, 4) == 'fail' && substr($file, -4) == 'json') {
                $before = file_get_contents($this->t.$file);
                $this->assertFalse($checker->isValid($before));
            }
        }
    }

}
?>