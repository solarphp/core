<?php
// prepend for all controllers
include $this->helper('prepend');

// list all bugs regardless of open or closed
$tpl->list = $bugs->fetchList();

// display
return $tpl->fetch('list.php');
?>