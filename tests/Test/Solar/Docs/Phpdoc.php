<?php

class Test_Solar_Docs_Phpdoc extends Solar_Test {
    
    protected $_method_block = '
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
    
    protected $_method_block_compressed = '
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
    
    protected $_param_block = '
        /**
         * 
         * This is the inital summary line; note how it passes over
         * two lines. Fuller description of the parameter.  Lorem ipsum
         * dolor sit amet.
         * 
         * @var float
         * 
         */';
    
    // param lines
    protected $_param = array(
        
        // full line
        'string $var1 Parameter summary.',
        
        // partial line, no summary
        'string $var1',
        
        // partial line, no variable
        'string Parameter summary.',
    );
    
    // return lines
    protected $_return = array(
        // full line
        'string Return summary.',
        
        // partial line
        'string',
    );
    
    // todo lines
    protected $_todo = "Todo summary.";
    
    // see lines
    protected $_see = "See summary.";
    
    // var lines
    protected $_var = array(
        // full line
        'string Var summary.',
        
        // partial line
        'string',
    );
    
    // throws lines
    protected $_throws = array(
        // full line
        'Solar_Exception Throws summary.',
        // partial line
        'Solar_Exception',
    );
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function _destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        $this->_phpdoc = Solar::factory('Solar_Docs_Phpdoc');
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_phpdoc, 'Solar_Docs_Phpdoc');
    }
    
    public function testParseParam_full()
    {
        $expect = array(
            'param' => array(
                'var1' => array(
                    'type' => 'string',
                    'summ' => 'Parameter summary.',
                ),
            ),
        );
        
        $this->_phpdoc->parseParam($this->_param[0]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseParam_noSummary()
    {
        $expect = array(
            'param' => array(
                'var1' => array(
                    'type' => 'string',
                    'summ' => '',
                ),
            ),
        );
        
        $this->_phpdoc->parseParam($this->_param[1]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseParam_noVariable()
    {
        $expect = array(
            'param' => array(
                0 => array(
                    'type' => 'string',
                    'summ' => 'Parameter summary.',
                ),
            ),
        );
        
        $this->_phpdoc->parseParam($this->_param[2]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseReturn_full()
    {
        $expect = array(
            'return' => array(
                'type' => 'string',
                'summ' => 'Return summary.',
            ),
        );
        
        $this->_phpdoc->parseReturn($this->_return[0]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseReturn_noSummary()
    {
        $expect = array(
            'return' => array(
                'type' => 'string',
                'summ' => '',
            ),
        );
        
        $this->_phpdoc->parseReturn($this->_return[1]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseTodo()
    {
        $expect = array(
            'todo' => array('Todo summary.'),
        );
        
        $this->_phpdoc->parseTodo($this->_todo);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseSee()
    {
        $expect = array(
            'see' => array('See summary.'),
        );
        
        $this->_phpdoc->parseSee($this->_see);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseVar_full()
    {
        $expect = array(
            'var' => array(
                'type' => 'string',
                'summ' => 'Var summary.',
            ),
        );
        
        $this->_phpdoc->parseVar($this->_var[0]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParseVar_noSummary()
    {
        $expect = array(
            'var' => array(
                'type' => 'string',
                'summ' => '',
            ),
        );
        
        $this->_phpdoc->parseVar($this->_var[1]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParse_method()
    {
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over\ntwo lines.",
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
        
        $actual = $this->_phpdoc->parse($this->_method_block);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_methodCompressed()
    {
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over\ntwo lines.",
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
        
        $actual = $this->_phpdoc->parse($this->_method_block_compressed);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_param()
    {
        $expect = array(
            'summ' => "This is the inital summary line; note how it passes over\ntwo lines.",
            'narr' => "Fuller description of the parameter.  Lorem ipsum\ndolor sit amet.",
            'tech' => array(
                'var' => array(
                    'type' => 'float',
                    'summ' => '',
                ),
            ),
        );
        
        $actual = $this->_phpdoc->parse($this->_param_block);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_throwsFull()
    {
        $expect = array(
            'throws' => array(
                array(
                    'type' => 'Solar_Exception',
                    'summ' => 'Throws summary.',
                ),
            ),
        );
        
        $this->_phpdoc->parseThrows($this->_throws[0]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
    
    public function testParse_throwsNoSummary()
    {
        $expect = array(
            'throws' => array(
                array(
                    'type' => 'Solar_Exception',
                    'summ' => '',
                ),
            ),
        );
        
        $this->_phpdoc->parseThrows($this->_throws[1]);
        $this->assertProperty($this->_phpdoc, '_info', 'same', $expect);
    }
}
?>