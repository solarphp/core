<?php
require_once 'Solar.php';
Solar::start();
$app = Solar::object('Solar_App_Bugs');
echo $app->run();
Solar::stop();
?>