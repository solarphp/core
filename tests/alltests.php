<?php
echo "\n";
// array of failure messages
$fail = array();

// array of skipped messages
$skip = array();

// run tests in each subdir
echo "Running tests:";
$time = time();
$proc = popen('pear run-tests -r', 'r');
$i = 0;
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
        echo '.';
    }
}
pclose($proc);

// REPORTING

// time
echo "\n";
echo time() - $time . " seconds, ";

// skips
$count = count($skip);
if ($count) {
    echo "$count skip";
    if ($count > 1) echo "s";
    echo ":\n" . implode("\n", $skip);
} else {
    echo "no skips, ";
}

// fails
$count = count($fail);
if ($count) {
    echo "$count failure";
    if ($count > 1) echo "s";
    echo ":\n" . implode("\n", $fail);
} else {
    echo "no failures.";
}

// done
echo "\n\n";
?>