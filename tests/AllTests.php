<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once dirname(__FILE__) . '/SolarUnitTest.config.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
 
class AllTests
{
    private static $_suite = null;
    
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        self::$_suite = new PHPUnit_Framework_TestSuite('PHPUnit3 Tests for Solar');
        self::_collectTestFiles(dirname(__FILE__));
        return self::$_suite;
    }
    
    private static $_rootPath = '';
    private static function _collectTestFiles($path) {
        if (empty(self::$_rootPath)) {
            // add trailing slash
            self::$_rootPath = $path . '/';
        }
        
        foreach (scandir($path) as $file) {
            if (preg_match('/^.{1,2}$/', $file)) {
                continue;
            }
            
            if (preg_match('/Test.php$/', $file)) {
                $fullFilePath = $path . '/' . $file;
                require_once $fullFilePath;
                
                $className = str_replace('/', '_', substr($fullFilePath, strlen(self::$_rootPath), -4));
                self::$_suite->addTestSuite($className);
                
            }
            
            if (is_dir($path . '/' . $file)) {
                self::_collectTestFiles($path . '/' . $file);
            }
        }
    }
}

Solar::start('config.inc.php');

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}