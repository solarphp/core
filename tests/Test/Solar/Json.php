<?php
/**
 *
 * A big, big thank you to Omar Kilani of ext/json and to Michal Migurski,
 * Matt Knapp and Brett Stimmerman for their extensive unit testing, from
 * which I have harvested a great deal of these unit tests.
 *
 * And, thank you to Douglas Crockford, for all things JSON, and especially
 * for the JSON_checker test suite.
 *
 * -Clay Loveless
 *
 */
class Test_Solar_Json extends Solar_Test {

    /**
     * Json Checker Test Suite dir
     */
    protected $t;

    /**
     * View, used for Protaculous related JSON tests
     */
    protected $_view;

    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->t = dirname(__FILE__).'/Json/testsuite/';
        $this->_view = Solar::factory('Solar_View');
    }

    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function test__construct()
    {
        $json = Solar::factory('Solar_Json');
        $this->assertInstance($json, 'Solar_Json');
    }

    public function testEncode_native()
    {
        $json = Solar::factory('Solar_Json');
        if (function_exists('json_encode')) {
            $json = Solar::factory('Solar_Json');
            $before = 'hello world';
            $after = $json->encode($before);
            $this->assertSame($after, '"hello world"');
        } else {
            $this->skip('json_encode() not available, ext/json not installed');
        }
    }


    public function testDecode_native()
    {
        $json = Solar::factory('Solar_Json');
        if (function_exists('json_decode')) {
            $json = Solar::factory('Solar_Json');
            $before = '{ "test": { "foo": "bar" } }';
            $actual = var_export($json->decode($before), 1);

            $expect = "stdClass::__set_state(array(\n"
                    . "   'test' => \n"
                    . "  stdClass::__set_state(array(\n"
                    . "     'foo' => 'bar',\n"
                    . "  )),\n"
                    . "))";

            $this->assertSame($actual, $expect);
        } else {
            $this->skip('json_decode() not available, ext/json not installed');
        }
    }

    public function testEncode_null_and_bool()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $this->assertSame($json->encode(null), 'null');

        $this->assertSame($json->encode(true), 'true');
        $this->assertSame($json->encode(false), 'false');
    }

    public function testEncode_null_and_bool_compat()
    {
        if (!function_exists('json_encode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $this->assertSame($pjson->encode(null), $njson->encode(null));

            $this->assertSame($pjson->encode(true), $njson->encode(true));
            $this->assertSame($pjson->encode(false), $njson->encode(false));

        }
    }

    public function testEncode_numeric()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $this->assertSame($json->encode(1), '1');
        $this->assertSame($json->encode(-1), '-1');
        $this->assertSame($json->encode(1.0), '1');
        $this->assertSame($json->encode(1.1), '1.1');
    }

    public function testEncode_numeric_compat()
    {
        if (!function_exists('json_encode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $this->assertSame($pjson->encode(1), $njson->encode(1));
            $this->assertSame($pjson->encode(-1), $njson->encode(-1));
            $this->assertSame($pjson->encode(1.0), $njson->encode(1.0));
            $this->assertSame($pjson->encode(1.1), $njson->encode(1.1));
        }
    }

    public function testEncode_string()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $this->assertSame($json->encode('hello world'), '"hello world"');

        $expect = '"hello\\t\\"world\\""';
        $this->assertSame($json->encode("hello\t\"world\""), $expect);

        $expect = '"\\\\\\r\\n\\t\\"\\/"';
        $this->assertSame($json->encode("\\\r\n\t\"/"), $expect);

        $expect = '"h\u00c3\u00a9ll\u00c3\u00b6 w\u00c3\u00b8r\u00c5\u201ad"';
        $this->assertSame($json->encode('hÃ©llÃ¶ wÃ¸rÅ‚d'), $expect);

        $expect = '"\u0440\u0443\u0441\u0441\u0438\u0448"';
        $this->assertSame($json->encode("руссиш"), $expect);
    }

    public function testEncode_string_compat()
    {
        if (!function_exists('json_encode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $this->assertSame($pjson->encode('hello world'),
                              $njson->encode('hello world'));
            $this->assertSame($pjson->encode("hello\t\"world\""),
                              $njson->encode("hello\t\"world\""));
            $this->assertSame($pjson->encode("\\\r\n\t\"/"),
                              $njson->encode("\\\r\n\t\"/"));
            $this->assertSame($pjson->encode('hÃ©llÃ¶ wÃ¸rÅ‚d'),
                              $njson->encode('hÃ©llÃ¶ wÃ¸rÅ‚d'));
            $this->assertSame($pjson->encode("руссиш"),
                              $njson->encode("руссиш"));


//            $this->assertSame($pjson->encode(),
//                              $njson->encode());

        }
    }

    public function testEncode_array()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        // array with elements and nested arrays
        $before = array(null, true, array(1, 2, 3), "hello\"],[world!");
        $expect = '[null,true,[1,2,3],"hello\"],[world!"]';
        $this->assertSame($json->encode($before), $expect);

        // associative array with nested associative arrays
        $before = array('car1' => array(
                                    'color'=> 'tan',
                                    'model' => 'sedan'
                                ),
                        'car2' => array(
                                    'color' => 'red',
                                    'model' => 'sports'
                                )
                        );
        $expect = '{"car1":{"color":"tan","model":"sedan"},"car2":{"color":"red","model":"sports"}}';
        $this->assertSame($json->encode($before), $expect);

        // associative array with nested associative arrays, and some numeric keys thrown in
        $before = array(0=> array(0=> 'tan\\', 'model\\' => 'sedan'), 1 => array(0 => 'red', 'model' => 'sports'));
        $expect = '[{"0":"tan\\\\","model\\\\":"sedan"},{"0":"red","model":"sports"}]';
        $this->assertSame($json->encode($before), $expect);

        // associative array numeric keys which are not fully populated in a range of 0 to length-1
        $before = array (1 => 'one', 2 => 'two', 5 => 'five');
        $expect = '{"1":"one","2":"two","5":"five"}';
        $this->assertSame($json->encode($before), $expect);
    }

    public function testEncode_array_compat()
    {
        if (!function_exists('json_encode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            // array with elements and nested arrays
            $before = array(null, true, array(1, 2, 3), "hello\"],[world!");
            $this->assertSame($pjson->encode($before),
                              $njson->encode($before));

            // associative array with nested associative arrays
            $before = array('car1' => array(
                                        'color'=> 'tan',
                                        'model' => 'sedan'
                                    ),
                            'car2' => array(
                                        'color' => 'red',
                                        'model' => 'sports'
                                    )
                            );
            $this->assertSame($pjson->encode($before),
                              $njson->encode($before));

            // associative array with nested associative arrays, and some numeric keys thrown in
            $before = array(0=> array(0=> 'tan\\', 'model\\' => 'sedan'), 1 => array(0 => 'red', 'model' => 'sports'));
            $this->assertSame($pjson->encode($before),
                              $njson->encode($before));


            // associative array numeric keys which are not fully populated in a range of 0 to length-1
            $before = array (1 => 'one', 2 => 'two', 5 => 'five');
            $this->assertSame($pjson->encode($before),
                              $njson->encode($before));

        }
    }

    public function testEncode_object()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        // object with properties, nested object and arrays
        $obj = new stdClass();
        $obj->a_string = '"he":llo}:{world';
        $obj->an_array = array(1, 2, 3);
        $obj->obj = new stdClass();
        $obj->obj->a_number = 123;

        $expect = '{"a_string":"\"he\":llo}:{world","an_array":[1,2,3],"obj":{"a_number":123}}';
        $this->assertSame($json->encode($obj), $expect);
    }

    public function testEncode_object_compat()
    {
        if (!function_exists('json_encode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            // object with properties, nested object and arrays
            $obj = new stdClass();
            $obj->a_string = '"he":llo}:{world';
            $obj->an_array = array(1, 2, 3);
            $obj->obj = new stdClass();
            $obj->obj->a_number = 123;

            $this->assertSame($pjson->encode($obj),
                              $njson->encode($obj));
        }
    }

    public function testEncode_deQuote()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $before = array(
            'parameters'=> "Form.serialize('foo')",
            'asynchronous' => true,
            'onSuccess' => 'function(t) { ' . $this->_view->jsScriptaculous()->effect->highlight('#user-auth', array('duration' => 1.0), true) . '}',
            'on404'     => 'function(t) { alert(\'Error 404: location not found\'); }',
            'onFailure' => 'function(t) { alert(\'Ack!\'); }',
            'requestHeaders' => array('X-Solar-Version', Solar::apiVersion(), 'X-Foo', 'Bar')
        );

        $after = $json->encode($before, array('onSuccess', 'on404', 'onFailure', 'parameters'));

        $expect = <<< ENDEXPECT
{"parameters":Form.serialize('foo'),"asynchronous":true,"onSuccess":function(t) { new Effect.Highlight(el, {"duration":1});},"on404":function(t) { alert('Error 404: location not found'); },"onFailure":function(t) { alert('Ack!'); },"requestHeaders":["X-Solar-Version","@package_version@","X-Foo","Bar"]}
ENDEXPECT;

        $this->assertSame($after, trim($expect));

    }

    public function testDecode_null_and_bool()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));
        $this->assertNull($json->decode('null'));

        $this->assertNull($json->decode('true'));
        $expect = "stdClass::__set_state(array(\n"
        . "   'foo' => true,\n"
        . "))";
        $this->assertSame(var_export($json->decode('{"foo": true}'), 1), $expect);

        $this->assertNull($json->decode('false'));
        $expect = "stdClass::__set_state(array(\n"
        . "   'foo' => false,\n"
        . "))";
        $this->assertSame(var_export($json->decode('{"foo": false}'), 1), $expect);
    }

    public function testDecode_null_and_bool_compat()
    {
        if (!function_exists('json_decode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $this->assertSame($pjson->decode('null'),
                              $njson->decode('null'));
            $this->assertSame($pjson->decode('true'),
                              $njson->decode('true'));
            $this->assertSame(var_export($pjson->decode('{"foo": true}'), 1),
                              var_export($njson->decode('{"foo": true}'), 1));
            $this->assertSame($pjson->decode('false'),
                              $njson->decode('false'));
            $this->assertSame(var_export($pjson->decode('{"foo": false}'), 1),
                              var_export($njson->decode('{"foo": false}'), 1));
        }
    }

    public function testDecode_numeric()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        // NULL for strings-only, numeric value when in legit container
        $this->assertNull($json->decode('1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => 1,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[1]}'), 1), $expect);

        $this->assertNull($json->decode('-1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => -1,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[-1]}'), 1), $expect);

        $this->assertNull($json->decode('1.0'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => 1,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[1.0]}'), 1), $expect);

        $this->assertNull($json->decode('1.1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => 1.1,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[1.1]}'), 1), $expect);

        $this->assertNull($json->decode('1.1e1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => 11,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[1.1e1]}'), 1), $expect);

        $this->assertNull($json->decode('1.10e+1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => 11,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[1.10e+1]}'), 1), $expect);

        $this->assertNull($json->decode('1.1e-1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => 0.11,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[1.1e-1]}'), 1), $expect);

        $this->assertNull($json->decode('-1.1e-1'));
        $expect = "stdClass::__set_state(array(\n"
                . "   'foo' => \n"
                . "  array (\n"
                . "    0 => -0.11,\n"
                . "  ),\n"
                . "))";
        $this->assertSame(var_export($json->decode('{"foo":[-1.1e-1]}'), 1), $expect);
    }

    public function testDecode_numeric_compat()
    {
        if (!function_exists('json_decode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $this->assertSame($pjson->decode('1'), $njson->decode('1'));
            $before = '{"foo":[1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('-1'), $njson->decode('-1'));
            $before = '{"foo":[-1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('1.0'), $njson->decode('1.0'));
            $before = '{"foo":[1.0]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('1.1'), $njson->decode('1.1'));
            $before = '{"foo":[1.1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('1.1e1'), $njson->decode('1.1e1'));
            $before = '{"foo":[1.1e1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('1.10e+1'), $njson->decode('1.10e+1'));
            $before = '{"foo":[1.10e+1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('1.1e-1'), $njson->decode('1.1e-1'));
            $before = '{"foo":[1.1e-1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $this->assertSame($pjson->decode('-1.1e-1'), $njson->decode('-1.1e-1'));
            $before = '{"foo":[-1.1e-1]}';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

        }
    }

    public function testDecode_array()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $before = '[[[[[[[[[[[[[[[[[[["Not too deep"]]]]]]]]]]]]]]]]]]]';
        $actual = $json->decode($before);

        $expect = array(
            array(
              array(
                array(
                  array(
                    array(
                      array(
                        array(
                          array(
                            array(
                              array(
                                array(
                                  array(
                                    array(
                                      array(
                                        array(
                                          array(
                                            array(
                                              array('Not too deep')
                                            )
                                          )
                                        )
                                      )
                                    )
                                  )
                                )
                              )
                            )
                          )
                        )
                      )
                    )
                  )
                )
              )
            )
          );
        $this->assertSame($actual, $expect);

    }

    public function testDecode_array_compat()
    {
        if (!function_exists('json_decode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $before = '[[[[[[[[[[[[[[[[[[["Not too deep"]]]]]]]]]]]]]]]]]]]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

        }
    }

    public function testDecode_object()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $before = '{ "test": { "foo": "bar" } }';
        $actual = var_export($json->decode($before), 1);
        $expect = "stdClass::__set_state(array(\n"
                . "   'test' => \n"
                . "  stdClass::__set_state(array(\n"
                . "     'foo' => 'bar',\n"
                . "  )),\n"
                . "))";
        $this->assertSame($actual, $expect);

        $before = '{"a_string":"\"he\":llo}:{world","an_array":[1,2,3],"obj":{"a_number":123}}';
        $actual = var_export($json->decode($before), 1);
        $expect = "stdClass::__set_state(array(\n"
                . "   'a_string' => '\"he\":llo}:{world',\n"
                . "   'an_array' => \n"
                . "  array (\n"
                . "    0 => 1,\n"
                . "    1 => 2,\n"
                . "    2 => 3,\n"
                . "  ),\n"
                . "   'obj' => \n"
                . "  stdClass::__set_state(array(\n"
                . "     'a_number' => 123,\n"
                . "  )),\n"
                . "))";
        $this->assertSame($actual, $expect);

        $before = '{ "JSON Test Pattern pass3": { "The outermost value": "must be an object or array.", "In this test": "It is an object." } }';
        $actual = var_export($json->decode($before), 1);
        $expect = "stdClass::__set_state(array(\n"
                . "   'JSON Test Pattern pass3' => \n"
                . "  stdClass::__set_state(array(\n"
                . "     'The outermost value' => 'must be an object or array.',\n"
                . "     'In this test' => 'It is an object.',\n"
                . "  )),\n"
                . "))";
        $this->assertSame($actual, $expect);
    }

    public function testDecode_object_compat()
    {
        if (!function_exists('json_decode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $before = '{ "test": { "foo": "bar" } }';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));

            $before = '{ "JSON Test Pattern pass3": { "The outermost value": "must be an object or array.", "In this test": "It is an object." } }';
            $this->assertSame(var_export($pjson->decode($before), 1),
                              var_export($njson->decode($before), 1));
        }
    }

    public function testDecode_stress()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));
        $before = file_get_contents($this->t.'pass1.json');
        $expect = file_get_contents($this->t.'pass1.json.txt');
        $this->assertSame(serialize($json->decode($before)), $expect);
    }

    public function testDecode_stress_compat()
    {

        if (!function_exists('json_decode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');
            $before = file_get_contents($this->t.'pass1.json');

            $nexpect = serialize($njson->decode($before));
            $pexpect = serialize($pjson->decode($before));
            $this->assertSame($pexpect, $nexpect);
        }
    }

    public function testDecode_failure()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));


        $tests = scandir($this->t);
        natsort($tests);

        foreach ($tests as $file) {
            if (substr($file, 0, 4) == 'fail' && substr($file, -4) == 'json') {
                $before = file_get_contents($this->t.$file);
                $this->assertNull($json->decode($before));
            }
        }
    }

    public function testDecode_failure_compat()
    {
        if (!function_exists('json_decode')) {
            $this->skip('Skipping compatibility test, ext/json not installed');
        } else {

            $pjson = Solar::factory('Solar_Json', array(
                                                    'bypass_ext' => true,
                                                    'bypass_mb' => true
                                                    ));

            $njson = Solar::factory('Solar_Json');

            $tests = scandir($this->t);
            natsort($tests);

            foreach ($tests as $file) {
                if (substr($file, 0, 4) == 'fail' && substr($file, -4) == 'json') {
                    $before = file_get_contents($this->t.$file);
                    $this->assertNull($pjson->decode($before));
                    $this->assertNull($njson->decode($before));
                }
            }

        }
    }



}
?>