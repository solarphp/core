<?php
echo "\n";
// array of failure messages
$fail = array();

// run tests in each subdir
$dir = dirname(__FILE__);
foreach (scandir($dir) as $name) {

    // skip certain directories
    if ($name == '..' || $name[0] == '.' || $name[0] == '_') {
        continue;
    }
    
    // only run if the name is a directory
    if (is_dir("$dir/$name")) {
    
        // run tests in the subdir
        echo "Running tests for $name ... ";
        $name = escapeshellarg($name);
        $cmd = "cd $name; pear run-tests; cd ..";
        exec($cmd, $output);
        echo "complete.\n";
        
        // retain failure messages
        $output = implode("\n", $output);
        $count = preg_match_all('/^FAIL.*$/m', $output, $matches, PREG_PATTERN_ORDER);
        if ($count) {
            $fail = array_merge($fail, $matches[0]);
        }
    }
}

// REPORTING
$count = count($fail);
echo "\n";
if ($count) {
    echo count($fail) . " failure";
    if ($count > 1) echo "s";
    echo ":\n" . implode("\n", $fail);
} else {
    echo "No failures.";
}
echo "\n\n";
?>