<?php
/**
 * 
 * Solar command to make a model class from an SQL table.
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
 */
class Solar_Cli_MakeModel extends Solar_Cli_Base
{
    /**
     * 
     * The base directory where we will write the class file to, typically
     * the local PEAR directory.
     * 
     * @var string
     * 
     */
    protected $_target = null;
    
    /**
     * 
     * The table name we're making the model from.
     * 
     * @var string
     * 
     */
    protected $_table = null;
    
    /**
     * 
     * The class name this model extends from.
     * 
     * @var string
     * 
     */
    protected $_extends = 'Solar_Sql_Model';
    
    /**
     * 
     * Array of model-class templates (skeletons).
     * 
     * @var array
     * 
     */
    protected $_tpl = array();
    
    /**
     * 
     * Whether or not to connect to the database.
     * 
     * @var bool
     * 
     */
    protected $_connect = true;
    
    /**
     * 
     * Is the model class inherited or not?
     * 
     * @var bool
     * 
     */
    protected $_inherit = false;
    
    /**
     * 
     * Write out a series of model, record, and collection classes for a model.
     * 
     * @param string $class The target class name for the model.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        // we need a class name, at least
        if (! $class) {
            throw $this->_exception('ERR_NO_CLASS');
        }
        
        $this->_outln("Making model '$class'.");
        
        // load the templates
        $this->_loadTemplates();
        
        // we need a target directory
        $this->_setTarget();
        
        // connect?
        $this->_setConnect();
        
        // we need a table name
        $this->_setTable($class);
        
        // extend this class
        $this->_setExtends();
        
        // using inheritance?
        $this->_setInherit();
        
        // write the model/record/collection files
        $this->_writeModel($class);
        $this->_writeRecord($class);
        $this->_writeCollection($class);
        
        // write out the setup information
        if ($this->_inherit) {
            $this->_outln('Using inheritance, so skipping setup.');
        } else {
            $this->_createSetupDir($class);
            $this->_writeTableName($class);
            $this->_writeTableCols($class);
        }
        
        // done!
        $this->_outln('Done.');
    }
    
    /**
     * 
     * Loads the template array from skeleton files.
     * 
     * @return void
     * 
     */
    protected function _loadTemplates()
    {
        $this->_tpl = array();
        $dir = Solar_Class::dir($this, 'Data');
        $list = glob($dir . '*.php');
        foreach ($list as $file) {
            // strip .php off the end of the file name
            $key = substr(basename($file), 0, -4);
            
            // we need to add the php-open tag ourselves, instead of
            // having it in the template file, becuase the PEAR packager
            // complains about parsing the skeleton code.
            $this->_tpl[$key] = "<?php\n" . file_get_contents($file);
        }
    }
    
    /**
     * 
     * Sets the base directory target.
     * 
     * @return void
     * 
     */
    protected function _setTarget()
    {
        $target = $this->_options['target'];
        if (! $target) {
            // use the solar system 'include' directory.
            // that should automatically point to the right vendor for us.
            $target = Solar::$system . '/include';
        }
        
        $this->_target = Solar_Dir::fix($target);
        
        $this->_outln("Will write to '{$this->_target}'.");
    }
    
    /**
     * 
     * Sets the table name; determines from the class name if no table name is
     * given.
     * 
     * @param string $class The class name for the model.
     * 
     * @return void
     * 
     */
    protected function _setTable($class)
    {
        $table = $this->_options['table'];
        
        if (! $table) {
            // try to determine from the class name
            $pos = strpos($class, 'Model_');
            if (! $pos) {
                throw $this->_exception('ERR_CANNOT_DETERMINE_TABLE');
            }
            
            // convert Solar_Model_TableName to table_name
            $table = substr($class, $pos + 6);
            $table = preg_replace('/([a-z])([A-Z])/', '$1_$2', $table);
            $table = strtolower($table);
        }
        
        $this->_table = $table;
        
        $this->_outln("Using table '{$this->_table}'.");
    }
    
    /**
     * 
     * Sets the $_connect property based on the command-line --connect flag.
     * 
     * @return void
     * 
     */
    protected function _setConnect()
    {
        $this->_connect = $this->_options['connect'];
        if ($this->_connect) {
            $this->_outln('Will connect to database for column information.');
        } else {
            $this->_outln('Will not connect to database.');
        }
    }
    
    /**
     * 
     * Sets the $_inherit property based on the $_extends value.
     * 
     * @return void
     * 
     */
    protected function _setInherit()
    {
        if (substr($this->_extends, -6) == '_Model') {
            $this->_inherit = false;
            $this->_outln('Not using inheritance.');
        } else {
            $this->_inherit = true;
            $this->_outln('Using inheritance.');
        }
    }
    
    /**
     * 
     * Sets the class this model will extend from.
     * 
     * @return void
     * 
     */
    protected function _setExtends()
    {
        $extends = $this->_options['extends'];
        if ($extends) {
            $this->_extends = $extends;
        } else {
            $this->_extends = 'Solar_Sql_Model';
        }
    }
    
    /**
     * 
     * Gets the SQL connection parameters from the command line options.
     * 
     * @return array An array of SQL connection parameters suitable for 
     * passing as a Solar_Sql_Adapter class config.
     * 
     */
    protected function _getSqlConfig()
    {
        $config = array();
        $list = array('adapter', 'host', 'port', 'user', 'pass', 'name');
        foreach ($list as $key) {
            $val = $this->_options[$key];
            if ($val) {
                $config[$key] = $val;
            }
        }
        return $config;
    }
    
