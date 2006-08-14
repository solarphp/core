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
        $before = '[
    "JSON Test Pattern pass1",
    {"object with 1 member":["array with 1 element"]},
    {},
    [],
    -42,
    true,
    false,
    null,
    {
        "integer": 1234567890,
        "real": -9876.543210,
        "e": 0.123456789e-12,
        "E": 1.234567890E+34,
        "":  23456789012E666,
        "zero": 0,
        "one": 1,
        "space": " ",
        "quote": "\"",
        "backslash": "\\",
        "controls": "\b\f\n\r\t",
        "slash": "/ & \/",
        "alpha": "abcdefghijklmnopqrstuvwyz",
        "ALPHA": "ABCDEFGHIJKLMNOPQRSTUVWYZ",
        "digit": "0123456789",
        "special": "`1~!@#$%^&*()_+-={\':[,]}|;.</>?",
        "hex": "\u0123\u4567\u89AB\uCDEF\uabcd\uef4A",
        "true": true,
        "false": false,
        "null": null,
        "array":[  ],
        "object":{  },
        "address": "50 St. James Street",
        "url": "http://www.JSON.org/",
        "comment": "// /* <!-- --",
        "# -- --> */": " ",
        " s p a c e d " :[1,2 , 3

,

4 , 5        ,          6           ,7        ],
        "compact": [1,2,3,4,5,6,7],
        "jsontext": "{\"object with 1 member\":[\"array with 1 element\"]}",
        "quotes": "&#34; \u0022 %22 0x22 034 &#x22;",
        "\/\\\"\uCAFE\uBABE\uAB98\uFCDE\ubcda\uef4A\b\f\n\r\t`1~!@#$%^&*()_+-=[]{}|;:\',./<>?"
: "A key can be any string"
    },
    0.5 ,98.6
,
99.44
,

1066


,"rosebud"]';
        //var_dump($json->decode($before));
        $this->todo();

    }

    public function testDecode_stress_compat()
    {
        $this->todo();
    }

    public function testDecode_failure()
    {
        $json = Solar::factory('Solar_Json', array(
                                                'bypass_ext' => true,
                                                'bypass_mb' => true
                                                ));

        $before = '"A JSON payload should be an object or array, not a string."';
        $this->assertNull($json->decode($before));

        $before = '["Unclosed array"';
        $this->assertNull($json->decode($before));

        $before = '{unquoted_key: "keys must be quoted"}';
        $this->assertNull($json->decode($before));

        $before = '["extra comma",]';
        $this->assertNull($json->decode($before));

        $before = '["double extra comma",,]';
        $this->assertNull($json->decode($before));

        $before = '[   , "<-- missing value"]';
        $this->assertNull($json->decode($before));

        $before = '["Comma after the close"],';
        $this->assertNull($json->decode($before));

        $before = '["Extra close"]]';
        $this->assertNull($json->decode($before));

        $before = '{"Extra comma": true,}';
        $this->assertNull($json->decode($before));

        $before = '{"Extra value after close": true} "misplaced quoted value"';
        $this->assertNull($json->decode($before));

        $before = '{"Illegal expression": 1 + 2}';
        $this->assertNull($json->decode($before));

        $before = '{"Illegal invocation": alert()}';
        $this->assertNull($json->decode($before));

        $before = '{"Numbers cannot have leading zeroes": 013}';
        $this->assertNull($json->decode($before));

        $before = '{"Numbers cannot be hex": 0x14}';
        $this->assertNull($json->decode($before));

        $before = '["Illegal backslash escape: \x15"]';
        $this->assertNull($json->decode($before));

        $before = "[\"Illegal backslash escape: \'\"]";
        $this->assertNull($json->decode($before));

        $before = '["Illegal backslash escape: \017"]';
        $this->assertNull($json->decode($before));

        $before = '[[[[[[[[[[[[[[[[[[[["Too deep"]]]]]]]]]]]]]]]]]]]]';
        $this->assertNull($json->decode($before));

        $before = '{"Missing colon" null}';
        $this->assertNull($json->decode($before));

        $before = '{"Double colon":: null}';
        $this->assertNull($json->decode($before));

        $before = '{"Comma instead of colon", null}';
        $this->assertNull($json->decode($before));

        $before = '["Colon instead of comma": false]';
        $this->assertNull($json->decode($before));

        $before = '["Bad value", truth]';
        $this->assertNull($json->decode($before));

        $before = "['single quote']";
        $this->assertNull($json->decode($before));

        $before = "[\"tab\tcharacter\tin\tstring\t\"]";
        $this->assertNull($json->decode($before));

        $before = "[\"tab\\\tcharacter\\\tin\\\tstring\\\t\"]";
        $this->assertNull($json->decode($before));

        $before = "[\"line\nbreak\"]";
        $this->assertNull($json->decode($before));

        $before = "[\"line\\\nbreak\"]";
        $this->assertNull($json->decode($before));
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

            $before = '"A JSON payload should be an object or array, not a string."';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Unclosed array"';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{unquoted_key: "keys must be quoted"}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["extra comma",]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["double extra comma",,]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '[   , "<-- missing value"]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Comma after the close"],';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Extra close"]]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Extra comma": true,}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Extra value after close": true} "misplaced quoted value"';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Illegal expression": 1 + 2}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Illegal invocation": alert()}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Numbers cannot have leading zeroes": 013}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Numbers cannot be hex": 0x14}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Illegal backslash escape: \x15"]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = "[\"Illegal backslash escape: \'\"]";
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Illegal backslash escape: \017"]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '[[[[[[[[[[[[[[[[[[[["Too deep"]]]]]]]]]]]]]]]]]]]]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Missing colon" null}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Double colon":: null}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '{"Comma instead of colon", null}';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Colon instead of comma": false]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = '["Bad value", truth]';
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = "['single quote']";
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = "[\"tab\tcharacter\tin\tstring\t\"]";
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = "[\"tab\\tcharacter\\tin\\tstring\\t\"]";
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = "[\"line\nbreak\"]";
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

            $before = "[\"line\\nbreak\"]";
            $this->assertSame($pjson->decode($before),
                              $njson->decode($before));

        }
    }

}
?>