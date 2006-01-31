<?php
echo "\n";
// array of failure messages
$fail = array();

// array of skipped messages
$skip = array();

// run tests in each subdir
$dir = dirname(__FILE__);
$time = time();
foreach (scandir($dir) as $subdir) {

    // skip certain directories
    if ($subdir == '..' || $subdir[0] == '.' || $subdir[0] == '_') {
        continue;
    }
    
    // only run if the name is a directory
    if (is_dir("$dir/$subdir")) {
        chdir($subdir);
        echo "Running tests for $subdir ";
        $proc = popen('pear run-tests', 'r');
        while ($line = fread($proc, 2048)) {
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
        echo " complete.\n";
        chdir('..');
    }
}

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