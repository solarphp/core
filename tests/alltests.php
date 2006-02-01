<?php
echo "\n";
// array of failure messages
$fail = array();

// array of skipped messages
$skip = array();

// run tests in each subdir
$time = time();
$proc = popen('pear run-tests -r', 'r');
$i = 0;

// how many tests are running?
$line = fread($proc, 2048);
echo $line;

while ($line = fread($proc, 2048)) {
    // 40 columns of dots
    if ($i++ % 40 == 0) {
        echo "\n";
    }
    // what kind of message?
    $type = substr($line, 0, 4);
    if ($type == 'FAIL') {
        $fail[] = trim($line);
        echo 'F';
    } elseif ($type == 'SKIP') {
        $skip[] = trim($line);
        echo 'S';
    } elseif ($type == 'PASS') {
        $pass[] = trim($line);
        echo '.';
    }
}
pclose($proc);
$secs = time() - $time;

// REPORTING
echo "\n\n";

$total = 0;
$count = count($pass);
$total += $count;
echo "$count passed, ";

$count = count($skip);
$total += $count;
echo "$count skipped, ";

$count = count($fail);
$total += $count;
echo "$count failed.\n";

echo "$total tests in $secs seconds.\n";

echo implode("\n", $skip);
echo implode("\n", $fail);
echo "\n\n";
?>