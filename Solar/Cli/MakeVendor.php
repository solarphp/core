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
 * @version $Id: MakeModel.php 2995 2008-03-12 13:58:29Z pmjones $
 * 
 */
class Solar_Cli_MakeVendor extends Solar_Cli_Base
{
    protected $_studly = null;
    
    protected $_dashes = null;
    
    protected $_dirs = array(
        '/{:dashes}/bin',
        '/{:dashes}/docs',
        '/{:dashes}/tests',
        '/{:dashes}/{:studly}/App',
        '/{:dashes}/{:studly}/App/Public',
        '/{:dashes}/{:studly}/App/Public/images',
        '/{:dashes}/{:studly}/App/Public/scripts',
        '/{:dashes}/{:studly}/App/Public/styles',
        '/{:dashes}/{:studly}/Model',
        '/{:dashes}/{:studly}/Locale',
        '/{:dashes}/{:studly}/View',
        '/{:dashes}/{:studly}/View/Helper',
    );
    
    protected $_inflect;
    
    /**
     * 
     * Write out a series of files and dirs for a page-controller.
     * 
     * @param string $class The target class name for the app.
     * 
     * @return void
     * 
     */
    protected function _exec($vendor = null)
    {
        // we need a vendor name, at least
        if (! $vendor) {
            throw $this->_exception('ERR_NO_VENDOR_NAME');
        } else {
            $this->_vendor = $vendor;
        }
        
        // build "foo-bar" and "FooBar" versions of the vendor name.
        $this->_inflect = Solar_Registry::get('inflect');
        $this->_dashes  = $this->_inflect->camelToDashes($vendor);
        $this->_studly  = $this->_inflect->dashesToStudly($this->_dashes);
        
        // create dirs and symlinks
        $this->_createDirs();
        $this->_createLinks();
        
        // done!
        $done = "Done! Remember to add your new {$this->_studly}_App class "
              . "prefix to the ['Solar_Controller_Front']['classes'] element "
              . "in your config file.";

        $this->_outln($done);
    }
    
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
    
    protected function _createLinks()
    {
        $this->_outln('Making symlinks.');
        
        $system = Solar::$system;
        
        // include/Vendor -> source/vendor/Vendor
        $source  = "$system/source/{$this->_dashes}/$this->_studly";
        $include = "$system/include/{$this->_studly}";
        $cmd     = "ln -s $source $include";
        $this->_outln($cmd);
        passthru($cmd);
        
        // docroot/public/Vendor -> include/Vendor/App/Public
        $public = "$system/docroot/public/{$this->_vendor}";
        $cmd    = "ln -s $include/App/Public $public";
        $this->_outln($cmd);
        passthru($cmd);
    }
}