    /**
     * 
     * Writes the model class file.
     * 
     * @param string $class The model class name.
     * 
     * @return void
     * 
     */
    protected function _writeModel($class)
    {
        // the target file
        $file = $this->_target
              . str_replace('_', DIRECTORY_SEPARATOR, $class)
              . '.php';
        
        // does the class file already exist?
        if (file_exists($file)) {
            $this->_outln('Model class exists.');
            return;
        }
        
        // create the class dir before attempting to write the model class
        $dir = Solar_Dir::fix(
            $this->_target . str_replace('_', '/', $class)
        );
        
        if (! file_exists($dir)) {
            $this->_out('Making class directory ... ');
            mkdir($dir, 0755, true);
            $this->_outln('done.');
        }
        
        // get the class model template
        $tpl_key = $this->_inherit ? 'model-inherit' : 'model';
        $text = str_replace(
            array('{:class}', '{:extends}'),
            array($class, $this->_extends),
            $this->_tpl[$tpl_key]
        );
        
        $this->_out('Writing model class ... ');
        file_put_contents($file, $text);
        $this->_outln('done.');
    }
    
    /**
     * 
     * Writes the record class file.
     * 
     * @param string $class The model class name.
     * 
     * @return void
     * 
     */
    protected function _writeRecord($class)
    {
        $file = $this->_target
              . str_replace('_', DIRECTORY_SEPARATOR, $class)
              . DIRECTORY_SEPARATOR
              . 'Record.php';

        if (file_exists($file)) {
            $this->_outln('Record class exists.');
            return;
        }
        
        $text = str_replace(
            array('{:class}', '{:extends}'),
            array($class, $this->_extends),
            $this->_tpl['record']
        );
        
        $this->_out('Writing record class ... ');
        file_put_contents($file, $text);
        $this->_outln('done.');
    }
    
    /**
     * 
     * Writes the collection class file.
     * 
     * @param string $class The model class name.
     * 
     * @return void
     * 
     */
    protected function _writeCollection($class)
    {
        $file = $this->_target
              . str_replace('_', DIRECTORY_SEPARATOR, $class)
              . DIRECTORY_SEPARATOR
              . 'Collection.php';

        if (file_exists($file)) {
            $this->_outln('Collection class exists.');
            return;
        }
        
        $text = str_replace(
            array('{:class}', '{:extends}'),
            array($class, $this->_extends),
            $this->_tpl['collection']
        );
        
        $this->_out('Writing collection class ... ');
        file_put_contents($file, $text);
        $this->_outln('done.');
    }
    
    /**
     * 
     * Creates the model "Setup/" directory.
     * 
     * @param string $class The model class name.
     * 
     * @return void
     * 
     */
    protected function _createSetupDir($class)
    {
        // get the setup dir
        $dir = Solar_Dir::fix(
            $this->_target . str_replace('_', '/', $class) . '/Setup'
        );
        
        if (! file_exists($dir)) {
            $this->_out('Creating setup directory ... ');
            mkdir($dir, 0755, true);
            $this->_outln('Done.');
        } else {
            $this->_outln('Setup directory exists.');
        }
    }
    
    /**
     * 
     * Writes the "Setup/table_name.php" file.
     * 
     * @param string $class The model class name.
     * 
     * @return void
     * 
     */
    protected function _writeTableName($class)
    {
        $dir = Solar_Dir::fix(
            $this->_target . str_replace('_', '/', $class) . '/Setup'
        );
        
        $file = $dir . DIRECTORY_SEPARATOR . 'table_name.php';
        $text = "<?php\nreturn '{$this->_table}';";
        
        // write the name file
        $this->_out('Saving table name for setup ... ');
        file_put_contents($file, $text);
        $this->_outln('done.');
    }
    
    /**
     * 
     * Writes the "Setup/table_cols.php" file, connecting to the database if
     * needed.
     * 
     * @param string $class The model class name.
     * 
     * @return void
     * 
     */
    protected function _writeTableCols($class)
    {
        $dir = Solar_Dir::fix(
            $this->_target . str_replace('_', '/', $class) . '/Setup'
        );
        
        $file = $dir . DIRECTORY_SEPARATOR . 'table_cols.php';
        
        if (! file_exists($file)) {
            $this->_out('Creating empty table cols setup ... ');
            $text = "<?php\nreturn array();";
            file_put_contents($file, $text);
            $this->_outln('done.');
        }
        
        if (! $this->_connect) {
            $this->_outln('Not connecting to database.');
            $this->_outln('Not fetching table cols.');
            return;
        }
        
        // connect to database
        $this->_out('Connecting to database ... ');
        $sql = Solar::factory('Solar_Sql', $this->_getSqlConfig());
        $this->_outln('connected.');
        
        // fetch table cols
        $this->_out('Fetching table cols ... ');
        $table_cols = $sql->fetchTableCols($this->_table);
        if (! $table_cols) {
            throw $this->_exception('ERR_NO_COLS', array(
                'table' => $this->_table
            ));
        }
        $this->_outln('done.');
        
        // write the cols file
        $this->_out('Saving table cols for setup ...');
        $text = var_export($table_cols, true);
        file_put_contents($dir . 'table_cols.php', "<?php\nreturn $text;");
        $this->_outln('done.');
    }
}

