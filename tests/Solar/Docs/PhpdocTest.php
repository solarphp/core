<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

class Solar_Docs_PhpdocTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->_phpdoc = Solar::factory('Solar_Docs_Phpdoc');
    }
    
    public function teardown()
    {
        $this->_phpdoc = null;
    }
    
    public function test__construct()
    {
        $this->assertType('Solar_Docs_Phpdoc', $this->_phpdoc);
    }
    
    public function testParseParam_full()
    {
        // full line
        $source = '
            /**
             * @param string $var1 Parameter summary.
             */';
        
        $expect = array(
            'param' => array(
                'var1' => array(
                    'type' => 'string',
                    'summ' => 'Parameter summary.',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseParam_noSummary()
    {
        // partial line, no summary
        $source = '
            /**
             * @param string $var1
             */';
        
        $expect = array(
            'param' => array(
                'var1' => array(
                    'type' => 'string',
                    'summ' => '',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseParam_noVariable()
    {
        // partial line, no variable
        $source = '
            /**
             * @param string Parameter summary.
             */';
        
        $expect = array(
            'param' => array(
                0 => array(
                    'type' => 'string',
                    'summ' => 'Parameter summary.',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseReturn_full()
    {
        // full line
        $source = '
            /**
             * @return string Return summary.
             */';
        
        $expect = array(
            'return' => array(
                'type' => 'string',
                'summ' => 'Return summary.',
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseReturn_noSummary()
    {
        // partial line
        $source = '
            /**
             * @return string
             */';
    
        $expect = array(
            'return' => array(
                'type' => 'string',
                'summ' => '',
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseTodo()
    {
        // todo lines
        $source = '
            /**
             * @todo Todo summary.
             */';
    
        $expect = array(
            'todo' => array('Todo summary.'),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseSee()
    {
        $source = '
            /**
             * @see See summary.
             */';
    
        $expect = array(
            'see' => array('See summary.'),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseVar_full()
    {
        // full line
        $source = '
            /**
             * @var string Var summary.
             */
            ';
    
        $expect = array(
            'var' => array(
                'type' => 'string',
                'summ' => 'Var summary.',
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseVar_noSummary()
    {
        // partial line
        $source = '
            /**
             * @var string
             */
            ';
        
        $expect = array(
            'var' => array(
                'type' => 'string',
                'summ' => '',
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParse_method()
    {
        $source = '
            /**
             * 
             * This is the inital summary line; note how it passes over
             * two lines. Fuller description of the method.  Lorem ipsum
             * dolor sit amet, consectetuer adipiscing elit. Nunc porta
             * libero quis orci.
             * 
             * @param string $var1 Parameter summary for $var1.  Lorem ipsum
             * dolor sit amet, consectetuer adipiscing elit.
             * 
             * @param object No variable name.
             * 
             * @param int
             * 
             * @param array $var4
             * 
             * @return object Return summary.
             * 
             * @throws Solar_Exception Throws summary.
             * 
             * @see Some other item.
             * 
             * @todo Do this later.
             * 
             */';
    
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over two lines.",
            'narr' => "Fuller description of the method.  Lorem ipsum\ndolor sit amet, consectetuer adipiscing elit. Nunc porta\nlibero quis orci.",
            'tech' => array(
                'param' => array(
                    'var1' => array(
                        'type' => 'string',
                        'summ' => 'Parameter summary for $var1.  Lorem ipsum dolor sit amet, consectetuer adipiscing elit.',
                    ),
                    1 => array(
                        'type' => 'object',
                        'summ' => 'No variable name.',
                    ),
                    2 => array(
                        'type' => 'int',
                        'summ' => '',
                    ),
                    'var4' => array(
                        'type' => 'array',
                        'summ' => '',
                    ),
                ),
                'return' => array(
                    'type' => 'object',
                    'summ' => 'Return summary.',
                ),
                'see' => array('Some other item.'),
                'todo' => array('Do this later.'),
                'throws' => array(
                    array(
                        'type' => 'Solar_Exception',
                        'summ' => 'Throws summary.',
                    ),
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertEquals($expect, $actual);
    }
    
    public function testParse_methodCompressed()
    {
        $source = '
            /**
             * This is the inital summary line; note how it passes over
             * two lines. Fuller description of the method.  Lorem ipsum
             * dolor sit amet, consectetuer adipiscing elit. Nunc porta
             * libero quis orci.
             * 
             * @param string $var1 Parameter summary for $var1.  Lorem ipsum
             * dolor sit amet, consectetuer adipiscing elit.
             * @param object No variable name.
             * @param int
             * @param array $var4
             * @return object Return summary.
             * @throws Solar_Exception Throws summary.
             * @see Some other item.
             * @todo Do this later.
             */';
    
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over two lines.",
            'narr' => "Fuller description of the method.  Lorem ipsum\ndolor sit amet, consectetuer adipiscing elit. Nunc porta\nlibero quis orci.",
            'tech' => array(
                'param' => array(
                    'var1' => array(
                        'type' => 'string',
                        'summ' => 'Parameter summary for $var1.  Lorem ipsum dolor sit amet, consectetuer adipiscing elit.',
                    ),
                    1 => array(
                        'type' => 'object',
                        'summ' => 'No variable name.',
                    ),
                    2 => array(
                        'type' => 'int',
                        'summ' => '',
                    ),
                    'var4' => array(
                        'type' => 'array',
                        'summ' => '',
                    ),
                ),
                'return' => array(
                    'type' => 'object',
                    'summ' => 'Return summary.',
                ),
                'see' => array('Some other item.'),
                'todo' => array('Do this later.'),
                'throws' => array(
                    array(
                        'type' => 'Solar_Exception',
                        'summ' => 'Throws summary.',
                    ),
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertEquals($expect, $actual);
    }
    
    public function testParse_methodNonTechnical()
    {
        $source = '
            /**
             * This is the inital summary line; note how it passes over
             * two lines. No technical data follows.
             */';
         
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over two lines.",
            'narr' => "No technical data follows.",
            'tech' => array(),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParseParam()
    {
        $source = '
            /**
             * 
             * This is the inital summary line; note how it passes over
             * two lines. Fuller description of the parameter.  Lorem ipsum
             * dolor sit amet.
             * 
             * @var float
             * 
             */';
    
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over two lines.",
            'narr' => "Fuller description of the parameter.  Lorem ipsum\ndolor sit amet.",
            'tech' => array(
                'var' => array(
                    'type' => 'float',
                    'summ' => '',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    // throws lines
    protected $_throws = array(
        // partial line
        'Solar_Exception',
    );
    
    public function testParseThrows_full()
    {
        // full line
        $source = '
            /**
             * @throws Solar_Exception Throws summary.
             */';
        
        $expect = array(
            'throws' => array(
                array(
                    'type' => 'Solar_Exception',
                    'summ' => 'Throws summary.',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseThrows_noSummary()
    {
        // no summary
        $source = '
            /**
             * @throws Solar_Exception
             */';
        
        $expect = array(
            'throws' => array(
                array(
                    'type' => 'Solar_Exception',
                    'summ' => '',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseCategory()
    {
        $source = '
            /**
             * @category Test
             */';
        
        $expect = array('category' => 'Test');
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseCategory_extraSpaces()
    {
        $source = '
            /**
             * @category Test with extra characters
             */';
        
        // should ignore extra spaces
        $expect = array('category' => 'Test');
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
        
    }
    
    public function testParsePackage()
    {
        $source = '
            /**
             * @package Test_Solar
             */';
        
        $expect = array('package' => 'Test_Solar');
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParsePackage_extraSpaces()
    {
        $source = '
            /**
             * @package Test_Solar with extra characters
             */';
        
        // should ignore extra spaces
        $expect = array('package' => 'Test_Solar');
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseSubpackage()
    {
        $source = '
            /**
             * @subpackage Test_Solar_Docs
             */';
        
        $expect = array('subpackage' => 'Test_Solar_Docs');
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
    
    public function testParseSubpackage_extraSpaces()
    {
        $source = '
            /**
             * @subpackage Test_Solar_Docs with extra characters
             */';
        
        // should ignore extra spaces
        $expect = array('subpackage' => 'Test_Solar_Docs');
        
        $actual = $this->_phpdoc->parse($source);
        $this->assertSame($actual['tech'], $expect);
    }
}
