<?php
/**
 * 
 * Solar command to make a Vendor directory set with symlinks to the right
 * places.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 * @todo Make Vendor_App_Hello, Vendor_Cli_Help.  Also make Vendor_App_Base
 * and Vendor_Cli_Base?
 * 
 */
class Solar_Cli_MakeVendor extends Solar_Cli_Base
{
    /**
     * 
     * The "StudlyCaps" version of the vendor name.
     * 
     * @var string
     * 
     */
    protected $_studly = null;
    
    /**
     * 
     * The "lowercase-dashes" version of the vendor name.
     * 
     * @var string
     * 
     */
    protected $_dashes = null;
    
    /**
     * 
     * The various "source/" dirs to create.
     * 
     * @var array
     * 
     */
    protected $_dirs = array(
        '/{:dashes}/script',
        '/{:dashes}/config',
        '/{:dashes}/docs',
        '/{:dashes}/tests',
        '/{:dashes}/tests/Test',
        '/{:dashes}/tests/Test/{:studly}',
        '/{:dashes}/tests/Mock',
        '/{:dashes}/tests/Mock/{:studly}',
        '/{:dashes}/{:studly}/Model',
        '/{:dashes}/{:studly}/Controller/Page/Layout',
        '/{:dashes}/{:studly}/Controller/Page/Locale',
        '/{:dashes}/{:studly}/Controller/Page/Public',
        '/{:dashes}/{:studly}/Controller/Page/View',
        '/{:dashes}/{:studly}/Controller/Bread/Layout',
        '/{:dashes}/{:studly}/Controller/Bread/Locale',
        '/{:dashes}/{:studly}/Controller/Bread/Public',
        '/{:dashes}/{:studly}/Controller/Bread/View',
    );
    
    /**
     * 
     * The registered Solar_Inflect instance.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
    /**
     * 
     * Write out a series of dirs and symlinks for a new Vendor source.
     * 
     * @param string $vendor The Vendor name.
     * 
     * @return void
     * 
     */
    protected function _exec($vendor = null)
    {
        // we need a vendor name, at least
        if (! $vendor) {
            throw $this->_exception('ERR_NO_VENDOR_NAME');
        }
        
        // build "foo-bar" and "FooBar" versions of the vendor name.
        $this->_inflect = Solar_Registry::get('inflect');
        $this->_dashes  = $this->_inflect->camelToDashes($vendor);
        $this->_studly  = $this->_inflect->dashesToStudly($this->_dashes);
        
        // create dirs, files, and symlinks
        $this->_createDirs();
        $this->_createFiles();
        $this->_createLinks();
    }
    
    /**
     * 
     * Creates the "source/" directories for the vendor.
     * 
     * @return void
     * 
     */
    protected function _createDirs()
    {
        $this->_outln('Making vendor source directories.');
        
        $system = Solar::$system;
        foreach ($this->_dirs as $dir) {
            
            $dir = "$system/source" . str_replace(
                array('{:dashes}', '{:studly}'),
                array($this->_dashes, $this->_studly),
                $dir
            );

            if (is_dir($dir)) {
                $this->_outln("Directory $dir exists.");
            } else {
                $this->_outln("Creating $dir.");
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * 
     * Creates the various symlinks for the vendor directories.
     * 
     * @return void
     * 
     */
    protected function _createLinks()
    {
        $link_vendor = Solar::factory('Solar_Cli_LinkVendor');
        $link_vendor->exec($this->_studly);
    }
    
    /**
     * 
     * Creates the baseline PHP files in the Vendor directories from the 
     * skeleton files in `Data/*.txt`.
     * 
     * @return void
     * 
     */
    protected function _createFiles()
    {
        $system = Solar::$system;
        $data_dir = Solar_Class::dir($this, 'Data');
        $list = glob($data_dir . "*.txt");
        foreach ($list as $data_file) {
            
            $file = substr($data_file, strlen($data_dir));
            $file = str_replace('.txt', '.php', $file);
            $file = str_replace('_', '/', $file);
            $file = str_replace('-', '_', $file);
            $file = "$system/source/{$this->_dashes}/{$this->_studly}/$file";
            
            if (file_exists($file)) {
                $this->_outln("File $file exists.");
                continue;
            }
            
            $dirname = dirname($file);
            if (! is_dir($dirname)) {
                $this->_out("Making directory $dirname ... ");
                mkdir($dirname, 0755, true);
                $this->_outln("done.");
            }
            
            $text = file_get_contents($data_file);
            $text = str_replace('{:php}', '<?php', $text);
            $text = str_replace('{:vendor}', $this->_studly, $text);
            
            $this->_out("Writing $file ... ");
            file_put_contents($file, $text);
            $this->_outln("done.");
        }
        
        // write a "config/default.php" file
        $file = "$system/source/{$this->_dashes}/config/default.php";
        if (file_exists($file)) {
            $this->_outln("File $file exists.");
        } else {
            $text = "<?php\n\$config = array();\nreturn \$config;\n";
            $this->_out("Writing $file ... ");
            file_put_contents($file, $text);
            $this->_outln("done.");
        }
        
        // write a "config/run-tests.php" file
        $file = "$system/source/{$this->_dashes}/config/run-tests.php";
        if (file_exists($file)) {
            $this->_outln("File $file exists.");
        } else {
            $text = "<?php\n\$config = array();\nreturn \$config;\n";
            $this->_out("Writing $file ... ");
            file_put_contents($file, $text);
            $this->_outln("done.");
        }
    }
}
