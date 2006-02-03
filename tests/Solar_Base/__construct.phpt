--TEST--
Solar_Base::__construct()
--FILE---
<?php
// include ../_prepend.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_prepend.inc')) {
    require dirname(dirname(__FILE__)) . '/_prepend.inc';
}

// include ./_prepend.inc
if (is_readable(dirname(__FILE__) . '/_prepend.inc')) {
    require dirname(__FILE__) . '/_prepend.inc';
}

// ---------------------------------------------------------------------

// does the class create the locale config?
// note that the boolean false cancels config overrides.
$example = Solar::factory('Solar_Test_Example', false);
$expect = array(
    'foo' => 'bar',
    'baz' => 'dib',
    'zim' => 'gir',
    'locale' => 'Solar/Test/Example/Locale/',
);
$assert->property($example, '_config', 'same', $expect);

// does the class merge Solar.config.php overrides?
$example = Solar::factory('Solar_Test_Example');
$expect = array(
    'foo' => 'bar',
    'baz' => 'dib',
    'zim' => 'gaz',
    'locale' => 'Solar/Test/Example/Locale/',
);
$assert->property($example, '_config', 'same', $expect);

// does the class merge internal config with Solar.config.php
// and the factory-time config?
$config = array('zim' => 'irk');
$example = Solar::factory('Solar_Test_Example', $config);
$expect = array(
    'foo' => 'bar',
    'baz' => 'dib',
    'zim' => 'irk',
    'locale' => 'Solar/Test/Example/Locale/',
);
$assert->property($example, '_config', 'same', $expect);


// ---------------------------------------------------------------------

// include ./_append.inc
if (is_readable(dirname(__FILE__) . '/_append.inc')) {
    require dirname(__FILE__) . '/_append.inc';
}
// include ../_append.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_append.inc')) {
    require dirname(dirname(__FILE__)) . '/_append.inc';
}
?>
--EXPECT--
