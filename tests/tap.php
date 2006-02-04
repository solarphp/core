<?php
// run tests in each subdir
$proc = popen('pear run-tests -r', 'r');

// the "plan" line
$line = fread($proc, 2048);
$tmp = explode(' ', $line);
echo "1.." . $tmp[1] . "\n";

// the "test" lines
$i = 0;
while ($line = fread($proc, 2048)) {
    $i++;
    // FAIL Solar_Base::__construct()[Solar_Base/__construct.phpt]
    // SKIP Solar_Base::__construct()[Solar_Base/__construct.phpt] (reason: test incomplete)
    // PASS Solar_Base::__construct()[Solar_Base/__construct.phpt]
    $k = preg_match('/^(.{4}) (.*)?\[(.*)\](.*)/', $line, $mesg);
    if (! $k) {
        continue;
    }
    if ($mesg[1] == 'FAIL') {
        echo "not ok $i - $mesg[3]";
    } elseif ($mesg[1] == 'SKIP') {
        echo "ok $i - $mesg[2] # skip$mesg[4]";
    } elseif ($mesg[1] == 'PASS') {
        echo "ok $i - $mesg[2]";
    }
    echo "\n";
}

// done!
pclose($proc);
?>