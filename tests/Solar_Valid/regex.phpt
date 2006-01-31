--TEST--
Solar_Valid::regex()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

/*
So many of the other validation methods depend on Solar::regex(),
we let it pass by default.  Is that bad?
*/

// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete