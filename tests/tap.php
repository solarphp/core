<?php
echo "\n";
// array of failure messages
$fail = array();

// array of skipped messages
$skip = array();

// run tests in each subdir
$time = time();
$proc = popen('pear run-tests -r', 'r');

// how many tests are running?
$line = fread($proc, 2048);
$tmp = explode(' ', $line);
echo "1.." . $tmp[1] . "\n";

// run each test
$i = 0;
while ($line = fread($proc, 2048)) {
    $i++;
    $type = substr($line, 0, 4);
    $mesg = substr($line, 4, -1); // drop the newline
    if ($type == 'FAIL') {
        echo "not ok $i - " . $mesg;
    } elseif ($type == 'SKIP') {
        echo "ok $i - $mesg # SKIP";
    } elseif ($type == 'PASS') {
        echo "ok $i - $mesg";
    }
    echo "\n";
}
pclose($proc);
echo "\n\n";
?>