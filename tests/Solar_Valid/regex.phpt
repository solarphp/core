--TEST--
Solar_Valid::regex()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

/*
So many of the other validation methods depend on Solar::regex(),
we let it pass by default.  Is that bad?
*/

// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
